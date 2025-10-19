<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XenAddons\AMS\Entity\ArticleItem;
use XF\PrintableException;
use XF\Util\Arr;

class Article extends Repository
{
	public function findArticlesForArticleList(array $viewableCategoryIds = null, array $limits = [], \XenAddons\AMS\Entity\Category $category = null)
	{
		$limits = array_replace([
			'visibility' => true,
			'allowOwnPending' => false
		], $limits);

		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');

		if (is_array($viewableCategoryIds))
		{
			$articleFinder->where('category_id', $viewableCategoryIds);
		}
		else
		{
			$articleFinder->with('Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}

		$articleFinder
			->with(['full', 'fullCategory'])
			->useDefaultOrder($category);

		if ($limits['visibility'])
		{
			$articleFinder->applyGlobalVisibilityChecks($limits['allowOwnPending']);
		}

		return $articleFinder;
	}
	
	public function findArticlesForAuthorArticleList(\XF\Entity\User $user, array $viewableCategoryIds = null, array $limits = [])
	{
		$limits = array_replace([
			'visibility' => true,
			'allowOwnPending' => true // false if you don't want authors to see their moderated articles
		], $limits);
	
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		if (is_array($viewableCategoryIds))
		{
			$articleFinder->where('category_id', $viewableCategoryIds);
		}
		else
		{
			$articleFinder->with('Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}
	
		$articleFinder
			->byUser($user)
			->with(['full', 'fullCategory'])
			->useDefaultOrder();
	
		if ($limits['visibility'])
		{
			$articleFinder->applyGlobalVisibilityChecks($limits['allowOwnPending']);
		}
	
		return $articleFinder;
	}	

	public function findArticlesForRssFeed(\XenAddons\AMS\Entity\Category $category = null)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder->where('article_state', 'visible')
			->setDefaultOrder('last_update', 'DESC')
			->with(['Category', 'User']);
	
		if ($category)
		{
			$articleFinder->where('category_id', $category->category_id);
		}
		else
		{
			$articleFinder->where('last_update', '>', $this->getReadMarkingCutOff());
		}
	
		return $articleFinder;
	}

	public function findFeaturedArticles(array $viewableCategoryIds = null)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');

		if (is_array($viewableCategoryIds))
		{
			$articleFinder->where('category_id', $viewableCategoryIds);
		}
		else
		{
			$articleFinder->with('Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}

		$articleFinder
			->with('Featured', true)
			->where('article_state', 'visible')
			->with(['full', 'fullCategory'])
			->setDefaultOrder($articleFinder->expression('RAND()'));

		return $articleFinder;
	}
	
	public function findFeaturedArticlesForUser(\XF\Entity\User $user)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder
			->with('Featured', true)
			->with('Category.Permissions|' . \XF::visitor()->permission_combination_id)
			->where('article_state', 'visible')
			->with(['full', 'fullCategory'])
			->where('user_id', $user->user_id);
	
		return $articleFinder;
	}
	
	public function findDraftArticlesForUser(\XF\Entity\User $user)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder
			->with(['full', 'fullCategory'])
			->with('Category.Permissions|' . \XF::visitor()->permission_combination_id)
			->where('article_state', 'draft')
			->where('user_id', $user->user_id);
	
		return $articleFinder;
	}

	public function findAwaitingArticlesForUser(\XF\Entity\User $user)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder
			->with(['full', 'fullCategory'])
			->with('Category.Permissions|' . \XF::visitor()->permission_combination_id)
			->where('article_state', 'awaiting')
			->where('user_id', $user->user_id)
			->setDefaultOrder('publish_date', 'ASC');
	
		return $articleFinder;
	}	
	
	// fetches "Drafts" and "Awaiting Publishing" articles for the Articles Queue.
	public function findArticlesForArticlesQueue(array $viewableCategoryIds = null)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		if (is_array($viewableCategoryIds))
		{
			$articleFinder->where('category_id', $viewableCategoryIds);
		}
		else
		{
			$articleFinder->with('Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}
	
		$articleFinder
			->with(['full', 'fullCategory'])
			->where('article_state', ['draft','awaiting'])
			->setDefaultOrder('last_update', 'DESC');

		return $articleFinder;
	}
	
	// currently only used by the listener to fetch counts of drafts/awaiting for the moderator bar.
	public function findArticlesPending()
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder
			->with('Category.Permissions|' . \XF::visitor()->permission_combination_id)
			->where('article_state', ['draft','awaiting']);
	
		return $articleFinder;
	}	
	
	public function findArticlesForWatchedList($userId = null)
	{
		if ($userId === null)
		{
			$userId = \XF::visitor()->user_id;
		}
		$userId = intval($userId);

		/** @var \XenAddons\AMS\Finder\ArticleItem $finder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');

		$articleFinder
			->with(['full', 'fullCategory'])
			->with('Watch|' . $userId, true)
			->with('Category.Permissions|' . \XF::visitor()->permission_combination_id)
			->where('article_state', 'visible')
			->setDefaultOrder('last_update', 'desc');

		return $articleFinder;
	}
	
	// currently only used by the add article to series function!
	public function findArticlesForSelectList($userId = null)
	{
		if ($userId === null)
		{
			$userId = \XF::visitor()->user_id;
		}
		$userId = intval($userId);
		
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder
			->with(['Category', 'Category.Permissions|' . \XF::visitor()->permission_combination_id])
			->where('article_state', 'visible')
			->where('user_id', $userId)
			->setDefaultOrder('last_update', 'desc');
	
		return $articleFinder;
	}

	public function findOtherArticlesByCategory(\XenAddons\AMS\Entity\ArticleItem $thisArticle)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');

		$articleFinder
			->with(['full', 'fullCategory'])
			->with(['User', 'Category', 'Category.Permissions|' . \XF::visitor()->permission_combination_id])
			->where('article_state', 'visible')
			->where('category_id', $thisArticle->category_id)
			->where('article_id', '<>', $thisArticle->article_id)
			->setDefaultOrder('last_update', 'desc');

		return $articleFinder;
	}
	
	public function findOtherArticlesByAuthor(\XF\Entity\User $user, $articleId, $excludeArticleIds = [])
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder->byUser($user)
			->with(['full', 'fullCategory'])
			->with(['User', 'Category', 'Category.Permissions|' . \XF::visitor()->permission_combination_id])
			->where('article_state', 'visible')
			->where('article_id', '<>', $articleId)
			->setDefaultOrder('last_update', 'desc');
	
		if ($excludeArticleIds)
		{
			$articleFinder->where('article_id', '<>', $excludeArticleIds);
		}
		
		return $articleFinder;
	}

	public function findArticlesForUser(\XF\Entity\User $user, array $viewableCategoryIds = null, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');

		$articleFinder->byUser($user)
			->with(['full', 'fullCategory'])
			->setDefaultOrder('last_update', 'desc');

		if (is_array($viewableCategoryIds))
		{
			// if we have viewable category IDs, we likely have those permissions
			$articleFinder->where('category_id', $viewableCategoryIds);
		}
		else
		{
			$articleFinder->with('Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}

		$limits = array_replace([
			'visibility' => true,
			'allowOwnPending' => $user->user_id == \XF::visitor()->user_id
		], $limits);

		if ($limits['visibility'])
		{
			$articleFinder->applyGlobalVisibilityChecks($limits['allowOwnPending']);
		}

		return $articleFinder;
	}
	
	public function findArticlesByContributor(
		int $userId,
		array $viewableCategoryIds = null,
		array $limits = []
	)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
		$articleFinder->with("Contributors|{$userId}", true)
			->with(['full', 'fullCategory'])
			->setDefaultOrder('last_update', 'desc');
	
		if (is_array($viewableCategoryIds))
		{
			// if we have viewable category IDs, we likely have those permissions
			$articleFinder->where('category_id', $viewableCategoryIds);
		}
		else
		{
			$articleFinder->with('Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}
	
		$limits = array_replace(['visibility' => true], $limits);
		if ($limits['visibility'])
		{
			$articleFinder->applyGlobalVisibilityChecks();
		}
	
		return $articleFinder;
	}

	public function findArticleForThread(\XF\Entity\Thread $thread)
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $finder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');

		$articleFinder->where('discussion_thread_id', $thread->thread_id)
			->with('full')
			->with('Category.Permissions|' . \XF::visitor()->permission_combination_id);

		return $articleFinder;
	}
	
	public function logArticleView(ArticleItem $articleItem)
	{
		$this->db()->query("
			INSERT INTO xf_xa_ams_article_view
				(article_id, total)
			VALUES
				(? , 1)
			ON DUPLICATE KEY UPDATE
				total = total + 1
		", $articleItem->article_id);
	}
	
	public function batchUpdateArticleViews()
	{
		$db = $this->db();
		$db->query("
			UPDATE xf_xa_ams_article AS article
			INNER JOIN xf_xa_ams_article_view AS article_view ON (article.article_id = article_view.article_id)
			SET article.view_count = article.view_count + article_view.total
		");
		$db->emptyTable('xf_xa_ams_article_view');
	}
	
	public function markArticlesReadByVisitor($categoryIds = null, $newViewed = null)
	{
		$articleFinder = $this->findArticlesForArticleList($categoryIds)
			->unreadOnly();
	
		$articleItems = $articleFinder->fetch();
	
		foreach ($articleItems AS $articleItem)
		{
			$this->markArticleItemReadByVisitor($articleItem, $newViewed);
		}
	}
	
	public function markAllArticleCommentsReadByVisitor($categoryIds = null, $newRead = null)
	{
		$articleFinder = $this->findArticlesForArticleList($categoryIds) 
			->withUnreadCommentsOnly();
	
		$articleItems = $articleFinder->fetch();
	
		foreach ($articleItems AS $articleItem)
		{
			$this->markArticleCommentsReadByVisitor($articleItem, $newRead);
		}
	}
	
	public function markArticleItemReadByVisitor(ArticleItem $articleItem, $newRead = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if ($newRead === null)
		{
			$newRead = \XF::$time;
		}
	
		$cutOff = $this->getReadMarkingCutOff();
		if ($newRead <= $cutOff)
		{
			return false;
		}
		
		$read = $articleItem->Read[$visitor->user_id];
		if ($read && $newRead <= $read->article_read_date)
		{
			return false;
		}
	
		$session = $this->app()->session();
		$articlesUnread = $session->get('amsUnreadArticles');  
		if (isset($articlesUnread['unread'][$articleItem->article_id]))
		{
			unset($articlesUnread['unread'][$articleItem->article_id]);
			$session->set('amsUnreadArticles', $articlesUnread);
		}
	
		$this->db()->insert('xf_xa_ams_article_read', [
			'article_id' => $articleItem->article_id,
			'user_id' => $visitor->user_id,
			'article_read_date' => $newRead
		], false, 'article_read_date = VALUES(article_read_date)');
	
		return true;
	}
	
	public function markArticleCommentsReadByVisitor(ArticleItem $articleItem, $newRead = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if ($newRead === null)
		{
			$newRead = \XF::$time;
		}
	
		$cutOff = $this->getReadMarkingCutOff();
		if ($newRead <= $cutOff)
		{
			return false;
		}
	
		$viewed = $articleItem->CommentRead[$visitor->user_id];
		if ($viewed && $newRead <= $viewed->comment_read_date)
		{
			return false;
		}
	
		$this->db()->insert('xf_xa_ams_comment_read', [
			'article_id' => $articleItem->article_id,
			'user_id' => $visitor->user_id,
			'comment_read_date' => $newRead
		], false, 'comment_read_date = VALUES(comment_read_date)');
	
		return true;
	}
	
	public function getReadMarkingCutOff()
	{
		return \XF::$time - $this->options()->readMarkingDataLifetime * 86400;
	}
	
	public function pruneArticleReadLogs($cutOff = null)
	{
		if ($cutOff === null)
		{
			$cutOff = $this->getReadMarkingCutOff();
		}
	
		$this->db()->delete('xf_xa_ams_article_read', 'article_read_date < ?', $cutOff);
	}

	public function autoUnfeatureArticles()
	{
		// do not allow this to run unless the auto unfeature option is enabled!
	
		if ($this->options()->xaAmsAutoUnfeatureArticles['enabled'])
		{
			$cutOffDays = $this->options()->xaAmsAutoUnfeatureArticles['days'];
			$cutOffDate = \XF::$time - ($cutOffDays * 86400);
				
			/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
			$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
	
			$articleFinder
				->with('Featured', true)
				->where('Featured.feature_date', '<', $cutOffDate);
				
			$featuredArticles = $articleFinder->fetch();
				
			foreach ($featuredArticles AS $article)
			{
				/** @var \XenAddons\AMS\Service\ArticleItem\Feature $featurer */
				$featurer = $this->app()->service('XenAddons\AMS:ArticleItem\Feature', $article);
	
				$featurer->unfeature();
			}
		}
	}
	
	public function publishScheduledArticles()
	{
		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = $this->finder('XenAddons\AMS:ArticleItem');
		
		$articleFinder
			->where('article_state', 'awaiting')
			->where('publish_date', '<=', \XF::$time);
		
		$awaitingArticles = $articleFinder->fetch();
		
		foreach ($awaitingArticles AS $article)
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\PublishDraft $draftPublisher */
			$draftPublisher = \XF::service('XenAddons\AMS:ArticleItem\PublishDraft', $article);
			$draftPublisher->setNotifyRunTime(1); // may be a lot happening
			$draftPublisher->publishDraft(true);
		}		
	}
	
	public function getArticleAttachmentConstraints()
	{
		$options = $this->options();
	
		return [
			'extensions' => Arr::stringToArray($options->xaAmsAllowedFileExtensions),
			'size' => $options->xaAmsArticleAttachmentMaxFileSize * 1024,
			'width' => $options->attachmentMaxDimensions['width'],
			'height' => $options->attachmentMaxDimensions['height']
		];
	}	

	public function sendModeratorActionAlert(
		\XenAddons\AMS\Entity\ArticleItem $article, $action, $reason = '', array $extra = [], \XF\Entity\User $forceUser = null
	)
	{
		if (!$forceUser)
		{
			if (!$article->user_id || !$article->User)
			{
				return false;
			}

			$forceUser = $article->User;
		}

		$extra = array_merge([
			'title' => $article->title,
			'prefix_id' => $article->prefix_id,
			'link' => $this->app()->router('public')->buildLink('nopath:ams', $article),
			'reason' => $reason
		], $extra);

		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = $this->repository('XF:UserAlert');
		$alertRepo->alert(
			$forceUser,
			0, '',
			'user', $forceUser->user_id,
			"ams_article_{$action}", $extra,
			['dependsOnAddOnId' => 'XenAddons/AMS']
		);

		return true;
	}

	public function addArticleEmbedsToContent($content, $metadataKey = 'embed_metadata', $articleGetterKey = 'AmsArticles', $pageGetterKey = 'AmsPages', $seriesGetterKey = 'AmsSeries')
	{
		if (!$content)
		{
			return;
		}
	
		$articleIds = [];
		$pageIds = [];
		$seriesIds = [];
		foreach ($content AS $item)
		{
			$metadata = $item->{$metadataKey};
			if (isset($metadata['amsEmbeds']['article']))
			{
				$articleIds = array_merge($articleIds, $metadata['amsEmbeds']['article']);
			}
			if (isset($metadata['amsEmbeds']['page']))
			{
				$pageIds = array_merge($pageIds, $metadata['amsEmbeds']['page']);
			}
			if (isset($metadata['amsEmbeds']['series']))
			{
				$seriesIds = array_merge($seriesIds, $metadata['amsEmbeds']['series']);
			}
		}
	
		$visitor = \XF::visitor();
	
		$series = [];
		$articles = [];
		$pages = [];
		
		if ($articleIds)
		{
			$articles = $this->finder('XenAddons\AMS:ArticleItem')
				->with('Category.Permissions|' . $visitor->permission_combination_id)
				->whereIds(array_unique($articleIds))
				->orderByDate()
				->fetch();
		}
		
		if ($pageIds)
		{
			$pages = $this->finder('XenAddons\AMS:ArticlePage')
				->with('Article.Category.Permissions|' . $visitor->permission_combination_id)
				->whereIds(array_unique($pageIds))
				//->orderByDate()
				->fetch();
		}
	
		foreach ($content AS $item)
		{
			$metadata = $item->{$metadataKey};
			if (isset($metadata['amsEmbeds']['article']))
			{
				$amsArticles = [];
				foreach ($metadata['amsEmbeds']['article'] AS $id)
				{
					if (!isset($articles[$id]))
					{
						continue;
					}
					$amsArticles[$id] = $articles[$id];
				}
	
				$item->{"set$articleGetterKey"}($amsArticles);
			}
			
			if (isset($metadata['amsEmbeds']['page']))
			{
				$amsPages = [];
				foreach ($metadata['amsEmbeds']['page'] AS $id)
				{
					if (!isset($pages[$id]))
					{
						continue;
					}
					$amsPages[$id] = $pages[$id];
				}
			
				$item->{"set$pageGetterKey"}($amsPages);
			}
			
			if (isset($metadata['amsEmbeds']['series']))
			{
				$amsSeries = [];
				foreach ($metadata['amsEmbeds']['series'] AS $id)
				{
					if (!isset($series[$id]))
					{
						continue;
					}
					$amsSeries[$id] = $series[$id];
				}
			
				$item->{"set$seriesGetterKey"}($amsSeries);
			}			
		}
	}	
	
	public function getUserArticleCount($userId)
	{
		return $this->db()->fetchOne("
			SELECT COUNT(*)
			FROM xf_xa_ams_article
			WHERE user_id = ?
				AND article_state = 'visible'
		", $userId);
	}
	
	/**
	 * @param $url
	 * @param null $type
	 * @param null $error
	 *
	 * @return null|\XF\Mvc\Entity\Entity
	 */
	public function getArticleFromUrl($url, $type = null, &$error = null)
	{
		$routePath = $this->app()->request()->getRoutePathFromUrl($url);
		$routeMatch = $this->app()->router($type)->routeToController($routePath);
		$params = $routeMatch->getParameterBag();
	
		if (!$params->article_id)
		{
			$error = \XF::phrase('xa_ams_no_article_id_could_be_found_from_that_url');
			return null;
		}
	
		$article = $this->app()->find('XenAddons\AMS:ArticleItem', $params->article_id);
		if (!$article)
		{
			$error = \XF::phrase('xa_ams_no_article_could_be_found_with_id_x', ['article_id' => $params->article_id]);
			return null;
		}
	
		return $article;
	}	
	
	/**
	 * @return int[]
	 */
	public function getArticleContributorCache(
		\XenAddons\AMS\Entity\ArticleItem $article
	): array
	{
		return $this->db()->fetchAllColumn(
			'SELECT user_id
			FROM xf_xa_ams_article_contributor
			WHERE article_id = ?',
			$article->article_id
		);
	}
	
	/**
	 * @return int[]
	 */
	public function rebuildArticleContributorCache(
		\XenAddons\AMS\Entity\ArticleItem $article
	): array
	{
		$cache = $this->getArticleContributorCache($article);
	
		$article->fastUpdate('contributor_user_ids', $cache);
		$article->clearCache('Contributors');
	
		return $cache;
	}
}