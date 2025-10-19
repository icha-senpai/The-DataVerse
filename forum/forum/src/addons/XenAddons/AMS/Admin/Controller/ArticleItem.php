<?php

namespace XenAddons\AMS\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class ArticleItem extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertAdminPermission('articleManagementSystem');
	}

	public function actionIndex()
	{
		return $this->view('XenAddons\AMS:ArticleItem', 'xa_ams_article');
	}
	
	public function actionList()
	{
		$this->setSectionContext('xa_amsBatchUpdateArticles');
	
		$criteria = $this->filter('criteria', 'array');
		$order = $this->filter('order', 'str');
		$direction = $this->filter('direction', 'str');
	
		$page = $this->filterPage();
		$perPage = 50;
	
		$showingAll = $this->filter('all', 'bool');
		if ($showingAll)
		{
			$page = 1;
			$perPage = 5000;
		}
	
		$searcher = $this->searcher('XenAddons\AMS:Article', $criteria);
		$searcher->setOrder($order, $direction);
	
		$finder = $searcher->getFinder();
		$finder->limitByPage($page, $perPage);
	
		$total = $finder->total();
		$articles = $finder->fetch();
	
		$viewParams = [
			'articles' => $articles,
		
			'total' => $total,
			'page' => $page,
			'perPage' => $perPage,
		
			'showingAll' => $showingAll,
			'showAll' => (!$showingAll && $total <= 5000),
		
			'criteria' => $searcher->getFilteredCriteria(),
			'order' => $order,
			'direction' => $direction
		];
		return $this->view('XenAddons\AMS:ArticleItem\Listing', 'xa_ams_batch_update_article_list', $viewParams);
	}	
	
	public function actionReplyBans()
	{
		$replyBanRepo = $this->getReplyBanRepo();
		$replyBanFinder = $replyBanRepo->findReplyBansForList();
	
		$user = null;
		$linkParams = [];
		if ($username = $this->filter('username', 'str'))
		{
			$user = $this->finder('XF:User')->where('username', $username)->fetchOne();
			if ($user)
			{
				$replyBanFinder->where('user_id', $user->user_id);
				$linkParams['username'] = $user->username;
			}
		}
	
		$page = $this->filterPage();
		$perPage = 25;
	
		$replyBanFinder->limitByPage($page, $perPage);
		$total = $replyBanFinder->total();
	
		$this->assertValidPage($page, $perPage, $total, 'xa-ams/reply-bans');
	
		$viewParams = [
			'bans' => $replyBanFinder->fetch(),
			'user' => $user,
		
			'page' => $page,
			'perPage' => $perPage,
			'total' => $total,
		
			'linkParams' => $linkParams
		];
		return $this->view('XenAddons\AMS:ArticleItem\ReplyBan\Listing', 'xa_ams_article_reply_ban_list', $viewParams);
	}
	
	public function actionReplyBansDelete(ParameterBag $params)
	{
		$replyBan = $this->assertReplyBanExists($params->article_reply_ban_id);
	
		if ($this->isPost())
		{
			$replyBan->delete();
			return $this->redirect($this->buildLink('xa-ams/reply-bans'));
		}
		else
		{
			$viewParams = [
				'ban' => $replyBan
			];
			return $this->view('XenAddons\AMS:ArticleItem\ReplyBan\Delete', 'xa_ams_article_reply_ban_delete', $viewParams);
		}
	}
	
	public function actionBatchUpdate()
	{
		$this->setSectionContext('xa_amsBatchUpdateArticles');
	
		$searcher = $this->searcher('XenAddons\AMS:Article');
	
		$viewParams = [
			'criteria' => $searcher->getFormCriteria(),
			'success' => $this->filter('success', 'bool')
		] + $searcher->getFormData();
		return $this->view('XenAddons\AMS:ArticleItem\BatchUpdate', 'xa_ams_article_batch_update', $viewParams);
	}
	
	public function actionBatchUpdateConfirm()
	{
		$this->setSectionContext('xa_amsBatchUpdateArticles');
	
		$this->assertPostOnly();
	
		$criteria = $this->filter('criteria', 'array');
		$searcher = $this->searcher('XenAddons\AMS:Article', $criteria);
	
		$articleIds = $this->filter('article_ids', 'array-uint');
	
		$total = count($articleIds) ?: $searcher->getFinder()->total();
		if (!$total)
		{
			throw $this->exception($this->error(\XF::phraseDeferred('no_items_matched_your_filter')));
		}
	
		if ($articleIds)
		{
			$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
			$articleFinder->where('article_id', $articleIds);
		}
		else
		{
			$articleFinder = clone $searcher->getFinder();
		}
		$hasPrefixes = (bool)$articleFinder
			->where('prefix_id', '>', 0)
			->total();
	
		/** @var \XenAddons\AMS\Repository\ArticlePrefix $prefixRepo */
		$prefixRepo = $this->repository('XenAddons\AMS:ArticlePrefix');
		$prefixes = $prefixRepo->getPrefixListData();
		
		/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
		$categoryRepo = $this->repository('XenAddons\AMS:Category');
		$categories = $categoryRepo->getCategoryOptionsData(false);
	
		$viewParams = [
			'total' => $total,
			'articleIds' => $articleIds,
			'hasPrefixes' => $hasPrefixes,
			'criteria' => $searcher->getFilteredCriteria(),
		
			'prefixes' => $prefixes,
			'categories' => $categories
		];
		return $this->view('XenAddons\AMS:ArticleItem\BatchUpdate\Confirm', 'xa_ams_article_batch_update_confirm', $viewParams);
	}
	
	public function actionBatchUpdateAction()
	{
		$this->setSectionContext('xa_amsBatchUpdateArticles');
	
		$this->assertPostOnly();
	
		if ($this->request->exists('article_ids'))
		{
			$articleIds = $this->filter('article_ids', 'json-array');
			$total = count($articleIds);
			$jobCriteria = null;
		}
		else
		{
			$criteria = $this->filter('criteria', 'json-array');
	
			$searcher = $this->searcher('XenAddons\AMS:Article', $criteria);
			$total = $searcher->getFinder()->total();
			$jobCriteria = $searcher->getFilteredCriteria();
	
			$articleIds = null;
		}
	
		if (!$total)
		{
			throw $this->exception($this->error(\XF::phraseDeferred('no_items_matched_your_filter')));
		}
	
		$actions = $this->filter('actions', 'array');
	
		if ($this->request->exists('confirm_delete') && empty($actions['delete']))
		{
			return $this->error(\XF::phrase('you_must_confirm_deletion_to_proceed'));
		}
	
		$this->app->jobManager()->enqueueUnique('articleAction', 'XenAddons\AMS:ArticleAction', [
			'total' => $total,
			'actions' => $actions,
			'articleIds' => $articleIds,
			'criteria' => $jobCriteria
		]);
	
		return $this->redirect($this->buildLink('xa-ams/batch-update', null, ['success' => true]));
	}

	/**
	 * @param string $id
	 * @param array|string|null $with
	 * @param null|string $phraseKey
	 *
	 * @return \XenAddons\AMS\Entity\ArticleReplyBan
	 */
	protected function assertReplyBanExists($id, array $extraWith = [], $phraseKey = null)
	{
		$extraWith[] = 'ArticleItem';
		$extraWith[] = 'User';
		return $this->assertRecordExists('XenAddons\AMS:ArticleReplyBan', $id, $extraWith, $phraseKey);
	}
	
	/**
	 * @return \XenAddons\AMS\Repository\ArticleReplyBan
	 */
	protected function getReplyBanRepo()
	{
		return $this->repository('XenAddons\AMS:ArticleReplyBan');
	}	
}