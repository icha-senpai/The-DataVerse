<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;
use XF\Service\AbstractNotifier;

class Notify extends AbstractNotifier
{
	/**
	 * @var ArticleItem
	 */
	protected $article;

	protected $actionType;

	public function __construct(\XF\App $app,ArticleItem $article, $actionType)
	{
		parent::__construct($app);

		switch ($actionType)
		{
			case 'update':
			case 'article':
				break;

			default:
				throw new \InvalidArgumentException("Unknown action type '$actionType'");
		}

		$this->actionType = $actionType;
		$this->article = $article;
	}

	public static function createForJob(array $extraData)
	{
		$article = \XF::app()->find('XenAddons\AMS:ArticleItem', $extraData['articleId'], ['Category']);
		if (!$article)
		{
			return null;
		}

		return \XF::service('XenAddons\AMS:ArticleItem\Notify', $article, $extraData['actionType']);
	}

	protected function getExtraJobData()
	{
		return [
			'articleId' => $this->article->article_id,
			'actionType' => $this->actionType
		];
	}

	protected function loadNotifiers()
	{
		return [
			'mention' => $this->app->notifier('XenAddons\AMS:Article\Mention', $this->article),
			'categoryWatch' => $this->app->notifier('XenAddons\AMS:Article\CategoryWatch', $this->article, $this->actionType),
			'articleWatch'=> $this->app->notifier('XenAddons\AMS:Article\ArticleWatch', $this->article)
		];
	}

	protected function loadExtraUserData(array $users)
	{
		$permCombinationIds = [];
		foreach ($users AS $user)
		{
			$id = $user->permission_combination_id;
			$permCombinationIds[$id] = $id;
		}

		$this->app->permissionCache()->cacheMultipleContentPermsForContent(
			$permCombinationIds,
			'article_category', $this->article->Category->category_id
		);
	}

	protected function canUserViewContent(\XF\Entity\User $user)
	{
		return \XF::asVisitor(
			$user,
			function() { return $this->article->canView(); }
		);
	}

	public function skipUsersWatchingCategory(\XenAddons\AMS\Entity\Category $category)
	{
		$checkCategories = array_keys($category->breadcrumb_data);
		$checkCategories[] = $category->category_id;

		$db = $this->db();

		$watchers = $db->fetchAll("
			SELECT user_id, send_alert, send_email
			FROM xf_xa_ams_category_watch
			WHERE category_id IN (" . $db->quote($checkCategories) . ")
				AND (category_id = ? OR include_children > 0)
				AND (send_alert = 1 OR send_email = 1)
		", $category->category_id);

		foreach ($watchers AS $watcher)
		{
			if ($watcher['send_alert'])
			{
				$this->setUserAsAlerted($watcher['user_id']);
			}
			if ($watcher['send_email'])
			{
				$this->setUserAsEmailed($watcher['user_id']);
			}
		}
	}
}