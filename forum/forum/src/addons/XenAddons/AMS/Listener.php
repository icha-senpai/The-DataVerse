<?php

namespace XenAddons\AMS;

class Listener
{
	public static function appSetup(\XF\App $app)
	{
		$container = $app->container();

		$container['prefixes.ams_article'] = $app->fromRegistry('xa_amsPrefixes',
			function(\XF\Container $c) { return $c['em']->getRepository('XenAddons\AMS:ArticlePrefix')->rebuildPrefixCache(); }
		);

		$container['customFields.ams_articles'] = $app->fromRegistry('xa_amsArticleFields',
			function(\XF\Container $c) { return $c['em']->getRepository('XenAddons\AMS:ArticleField')->rebuildFieldCache(); },
			function(array $amsArticleFieldsInfo)
			{
				$definitionSet = new \XF\CustomField\DefinitionSet($amsArticleFieldsInfo);
				$definitionSet->addFilter('display_on_list', function(array $field)
				{
					return (bool)$field['display_on_list'];
				});
				return $definitionSet;
			}
		);
		
		$container['customFields.ams_reviews'] = $app->fromRegistry('xa_amsReviewFields',
			function(\XF\Container $c) { return $c['em']->getRepository('XenAddons\AMS:ReviewField')->rebuildFieldCache(); },
			function(array $fields)
			{
				return new \XF\CustomField\DefinitionSet($fields);
			}
		);
	}
	
	public static function navigationSetup(\XF\Pub\App $app, array &$navigationFlat, array &$navigationTree)
	{
		$visitor = self::visitor();
		
		if (isset($navigationFlat['xa_ams']) 
			&& $visitor->canViewAmsArticles() 
			&& \XF::options()->xaAmsUnreadCounter)
		{
			$session = $app->session();
	
			$articlesUnread = $session->get('amsUnreadArticles');
			
			if ($articlesUnread)
			{
				$navigationFlat['xa_ams']['counter'] = count($articlesUnread['unread']);
			}
		}
	}	

	public static function appPubStartEnd(\XF\Pub\App $app)
	{
		$visitor = self::visitor();
		
		if ($visitor->user_id 
			&& $visitor->canViewAmsArticles()
			&& $visitor->canViewAmsArticlesQueue()
		)
		{
			$session = $app->session();
		
			$articlesQueue = array_replace([
				'pendingCount' => 0,
				'lastUpdateDate' => 0
			], $session->get('amsArticlesQueue') ?: []);
		
			if ($articlesQueue['lastUpdateDate']  < (\XF::$time - 5 * 60)) // 5 minutes
			{
				$articleRepo = \XF::repository('XenAddons\AMS:Article');
				$articles = $articleRepo->findArticlesPending()->fetch();
		
				$articlesQueue['pendingCount'] = $articles->count();
			}
		
			$articlesQueue['lastUpdateDate'] = \XF::$time;
			$session->set('amsArticlesQueue', $articlesQueue);
		}
	
		if ($visitor->user_id && $visitor->canViewAmsArticles() && \XF::options()->xaAmsUnreadCounter)
		{
			$session = $app->session();
	
			$articlesUnread = array_replace([
				'unread' => [],
				'lastUpdateDate' => 0
			], $session->get('amsUnreadArticles') ?: []);
	
			if ($articlesUnread['lastUpdateDate']  < (\XF::$time - 5 * 60)) // 5 minutes
			{
				$categoryRepo = \XF::repository('XenAddons\AMS:Category');
				$categoryList = $categoryRepo->getViewableCategories();
				$categoryIds = $categoryList->keys();
	
				$articleRepo = \XF::repository('XenAddons\AMS:Article');
				$articleItems = $articleRepo->findArticlesForArticleList($categoryIds)
					->unreadOnly($visitor->user_id)
					->orderByDate()
					->fetch();
	
				if ($articleItems->count())
				{
					$articlesUnread['unread'] = array_fill_keys($articleItems->keys(), true);
				}
			}
	
			$articlesUnread['lastUpdateDate'] = \XF::$time;
			$session->set('amsUnreadArticles', $articlesUnread);
		}
	}
	
	public static function criteriaUser($rule, array $data, \XF\Entity\User $user, &$returnValue)
	{
		switch ($rule)
		{
			case 'xa_ams_article_count': 
				if (isset($user->xa_ams_article_count) && $user->xa_ams_article_count >= $data['articles'])
				{
					$returnValue = true;
				}
				break;
				
			case 'xa_ams_article_count_nmt': 
				if (isset($user->xa_ams_article_count) && $user->xa_ams_article_count <= $data['articles'])
				{
					$returnValue = true;
				}
				break;	
				
			case 'xa_ams_featured_article_count':
				$articleRepo = \XF::repository('XenAddons\AMS:Article');
				$articleFinder = $articleRepo->findFeaturedArticlesForUser($user);
				$articleCount = $articleFinder->fetch()->count();
				if ($articleCount >= $data['articles'])
				{
					$returnValue = true;
				}
				break;
					
			case 'xa_ams_featured_article_count_nmt':
				$articleRepo = \XF::repository('XenAddons\AMS:Article');
				$articleFinder = $articleRepo->findFeaturedArticlesForUser($user);
				$articleCount = $articleFinder->fetch()->count();
				if ($articleCount <= $data['articles'])
				{
					$returnValue = true;
				}
				break;
					
			case 'xa_ams_comment_count':
				$commentRepo = \XF::repository('XenAddons\AMS:Comment');
				$commentFinder = $commentRepo->findCommentsForUser($user);
				$commentCount = $commentFinder->fetch()->count();
				if ($commentCount >= $data['comments'])
				{
					$returnValue = true;
				}
				break;
					
			case 'xa_ams_comment_count_nmt':
				$commentRepo = \XF::repository('XenAddons\AMS:Comment');
				$commentFinder = $commentRepo->findCommentsForUser($user);
				$commentCount = $commentFinder->fetch()->count();
				if ($commentCount <= $data['comments'])
				{
					$returnValue = true;
				}
				break;
			
			case 'xa_ams_review_count':
				$ratingRepo = \XF::repository('XenAddons\AMS:ArticleRating');
				$reviewFinder = $ratingRepo->findReviewsForUser($user);
				$reviewCount = $reviewFinder->fetch()->count();
				if ($reviewCount >= $data['reviews'])
				{
					$returnValue = true;
				}
				break;
			
			case 'xa_ams_review_count_nmt':
				$ratingRepo = \XF::repository('XenAddons\AMS:ArticleRating');
				$reviewFinder = $ratingRepo->findReviewsForUser($user);
				$reviewCount = $reviewFinder->fetch()->count();
				if ($reviewCount <= $data['reviews'])
				{
					$returnValue = true;
				}
				break;
				
			case 'xa_ams_series_count':
				if (isset($user->xa_ams_series_count) && $user->xa_ams_series_count >= $data['series'])
				{
					$returnValue = true;
				}
				break;
		}
	}
	
	public static function criteriaPage($rule, array $data, \XF\Entity\User $user, array $params, &$returnValue)
	{
		if (isset($params['breadcrumbs']) && is_array($params['breadcrumbs']) && !empty($data['ams_category_ids']) && isset($params['amsCategory']))
		{
			$returnValue = false;
	
			if (empty($data['ams_category_only']))
			{
				foreach ($params['breadcrumbs'] AS $i => $navItem)
				{
					if (isset($navItem['attributes']['category_id']) && in_array($navItem['attributes']['category_id'], $data['ams_category_ids']))
					{
						$returnValue = true;
					}
				}
			}
	
			if ($params['containerKey'])
			{
				list ($type, $id) = explode('-', $params['containerKey'], 2);
	
				if ($type == 'amsCategory' && $id && in_array($id, $data['ams_category_ids']))
				{
					$returnValue = true;
				}
			}
		}
	}	
	
	public static function criteriaTemplateData(array &$templateData)
	{
		$categoryRepo = \XF::repository('XenAddons\AMS:Category');
		$templateData['amsCategories'] = $categoryRepo->getCategoryOptionsData(false);
	}

	public static function templaterSetup(\XF\Container $container, \XF\Template\Templater &$templater)
	{
		/** @var \XenAddons\AMS\Template\TemplaterSetup $templaterSetup */
		$class = \XF::extendClass('XenAddons\AMS\Template\TemplaterSetup');
		$templaterSetup = new $class();
		
		$templater->addFunction('ams_article_thumbnail', [$templaterSetup, 'fnAmsArticleThumbnail']);
		$templater->addFunction('ams_article_page_thumbnail', [$templaterSetup, 'fnAmsArticlePageThumbnail']);
		$templater->addFunction('ams_category_icon', [$templaterSetup, 'fnAmsCategoryIcon']);
		$templater->addFunction('ams_series_icon', [$templaterSetup, 'fnAmsSeriesIcon']);
	}

	public static function templaterTemplatePreRenderPublicEditor(\XF\Template\Templater $templater, &$type, &$template, array &$params)
	{
		if (!self::visitor()->canViewAmsArticles())
		{
			$params['removeButtons'][] = 'xfCustom_ams';
		}
	}
	
	public static function editorDialog(array &$data, \XF\Pub\Controller\AbstractController $controller)
	{
		$controller->assertRegistrationRequired();
	
		$data['template'] = 'xa_ams_editor_dialog_ams';
	}	

	public static function userContentChangeInit(\XF\Service\User\ContentChange $changeService, array &$updates)
	{
		$updates['xf_xa_ams_article'] = [
			['user_id', 'username'],
			['last_comment_user_id', 'last_comment_username']
		];
		$updates['xf_xa_ams_article_contributor'] = ['user_id', 'emptyable' => false];
		$updates['xf_xa_ams_article_page'] = ['user_id', 'username'];
		$updates['xf_xa_ams_article_rating'] = [
			['user_id', 'username'],
			['author_response_contributor_user_id', 'author_response_contributor_username'],
		];
		$updates['xf_xa_ams_article_read'] = ['user_id', 'emptyable' => false];
		$updates['xf_xa_ams_article_reply_ban'] = ['user_id', 'emptyable' => false];
		$updates['xf_xa_ams_article_watch'] = ['user_id', 'emptyable' => false];
		
		$updates['xf_xa_ams_author_watch'] = [
			['user_id', 'emptyable' => false],
			['author_id', 'emptyable' => false]
		];
		
		$updates['xf_xa_ams_category_watch'] = ['user_id', 'emptyable' => false];
		
		$updates['xf_xa_ams_comment'] = ['user_id', 'username'];
		$updates['xf_xa_ams_comment_read'] = ['user_id', 'emptyable' => false];
		
		$updates['xf_xa_ams_series'] = ['user_id', 'emptyable' => false];
		$updates['xf_xa_ams_series_part'] = ['user_id', 'emptyable' => false];
		$updates['xf_xa_ams_series_watch'] = ['user_id', 'emptyable' => false];
	}

	public static function userDeleteCleanInit(\XF\Service\User\DeleteCleanUp $deleteService, array &$deletes)
	{
		$deletes['xf_xa_ams_article_contributor'] = 'user_id = ?';
		$deletes['xf_xa_ams_article_read'] = 'user_id = ?';
		$deletes['xf_xa_ams_article_watch'] = 'user_id = ?';
		$deletes['xf_xa_ams_author_watch'] = 'user_id = ?';
		$deletes['xf_xa_ams_category_watch'] = 'user_id = ?';
		$deletes['xf_xa_ams_comment_read'] = 'user_id = ?';
		$deletes['xf_xa_ams_series_watch'] = 'user_id = ?';
	}

	public static function userMergeCombine(
		\XF\Entity\User $target, \XF\Entity\User $source, \XF\Service\User\Merge $mergeService
	)
	{
		$target->xa_ams_article_count += $source->xa_ams_article_count;
		$target->xa_ams_series_count += $source->xa_ams_series_count;
	}

	public static function userSearcherOrders(\XF\Searcher\User $userSearcher, array &$sortOrders)
	{
		$sortOrders['xa_ams_article_count'] = \XF::phrase('xa_ams_ams_article_count');
		$sortOrders['xa_ams_series_count'] = \XF::phrase('xa_ams_ams_series_count');
	}
	
	public static function memberStatResultPrepare($order, array &$cacheResults)
	{
		switch ($order)
		{
			case 'xa_ams_article_count':
			case 'xa_ams_series_count':
				$cacheResults = array_map(function($value)
				{
					return \XF::language()->numberFormat($value);
				}, $cacheResults);
				break;
		}
	}	
	
	/**
	 * @return \XenAddons\AMS\XF\Entity\User
	 */
	public static function visitor()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		return $visitor;
	}	
}