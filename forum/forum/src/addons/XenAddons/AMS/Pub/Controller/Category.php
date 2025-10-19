<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class Category extends AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		$this->assertNotEmbeddedImageRequest();
		
		$category = $this->assertViewableCategory($params->category_id, $this->getCategoryViewExtraWith());

		if ($this->responseType == 'rss')
		{
			return $this->getCategoryRss($category);
		}
		
		/** @var \XenAddons\AMS\ControllerPlugin\ArticleList $articleListPlugin */
		$articleListPlugin = $this->plugin('XenAddons\AMS:ArticleList');

		$categoryParams = $articleListPlugin->getCategoryListData($category);

		/** @var \XF\Tree $categoryTree */
		$categoryTree = $categoryParams['categoryTree'];
		$descendants = $categoryTree->getDescendants($category->category_id);

		$sourceCategoryIds = array_keys($descendants);
		$sourceCategoryIds[] = $category->category_id;

		$category->cacheViewableDescendents($descendants);

		$listParams = $articleListPlugin->getArticleListData($sourceCategoryIds, $category);

		$this->assertValidPage(
			$listParams['page'],
			$listParams['perPage'],
			$listParams['total'],
			'ams/categories',
			$category
		);
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/categories', $category, $listParams['page']));
		
		if ($category->layout_type)
		{
			$layoutType = $category->layout_type;
		}
		else 
		{
			$layoutType = $this->options()->xaAmsArticleListLayoutType;
		}
		
		$viewParams = [
			'category' => $category,
			'pendingApproval' => $this->filter('pending_approval', 'bool'),
			'layoutType' => $layoutType
		];
		$viewParams += $categoryParams + $listParams;

		return $this->view('XenAddons\AMS:Category\View', 'xa_ams_category_view', $viewParams);
	}

	protected function getCategoryViewExtraWith()
	{
		$extraWith = [];
		$userId = \XF::visitor()->user_id;
		if ($userId)
		{
			$extraWith[] = 'Watch|' . $userId;
		}

		return $extraWith;
	}

	public function actionFilters(ParameterBag $params)
	{
		$category = $this->assertViewableCategory($params->category_id);

		/** @var \XenAddons\AMS\ControllerPlugin\ArticleList $articleListPlugin */
		$articleListPlugin = $this->plugin('XenAddons\AMS:ArticleList');

		return $articleListPlugin->actionFilters($category);
	}

	public function actionFeatured(ParameterBag $params)
	{
		$category = $this->assertViewableCategory($params->category_id);

		/** @var \XenAddons\AMS\ControllerPlugin\ArticleList $articleListPlugin */
		$articleListPlugin = $this->plugin('XenAddons\AMS:ArticleList');

		return $articleListPlugin->actionFeatured($category);
	}
	
	public function actionMarkRead(ParameterBag $params)
	{
		$category = $this->assertViewableCategory($params->category_id);
	
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return $this->noPermission();
		}
	
		$markDate = $this->filter('date', 'uint');
		if (!$markDate)
		{
			$markDate = \XF::$time;
		}
	
		if ($this->isPost())
		{
			/** @var \XenAddons\AMS\ControllerPlugin\ArticleList $articleListPlugin */
			$articleListPlugin = $this->plugin('XenAddons\AMS:ArticleList');
			
			$categoryParams = $articleListPlugin->getCategoryListData($category);
			
			/** @var \XF\Tree $categoryTree */
			$categoryTree = $categoryParams['categoryTree'];
			$descendants = $categoryTree->getDescendants($category->category_id);
			
			$categoryIds = array_keys($descendants);
			$categoryIds[] = $category->category_id;
			
			$articleRepo = $this->getArticleRepo();
			$articleRepo->markArticlesReadByVisitor($categoryIds, $markDate);
			$articleRepo->markAllArticleCommentsReadByVisitor($categoryIds, $markDate);
	
			return $this->redirect(
				$this->buildLink('ams/categories', $category),
				\XF::phrase('xa_ams_category_x_marked_as_read', ['title' => $category->title])
			);
		}
		else
		{
			$viewParams = [
				'category' => $category,
				'date' => $markDate
			];
			return $this->view('XenAddons\AMS:Category\MarkRead', 'xa_ams_category_mark_read', $viewParams);
		}
	}	

	public function actionWatch(ParameterBag $params)
	{
		$category = $this->assertViewableCategory($params->category_id);
		if (!$category->canWatch($error))
		{
			return $this->noPermission($error);
		}

		$visitor = \XF::visitor();

		if ($this->isPost())
		{
			if ($this->filter('stop', 'bool'))
			{
				$action = 'delete';
				$config = [];
			}
			else
			{
				$action = 'watch';
				$config = $this->filter([
					'notify_on' => 'str',
					'send_alert' => 'bool',
					'send_email' => 'bool',
					'include_children' => 'bool'
				]);
			}

			/** @var \XenAddons\AMS\Repository\CategoryWatch $watchRepo */
			$watchRepo = $this->repository('XenAddons\AMS:CategoryWatch');
			$watchRepo->setWatchState($category, $visitor, $action, $config);

			$redirect = $this->redirect($this->buildLink('ams/categories', $category));
			$redirect->setJsonParam('switchKey', $action == 'delete' ? 'watch' : 'unwatch');
			return $redirect;
		}
		else
		{
			$viewParams = [
				'category' => $category,
				'isWatched' => !empty($category->Watch[$visitor->user_id])
			];
			return $this->view('XenAddons\AMS:Category\Watch', 'xa_ams_category_watch', $viewParams);
		}
	}

	/**
	 * @param \XenAddons\AMS\Entity\Category $category
	 *
	 * @return \XenAddons\AMS\Service\ArticleItem\Create
	 */
	protected function setupArticleCreate(\XenAddons\AMS\Entity\Category $category)
	{
		$title = $this->filter('title', 'str');

		$message = $this->plugin('XF:Editor')->fromInput('message');

		/** @var \XenAddons\AMS\Service\ArticleItem\Create $creator */
		$creator = $this->service('XenAddons\AMS:ArticleItem\Create', $category);
		
		$creator->setContent($title, $message);
		
		// This is for allowing Authors to save an article as a draft and publish it at a later time.
		if ($this->filter('save_as_draft', 'str'))
		{
			$article = $creator->getArticle();
			if ($article->canSaveAsDraft())
			{
				$creator->setArticleState('draft');
			}	
		}
		
		$prefixId = $this->filter('prefix_id', 'uint');
		if ($prefixId && $category->isPrefixUsable($prefixId))
		{
			$creator->setPrefix($prefixId);
		}

		if ($category->canEditTags())
		{
			$creator->setTags($this->filter('tags', 'str'));
			
			if ($category->thread_node_id && $category->thread_set_article_tags)
			{
				$creator->setAssociatedThreadTags($this->filter('tags', 'str'));
			}
		}

		if ($category->canUploadAndManageArticleAttachments())
		{
			$creator->setArticleAttachmentHash($this->filter('attachment_hash', 'str'));
		}
		
		if ($category->allow_author_rating)
		{
			$creator->setAuthorRating($this->filter('author_rating', 'float'));
		}		
		
		$originalSourceInput = $this->filter([
			'os_article_author' => 'str',
			'os_article_title' => 'str',
			'os_article_date' => 'datetime',
			'os_source_name' => 'str',
			'os_source_url' => 'str',
		]);

		$bulkInput = $this->filter([
			'description' => 'str',
			'meta_description' => 'str',
			'overview_page_title' => 'str',
			'overview_page_nav_title' => 'str',
			'location' => 'str',
			'cover_image_above_article' => 'bool',
			'about_author' => 'bool',
			'comments_open' => 'bool',
			'ratings_open' => 'bool',
		]);
		$bulkInput['original_source'] = $originalSourceInput;
		$creator->getArticle()->bulkSet($bulkInput);

		$customFields = $this->filter('custom_fields', 'array');
		$creator->setCustomFields($customFields);

		$pollQuestion = $this->filter('poll.question', 'str');
		if ($category->canCreatePoll() && strlen($pollQuestion))
		{
			$pollCreator = $this->plugin('XF:Poll')->setupPollCreate('ams_article', $creator->getArticle());
			$creator->setPollCreator($pollCreator);
		}
		
		return $creator;
	}

	protected function finalizeArticleCreate(\XenAddons\AMS\Service\ArticleItem\Create $creator)
	{
		$creator->sendNotifications();
		
		$article = $creator->getArticle();

		if (\XF::visitor()->user_id)
		{
			$creator->getCategory()->draft_article->delete();

			if ($article->article_state == 'moderated')
			{
				$this->session()->setHasContentPendingApproval();
			}
		}
		
		$visitor = \XF::visitor();
		
		/** @var \XenAddons\AMS\Repository\ArticleWatch $watchRepo */
		$watchRepo = $this->repository('XenAddons\AMS:ArticleWatch');
		$watchRepo->autoWatchAmsArticleItem($article, $visitor, true);
	}

	public function actionAdd(ParameterBag $params)
	{
		$category = $this->assertViewableCategory($params->category_id);
		if (!$category->canAddArticle($error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			$creator = $this->setupArticleCreate($category);
			$creator->checkForSpam();

			if (!$creator->validate($errors))
			{
				return $this->error($errors);
			}
			$this->assertNotFlooding('post');

			/** @var \XenAddons\AMS\Entity\ArticleItem $article */
			$article = $creator->save();
			$this->finalizeArticleCreate($creator);

			if (!$article->canView())
			{
				return $this->redirect($this->buildLink('ams/categories', $category, ['pending_approval' => 1]));
			}
			else
			{
				return $this->redirect($this->buildLink('ams', $article));
			}
		}
		else
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');

			$draft = $category->draft_article;

			if ($category->canUploadAndManageArticleAttachments())
			{
				$attachmentData = $attachmentRepo->getEditorData('ams_article', $category, $draft->attachment_hash);
			}
			else
			{
				$attachmentData = null;
			}

			$article = $category->getNewArticle();

			$article->title = $draft->title ?: '';
			$article->prefix_id = $draft->prefix_id ?: 0;
			$article->description = $draft->description ?: '';
			$article->message = $draft->message ?: '';
			
			if ($category->draft_article->tags)
			{
				// do nothing for now!  Might expand to FORCE default tags at some point in the future			
			}
			else 
			{
				// Adds the categories default tags to preload in the tags input for creating new articles
				$category->draft_article->tags = $category->default_tags;
			}
			
			if ($draft->custom_fields)
			{
				/** @var \XF\CustomField\Set $customFields */
				$customFields = $article->custom_fields;
				$customFields->bulkSet($draft->custom_fields, null, 'user', true);
			}
			
			$viewParams = [
				'category' => $category,
				'article' => $article,
				'prefixes' => $category->getUsablePrefixes(),

				'attachmentData' => $attachmentData,
			];
			return $this->view('XenAddons\AMS:Category\AddArticle', 'xa_ams_category_add_article', $viewParams);
		}
	}

	public function actionDraft(ParameterBag $params)
	{
		$this->assertPostOnly();

		$category = $this->assertViewableCategory($params->category_id);
		if (!$category->canAddArticle($error))
		{
			return $this->noPermission($error);
		}

		$creator = $this->setupArticleCreate($category);
		$article = $creator->getArticle();

		$fromInput = $this->filter([
			'tags' => 'str',
			'attachment_hash' => 'str',
		]);

		$extraData = [
			'prefix_id' => $article->prefix_id,
			'title' => $article->title,
			'description' => $article->description,
			'location' => $article->location,
			'custom_fields' => $article->custom_fields->getFieldValues()
		] + $fromInput;

		if ($category->canCreatePoll() && $this->filter('poll.question', 'str'))
		{
			$pollPlugin = $this->plugin('XF:Poll');
			$extraData['poll'] = $pollPlugin->getPollInput();
		}
		
		/** @var \XF\ControllerPlugin\Draft $draftPlugin */
		$draftPlugin = $this->plugin('XF:Draft');
		
		return $draftPlugin->actionDraftMessage($category->draft_article, $extraData, 'message');
	}

	public function actionPreview(ParameterBag $params)
	{
		$this->assertPostOnly();

		$category = $this->assertViewableCategory($params->category_id);
		if (!$category->canAddArticle($error))
		{
			return $this->noPermission($error);
		}

		$creator = $this->setupArticleCreate($category);

		if (!$creator->validate($errors))
		{
			return $this->error($errors);
		}

		$article = $creator->getArticle();

		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');

		if ($category && $category->canUploadAndManageArticleAttachments())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_article', $article, $tempHash);
			$attachments = $attachmentData['attachments'];
		}

		return $this->plugin('XF:BbCodePreview')->actionPreview(
			$article->message, 'ams_article', $article->User, $attachments, $article->canViewArticleAttachments()
		);
	}
	
	protected function getCategoryRss(\XenAddons\AMS\Entity\Category $category = null)
	{
		$limit = $this->options()->discussionsPerPage;
		
		$articleRepo = $this->getArticleRepo();
		$articleList = $articleRepo->findArticlesForRssFeed($category)->limit($limit * 3);
	
		$order = $this->filter('order', 'str');
		switch ($order)
		{
			case 'last_update':
				break;
	
			default:
				$order = 'publish_date';
				break;
		}
		$articleList->order($order, 'DESC');
	
		$articles = $articleList->fetch()->filterViewable()->slice(0, $limit);
	
		return $this->view('XenAddons\AMS:Category\Rss', '', [
			'category' => $category,
			'order' => $order,
			'articles' => $articles
		]);
	}	

	public static function getActivityDetails(array $activities)
	{
		return self::getActivityDetailsForContent(
			$activities, \XF::phrase('xa_ams_viewing_article_category'), 'category_id',
			function(array $ids)
			{
				$categories = \XF::em()->findByIds(
					'XenAddons\AMS:Category',
					$ids,
					['Permissions|' . \XF::visitor()->permission_combination_id]
				);

				$router = \XF::app()->router('public');
				$data = [];

				foreach ($categories->filterViewable() AS $id => $category)
				{
					$data[$id] = [
						'title' => $category->title,
						'url' => $router->buildLink('ams/categories', $category)
					];
				}

				return $data;
			}
		);
	}
	
	public function actionPrefixHelp(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		$category = $this->assertViewableCategory($params->category_id);
	
		$prefixId = $this->filter('prefix_id', 'uint');
		if ($prefixId && $category->isPrefixUsable($prefixId))
		{
			$prefix = $this->em()->find('XenAddons\AMS:ArticlePrefix', $prefixId);
	
			return $this->view('XenAddons\AMS:Category\PrefixHelp', 'prefix_usage_help', [
				'prefix' => $prefix,
				'contentType' => 'ams_article'
			]);
		}
	
		return $this->notFound();
	}
}