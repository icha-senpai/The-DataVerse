<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class ArticlePage extends AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		if ($params->page_id)
		{
			return $this->rerouteController(__CLASS__, 'view', $params);
		}
		
		if ($params->article_id)
		{
			return $this->redirect($this->buildLink('ams', $params));
		}
		
		return $this->redirect($this->buildLink('ams'));
	}
	
	protected function getArticleViewExtraWith()
	{
		$extraWith = ['CoverImage', 'Featured', 'SeriesPart', 'SeriesPart.Series'];
	
		$userId = \XF::visitor()->user_id;
		if ($userId)
		{
			$extraWith[] = 'Read|' . $userId;
			$extraWith[] = 'Watch|' . $userId;
			$extraWith[] = 'Reactions|' . $userId;
			$extraWith[] = 'Bookmarks|' . $userId;
			$extraWith[] = 'ReplyBans|' . $userId;
		}
	
		return $extraWith;
	}

	public function actionView(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id, $this->getArticleViewExtraWith());
		
		$articlePage = $this->assertViewablePage($params->page_id);
		
		$category = $article->Category;
		
		$page = $this->filterPage($params->page);
		$perPage = $this->options()->xaAmsCommentsPerPage;
	
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/page', $articlePage, $page));
	
		$articleRepo = $this->getArticleRepo();
		$commentRepo = $this->getCommentRepo();
		
		$articlePages = $this->em()->getEmptyCollection();
		$isFullView = false;
		$nextPage = false;
		$previousPage = false;
		if ($article->page_count)
		{
			/** @var \XenAddons\AMS\Repository\ArticlePage $articlePageRepo */
			$articlePageRepo = $this->repository('XenAddons\AMS:ArticlePage');
			$articlePages = $articlePageRepo->findPagesInArticle($article)->with('full')->fetch();
	
			if ($articlePages)
			{
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = \XF::repository('XF:Attachment');
				$attachmentRepo->addAttachmentsToContent($articlePages, 'ams_page');
			}
	
			if (!$isFullView) // prepare data for previous/next pages navigation!
			{
				$nextPage = $article->getNextPage($articlePages, $articlePage);
				$previousPage = $article->getPreviousPage($articlePages, $articlePage);
			}
				
			$article->page_count = $article->page_count + 1;  // this counts the article, plus the additional pages to get the correct count!
		}
	
		$seriesToc = $this->em()->getEmptyCollection();
		if ($article->isInSeries())
		{
			$seriesRepo = $this->getSeriesRepo();
			$seriesPartRepo = $this->getSeriesPartRepo();
				
			/** @var \XenAddons\AMS\Repository\SeriesPart $seriesPartRepo */
			$seriesPartFinder = $seriesPartRepo->findPartsInSeries($article->SeriesPart->Series)->forTOC();
			$seriesToc = $seriesPartFinder->fetch();
		}
	
		$nextSeriesPart = false;
		$previousSeriesPart = false;
		if ($seriesToc)
		{
			$nextSeriesPart = $article->getNextSeriesPart($seriesToc);
			$previousSeriesPart = $article->getPreviousSeriesPart($seriesToc);
		}
	
		/** @var \XenAddons\AMS\Entity\Comment[] $comments */
		$commentList = $commentRepo->findCommentsForContent($article)
			->limitByPage($page, $perPage);
		
		$comments = $commentList->fetch();
		$totalItems = $commentList->total();
		
		$articleRepo->markArticleItemReadByVisitor($article);
		
		// Only log views to non contributors (contributors include Author, Co-Authors and Contributors)
		if (!$article->isContributor())
		{
			$articleRepo->logArticleView($article);
		}
	
		$last = $comments->last();
		if ($last)
		{
			$articleRepo->markArticleCommentsReadByVisitor($article, $last->comment_date);
		}
		
		$this->assertValidPage($page, $perPage, $totalItems, 'ams', $article);
		
		$canInlineModComments = false;
		foreach ($comments AS $comment)
		{
			if ($comment->canUseInlineModeration())
			{
				$canInlineModComments = true;
				break;
			}
		}
		
		if ($comments)
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = \XF::repository('XF:Attachment');
			$attachmentRepo->addAttachmentsToContent($comments, 'ams_comment');
		}
		
		$latestReviews = $this->em()->getEmptyCollection();
		if ($article->real_review_count)
		{
			$recentReviewsMax = $this->options()->xaAmsRecentReviewsCount;
			if ($recentReviewsMax)
			{
				/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
				$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
				$latestReviews = $ratingRepo->findReviewsInArticle($article)->with('full')->fetch($recentReviewsMax);
		
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = \XF::repository('XF:Attachment');
				$attachmentRepo->addAttachmentsToContent($latestReviews, 'ams_rating');
			}
		}
		
		$canInlineModReviews = false;
		foreach ($latestReviews AS $review)
		{
			if ($review->canUseInlineModeration())
			{
				$canInlineModReviews = true;
				break;
			}
		}
		
		$excludeArticleIds = [];
		if ($this->options()->xaAmsCategoryOtherArticlesCount && $article->Category)
		{
			$categoryOthers = $this->getArticleRepo()
				->findOtherArticlesByCategory($article)
				->fetch($this->options()->xaAmsCategoryOtherArticlesCount);
			$categoryOthers = $categoryOthers->filterViewable();
			$excludeArticleIds = $categoryOthers->pluckNamed('article_id');
		}
		else
		{
			$categoryOthers = $this->em()->getEmptyCollection();
		}
	
		if ($this->options()->xaAmsAuthorOtherArticlesCount && $articlePage->User)
		{
			$authorOthers = $this->getArticleRepo()
				->findOtherArticlesByAuthor($articlePage->User, $article->article_id, $excludeArticleIds)
				->fetch($this->options()->xaAmsAuthorOtherArticlesCount);
			$authorOthers = $authorOthers->filterViewable();
		}
		else
		{
			$authorOthers = $this->em()->getEmptyCollection();
		}
	
		if ($category && $category->canUploadAndManageCommentImages())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_comment', $category);
		}
		else
		{
			$attachmentData = null;
		}

		$pagePoll = (($articlePage && $articlePage->has_poll) ? $articlePage->Poll : null);
		
		/** @var \XF\Repository\UserAlert $userAlertRepo */
		$userAlertRepo = $this->repository('XF:UserAlert');
		$userAlertRepo->markUserAlertsReadForContent('ams_article', $article->article_id);
		$userAlertRepo->markUserAlertsReadForContent('ams_comment', $comments->keys());
		$userAlertRepo->markUserAlertsReadForContent('ams_rating', $latestReviews->keys());
		
		$viewParams = [
			'articlePage' => $articlePage,
					
			'article' => $article,
			'category' => $article->Category,

			'pagePoll' => $pagePoll,
			
			'articlePages' => $articlePages,
			'nextPage' => $nextPage,
			'previousPage' => $previousPage,
				
			'seriesToc' => $seriesToc,
			'nextSeriesPart' => $nextSeriesPart,
			'previousSeriesPart' => $previousSeriesPart,
			
			'comments' => $comments,
			'attachmentData' => $attachmentData,
			
			'latestReviews' => $latestReviews,
			'categoryOthers' => $categoryOthers,
			'authorOthers' => $authorOthers,
			
			'page' => $page,
			'perPage' => $perPage,
			'totalItems' => $totalItems,
			'pageNavHash' => '>0:#comments',
				
			'canInlineModComments' => $canInlineModComments,
			'canInlineModReviews' => $canInlineModReviews
		];
		return $this->view('XenAddons\AMS:ArticlePage\View', 'xa_ams_article_page_view', $viewParams);
	}

	public function actionCoverImage(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id, ['CoverImage']);
	
		if (!$page->CoverImage)
		{
			return $this->notFound();
		}
	
		$this->request->set('no_canonical', 1);
	
		return $this->rerouteController('XF:Attachment', 'index', ['attachment_id' => $page->CoverImage->attachment_id]);
	}
	
	/**
	 * @param \XenAddons\AMS\Entity\ArticlePage $page
	 *
	 * @return \XenAddons\AMS\Service\Page\Edit
	 */
	protected function setupPageEdit(\XenAddons\AMS\Entity\ArticlePage $page)
	{
		/** @var \XenAddons\AMS\Service\Page\Edit $editor */
		$editor = $this->service('XenAddons\AMS:Page\Edit', $page);

		$editor->setTitle($this->filter('title', 'str'));
		$editor->setNavTitle($this->filter('nav_title', 'str'));
		
		$message = $this->plugin('XF:Editor')->fromInput('message');
		$editor->setMessage($message);
		
		$basicFields = $this->filter([
			'display_order' => 'int',
			'depth' => 'int',
			'page_state' => 'str',
			'cover_image_above_page' => 'bool',
			'display_byline' => 'bool',
			'meta_description' => 'str',
		]);
		$page->bulkSet($basicFields);

		$page->edit_date = time();
		
		if ($page->Article->Category->canUploadAndManagePageAttachments())
		{
			$editor->setAttachmentHash($this->filter('attachment_hash', 'str'));
		}
		
		return $editor;
	}	
	
	public function actionEdit(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		if (!$page->canEdit($error))
		{
			return $this->noPermission($error);
		}	
		
		$article = $page->Article;
		$category = $page->Article->Category;

		if ($this->isPost())
		{
			$oldPageState = $page->page_state;
			
			$editor = $this->setupPageEdit($page);
			
			if (!$editor->validate($errors))
			{
				return $this->error($errors);
			}
			
			$page = $editor->save();
			
			// If the page is in draft mode and changed to visible, we want to send notifications to watched users
			if ($oldPageState == 'draft' && $page->page_state == 'visible')
			{
				$editor->sendNotifications();
			}
			
			if ($this->filter('mp', 'bool'))
			{
				return $this->redirectToManagePages($page);
			}
			else 
			{
				return $this->redirect($this->buildLink('ams/page', $page));
			}			
		}
		else
		{
			if ($category && $category->canUploadAndManagePageAttachments())
			{
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = $this->repository('XF:Attachment');
				$attachmentData = $attachmentRepo->getEditorData('ams_page', $page);
			}
			else
			{
				$attachmentData = null;
			}
			
			$viewParams = [
				'page' => $page,
				'article' => $article,
				'category' => $category,
				'attachmentData' => $attachmentData,
				'from_page_management' => $this->filter('mp', 'bool')
			];
			return $this->view('XenAddons\AMS:Page\Edit', 'xa_ams_page_edit', $viewParams);
		}	
	}
	
	public function actionPreview(ParameterBag $params)
	{
		$this->assertPostOnly();
		
		$page = $this->assertViewablePage($params->page_id);
	
		$pageEditor = $this->setupPageEdit($page);
		if (!$pageEditor->validate($errors))
		{
			return $this->error($errors);
		}
	
		$page = $pageEditor->getPage();
	
		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');
	
		if ($page->Article->Category && $page->Article->Category->canUploadAndManagePageAttachments())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_page', $page, $tempHash);
			$attachments = $attachmentData['attachments'];
		}
	
		return $this->plugin('XF:BbCodePreview')->actionPreview(
			$page->message, 'ams_page', $page->Article->User, $attachments, $page->Article->canViewPageAttachments()
		);
	}	
	
	public function actionSetCoverImage(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		if (!$page->canSetCoverImage($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$page->cover_image_id = $this->filter('attachment_id', 'int');
			$page->cover_image_above_page = $this->filter('cover_image_above_page', 'int');
			$page->save();
				
			if ($this->filter('mp', 'bool'))
			{
				return $this->redirectToManagePages($page);
			}
			else
			{
				return $this->redirect($this->buildLink('ams/page', $page));
			}	
		}
		else
		{
			$viewParams = [
				'page' => $page,
				'article' => $page->Article,
				'category' => $page->Article->Category,
				'from_page_management' => $this->filter('mp', 'bool')
			];
			return $this->view('XF:ArticlePage\SetCoverImage', 'xa_ams_article_page_set_cover_image', $viewParams);
		}
	}	
	
	public function actionReassign(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		if (!$page->canReassign($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$user = $this->em()->findOne('XF:User', ['username' => $this->filter('username', 'str')]);
			if (!$user)
			{
				return $this->error(\XF::phrase('requested_user_not_found'));
			}
	
			$canTargetView = \XF::asVisitor($user, function() use ($page)
			{
				return $page->canView();
			});
			if (!$canTargetView)
			{
				return $this->error(\XF::phrase('xa_ams_new_owner_must_be_able_to_view_this_page'));
			}
	
			/** @var \XenAddons\AMS\Service\Page\Reassign $reassigner */
			$reassigner = $this->service('XenAddons\AMS:Page\Reassign', $page);
	
			if ($this->filter('alert', 'bool'))
			{
				$reassigner->setSendAlert(true, $this->filter('alert_reason', 'str'));
			}
	
			$reassigner->reassignTo($user);
			
			if ($this->filter('mp', 'bool'))
			{
				return $this->redirectToManagePages($page);
			}
			else
			{
				return $this->redirect($this->buildLink('ams/page', $page));
			}
		}
		else
		{
			$viewParams = [
				'page' => $page,
				'article' => $page->Article,
				'category' => $page->Article->Category,
				'from_page_management' => $this->filter('mp', 'bool')
			];
			return $this->view('XenAddons\AMS:Page\Reassign', 'xa_ams_page_reassign', $viewParams);
		}
	}	

	public function actionDelete(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		if (!$page->canDelete('soft', $error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			$type = $this->filter('hard_delete', 'bool') ? 'hard' : 'soft';
			$reason = $this->filter('reason', 'str');

			if (!$page->canDelete($type, $error))
			{
				return $this->noPermission($error);
			}
			
			/** @var \XenAddons\AMS\Service\Outcome\Deleter $deleter */
			$deleter = $this->service('XenAddons\AMS:Page\Deleter', $page);

			if ($this->filter('author_alert', 'bool') && $page->canSendModeratorActionAlert())
			{
				$deleter->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
			}
			
			$deleter->delete($type, $reason);
			
			if ($this->filter('mp', 'bool'))
			{
				return $this->redirectToManagePages($page);
			}
			else 
			{
				return $this->redirectToArticle($page->Article);
			}
		}
		else
		{
			$viewParams = [
				'page' => $page,
				'article' => $page->Article,
				'from_page_management' => $this->filter('mp', 'bool')
			];
			return $this->view('XenAddons\AMS:ArticlePage\Delete', 'xa_ams_page_delete', $viewParams);
		}
	}
	
	public function actionUndelete(ParameterBag $params)
	{
		$this->assertValidCsrfToken($this->filter('t', 'str'));
	
		$page = $this->assertViewablePage($params->page_id);
		if (!$page->canUndelete($error))
		{
			return $this->noPermission($error);
		}
	
		if ($page->page_state == 'deleted')
		{
			$page->page_state = 'visible';
			$page->save();
		}
	
		if ($this->filter('mp', 'bool'))
		{
			return $this->redirectToManagePages($page);
		}
		else 
		{
			return $this->redirect($this->buildLink('ams/page', $page));
		}
	}
	
	public function actionHistory(ParameterBag $params)
	{
		return $this->rerouteController('XF:EditHistory', 'index', [
			'content_type' => 'ams_page',
			'content_id' => $params->page_id
		]);
	}
	
	public function actionIp(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
	
		$article = $page->Article;
		$breadcrumbs = $article->Category->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Ip $ipPlugin */
		$ipPlugin = $this->plugin('XF:Ip');
		return $ipPlugin->actionIp($page, $breadcrumbs);
	}
	
	public function actionReport(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		if (!$page->canReport($error))
		{
			return $this->noPermission($error);
		}
	
		/** @var \XF\ControllerPlugin\Report $reportPlugin */
		$reportPlugin = $this->plugin('XF:Report');
		return $reportPlugin->actionReport(
			'ams_page', $page,
			$this->buildLink('ams/page/report', $page),
			$this->buildLink('ams/page', $page)
		);
	}	
	
	public function actionReact(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactSimple($page, 'ams/page');
	}
	
	public function actionReactions(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
	
		$breadcrumbs = $page->Content->getBreadcrumbs();
		$title = \XF::phrase('xa_ams_members_who_have_reacted_to_page_by_x', ['title' => $page->title , 'user' => $page->username]);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactions(
			$page,
			'ams/page/reactions',
			$title, $breadcrumbs
		);
	}
	
	public function actionWarn(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
	
		if (!$page->canWarn($error))
		{
			return $this->noPermission($error);
		}
	
		$article = $page->Article;
		$breadcrumbs = $article->Category->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Warn $warnPlugin */
		$warnPlugin = $this->plugin('XF:Warn');
		return $warnPlugin->actionWarn(
			'ams_page', $page,
			$this->buildLink('ams/page/warn', $page),
			$breadcrumbs
		);
	}
	
	public function actionPollCreate(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
	
		$breadcrumbs = $page->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionCreate('ams_page', $page, $breadcrumbs);
	}
	
	public function actionPollEdit(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		$poll = $page->Poll;
	
		$breadcrumbs = $page->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionEdit($poll, $breadcrumbs);
	}
	
	public function actionPollDelete(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		$poll = $page->Poll;
	
		$breadcrumbs = $page->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionDelete($poll, $breadcrumbs);
	}
	
	public function actionPollVote(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		$poll = $page->Poll;
	
		$breadcrumbs = $page->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionVote($poll, $breadcrumbs);
	}
	
	public function actionPollResults(ParameterBag $params)
	{
		$page = $this->assertViewablePage($params->page_id);
		$poll = $page->Poll;
	
		$breadcrumbs = $page->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionResults($poll, $breadcrumbs);
	}
	
	protected function redirectToManagePages(\XenAddons\AMS\Entity\ArticlePage $page)
	{
		$article = $page->Article;
	
		return $this->redirect($this->buildLink('ams/pages', $article));
	}
	
	protected function redirectToArticle(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		return $this->redirect($this->buildLink('ams', $article));
	}
	
	public static function getActivityDetails(array $activities)
	{
		return self::getActivityDetailsForContent(
			$activities, \XF::phrase('xa_ams_viewing_article'), 'article_id',
			function(array $ids)
			{
				$articles = \XF::em()->findByIds(
					'XenAddons\AMS:ArticleItem',
					$ids,
					['Category', 'Category.Permissions|' . \XF::visitor()->permission_combination_id]
				);

				$router = \XF::app()->router('public');
				$data = [];

				foreach ($articles->filterViewable() AS $id => $article)
				{
					$data[$id] = [
						'title' => $article->title,
						'url' => $router->buildLink('ams', $article)
					];
				}

				return $data;
			},
			\XF::phrase('xa_ams_viewing_articles')
		);
	}	
}