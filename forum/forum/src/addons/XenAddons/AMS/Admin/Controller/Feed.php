<?php

namespace XenAddons\AMS\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class Feed extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertAdminPermission('articleManagementSystem');
	}

	public function actionIndex(ParameterBag $params)
	{
		$feedRepo = $this->getFeedRepo();

		$viewParams = [
			'feeds' => $feedRepo->findFeedsForList()->fetch()
		];
		return $this->view('XenAddons\AMS:Feed\Listing', 'xa_ams_feed_list', $viewParams);
	}

	public function feedAddEdit(\XenAddons\AMS\Entity\Feed $feed)
	{
		$categoryRepo = $this->repository('XenAddons\AMS:Category');

		$prefixes = [];
		if ($feed->category_id)
		{
			/** @var \XenAddons\AMS\Entity\Category $category */
			$category = $this->em()->find('XenAddons\AMS:Category', $feed->category_id);
			if ($category)
			{
				$prefixes = $category->getPrefixesGrouped();
			}
		}
		
		$viewParams = [
			'feed' => $feed,
			'categories' => $categoryRepo->getCategoryOptionsData(false),
			'prefixes' => $prefixes
		];
		return $this->view('XenAddons\AMS:Feed\Edit', 'xa_ams_feed_edit', $viewParams);
	}

	public function actionEdit(ParameterBag $params)
	{
		$feed = $this->assertFeedExists($params->feed_id);
		return $this->feedAddEdit($feed);
	}

	public function actionAdd()
	{
		$feed = $this->em()->create('XenAddons\AMS:Feed');

		return $this->feedAddEdit($feed);
	}

	protected function getFeedInput()
	{
		return $this->filter([
			'title' => 'str',
			'url' => 'str',
			'frequency' => 'uint',
			'active' => 'bool',

			'user_id' => 'int',

			'category_id' => 'uint',
			'prefix_id' => 'uint',
			'title_template' => 'str',
			'message_template' => 'str',

			'article_visible' => 'bool'
		]);
	}

	protected function feedSaveProcess(\XenAddons\AMS\Entity\Feed $feed)
	{
		$form = $this->formAction();

		$input = $this->getFeedInput();
		if ($input['user_id'] == -1)
		{
			$username = $this->filter('username', 'str');
			$user = $this->finder('XF:User')->where('username', $username)->fetchOne();
			if ($user)
			{
				$input['user_id'] = $user['user_id'];
			}
		}
		$input['user_id'] = intval(max($input['user_id'], 0));

		$reader = $this->getFeedReader($input['url']);
		$feedData = $reader->getFeedData(false);

		$this->assertValidFeedData($feedData, $reader, false);

		if (!$input['title'] && !empty($feedData['title']))
		{
			$input['title'] = $feedData['title'];
		}

		$form->basicEntitySave($feed, $input);

		return $form;
	}

	public function actionSave(ParameterBag $params)
	{
		$this->assertPostOnly();

		if ($this->request->exists('preview'))
		{
			return $this->rerouteController(__CLASS__, 'preview', $params);
		}

		if ($params->feed_id)
		{
			$feed = $this->assertFeedExists($params->feed_id);
		}
		else
		{
			$feed = $this->em()->create('XenAddons\AMS:Feed');
		}

		$this->feedSaveProcess($feed)->run();

		return $this->redirect($this->buildLink('xa-ams/feeds'));
	}

	public function actionPreview()
	{
		$input = $this->getFeedInput();

		/** @var \XenAddons\AMS\Entity\Feed $feed */
		$feed = $this->em()->create('XenAddons\AMS:Feed');
		$feed->bulkSet($input);

		$reader = $this->getFeedReader($feed['url']);
		$feedData = $reader->getFeedData();

		$this->assertValidFeedData($feedData, $reader);

		if (!$feed->title && $feedData['title'])
		{
			$feed->title = $feedData['title'];
		}

		$entry = $feedData['entries'][mt_rand(0, count($feedData['entries']) - 1)];

		$title = $feed->getEntryTitle($entry);
		$message = $feed->getEntryMessage($entry);
		
		$entry['title'] = $title;
		$entry['message'] = $message;

		if ($input['user_id'] == 0)
		{
			$entry['author'] = $feed->title;
		}
		else if ($input['user_id'] == -1)
		{
			$entry['author'] = $this->filter('username', 'str');
		}

		$viewParams = [
			'feed' => $feed,
			'feedData' => $feedData,
			'entry' => $entry
		];
		return $this->view('XenAddons\AMS:\Feed\Preview', 'xa_ams_feed_preview', $viewParams);
	}

	public function actionDelete(ParameterBag $params)
	{
		$feed = $this->assertFeedExists($params->feed_id);
		if (!$feed->preDelete())
		{
			return $this->error($feed->getErrors());
		}

		if ($this->isPost())
		{
			$feed->delete();

			return $this->redirect($this->buildLink('xa-ams/feeds'));
		}
		else
		{
			$viewParams = [
				'feed' => $feed
			];
			return $this->view('XenAddons\AMS:Feed\Delete', 'xa_ams_feed_delete', $viewParams);
		}
	}

	public function actionToggle()
	{
		/** @var \XF\ControllerPlugin\Toggle $plugin */
		$plugin = $this->plugin('XF:Toggle');
		return $plugin->actionToggle('XenAddons\AMS:Feed');
	}

	public function actionImport(ParameterBag $params)
	{
		$feed = $this->assertFeedExists($params->feed_id, ['Category']);
		if (!$feed->Category)
		{
			throw $this->exception($this->error(\XF::phrase('cannot_find_associated_category')));
		}

		$feeder = $this->getFeedFeeder($feed->url);
		if ($feeder->setupImport($feed, true) && $feeder->countPendingEntries())
		{
			$feeder->importEntries();
		}
		return $this->redirect($this->buildLink('xa-ams/feeds'));
	}

	protected function assertValidFeedData($feedData, \XenAddons\AMS\Service\Feed\Reader $reader, $checkEntries = true)
	{
		if (!$feedData || ($checkEntries && empty($feedData['entries'])))
		{
			throw $this->exception($this->error(
				\XF::phrase('there_was_problem_requesting_feed', [
					'message' => $reader->getException()
						? $reader->getException()->getMessage()
						: \XF::phrase('n_a')
				])
			));
		}
	}

	/**
	 * @param $url
	 *
	 * @return \XenAddons\AMS\Service\Feed\Reader
	 */
	protected function getFeedReader($url)
	{
		return $this->service('XenAddons\AMS:Feed\Reader', $url);
	}

	/**
	 * @param $url
	 *
	 * @return \XenAddons\AMS\Service\Feed\Feeder
	 */
	protected function getFeedFeeder($url)
	{
		return $this->service('XenAddons\AMS:Feed\Feeder', $url);
	}

	/**
	 * @param string $id
	 * @param array|string|null $with
	 * @param null|string $phraseKey
	 *
	 * @return \XenAddons\AMS\Entity\Feed
	 */
	protected function assertFeedExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists('XenAddons\AMS:Feed', $id, $with, $phraseKey);
	}

	/**
	 * @return \XenAddons\AMS\Repository\Feed
	 */
	protected function getFeedRepo()
	{
		return $this->repository('XenAddons\AMS:Feed');
	}
}