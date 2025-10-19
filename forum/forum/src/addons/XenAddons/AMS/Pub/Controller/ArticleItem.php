<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;

class ArticleItem extends AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		if ($this->responseType == 'rss')
		{
			return $this->getAmsRss();
		}
		
		if ($params->article_id)
		{
			return $this->rerouteController(__CLASS__, 'view', $params);
		}
		
		if (isset($this->options()->xaAmsIndexPageType) && $this->options()->xaAmsIndexPageType == 'modular')
		{
			return $this->rerouteController(__CLASS__, 'modular', $params);
		}
		
		$this->assertNotEmbeddedImageRequest();

		/** @var \XenAddons\AMS\ControllerPlugin\ArticleList $articleListPlugin */
		$articleListPlugin = $this->plugin('XenAddons\AMS:ArticleList');

		$categoryParams = $articleListPlugin->getCategoryListData();
		$viewableCategoryIds = $categoryParams['categories']->keys();

		$listParams = $articleListPlugin->getArticleListData($viewableCategoryIds);
		
		$this->assertValidPage($listParams['page'], $listParams['perPage'], $listParams['total'], 'ams');
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams', null, $listParams['page']));
		
		$viewParams = $categoryParams + $listParams;

		return $this->view('XenAddons\AMS:Index', 'xa_ams_index', $viewParams);
	}
	
	public function actionModular(ParameterBag $params)
	{
		$viewParams = [];
		
		return $this->view('XenAddons\AMS:Modular', 'xa_ams_index_modular', $viewParams);		
	}

	public function actionFilters()
	{
		/** @var \XenAddons\AMS\ControllerPlugin\ArticleList $articleListPlugin */
		$articleListPlugin = $this->plugin('XenAddons\AMS:ArticleList');

		return $articleListPlugin->actionFilters();
	}

	public function actionFeatured()
	{
		/** @var \XenAddons\AMS\ControllerPlugin\ArticleList $articleListPlugin */
		$articleListPlugin = $this->plugin('XenAddons\AMS:ArticleList');

		return $articleListPlugin->actionFeatured();
	}

	public function actionLatestReviews()
	{
		$this->assertNotEmbeddedImageRequest();
		
		$viewableCategoryIds = $this->getCategoryRepo()->getViewableCategoryIds();

		$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
		$finder = $ratingRepo->findLatestReviews($viewableCategoryIds);

		$total = $finder->total();
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsReviewsPerPage;

		$this->assertValidPage($page, $perPage, $total, 'ams/latest-reviews');
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/latest-reviews', null, $page));

		$reviews = $finder->limitByPage($page, $perPage)->fetch();
		$reviews = $reviews->filterViewable();

		/** @var \XF\Repository\Attachment $attachmentRepo */
		$attachmentRepo = \XF::repository('XF:Attachment');
		$attachmentRepo->addAttachmentsToContent($reviews, 'ams_rating');
		
		$canInlineModReviews = false;
		foreach ($reviews AS $review)
		{
			if ($review->canUseInlineModeration())
			{
				$canInlineModReviews = true;
				break;
			}
		}
		
		$viewParams = [
			'reviews' => $reviews,
			'page' => $page,
			'perPage' => $perPage,
			'total' => $total,
			'canInlineModReviews' => $canInlineModReviews
		];
		return $this->view('XenAddons\AMS:LatestReviews', 'xa_ams_latest_reviews', $viewParams);
	}

	public function actionAdd()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		if (!$visitor->canAddAmsArticle($error))
		{
			return $this->noPermission($error);
		}

		$this->assertCanonicalUrl($this->buildLink('ams/add'));

		$categoryRepo = $this->getCategoryRepo();

		$categories = $categoryRepo->getViewableCategories();
		$canAdd = false;

		foreach ($categories AS $category)
		{
			/** @var \XenAddons\AMS\Entity\Category $category */
			if ($category->canAddArticle())
			{
				$canAdd = true;
				break;
			}
		}

		if (!$canAdd)
		{
			return $this->noPermission();
		}

		$categoryTree = $categoryRepo->createCategoryTree($categories);
		$categoryTree = $categoryTree->filter(null, function($id, \XenAddons\AMS\Entity\Category $category, $depth, $children)
		{
			if ($children)
			{
				return true;
			}
			if ($category->canAddArticle())
			{
				return true;
			}

			return false;
		});

		$categoryExtras = $categoryRepo->getCategoryListExtras($categoryTree);

		$viewParams = [
			'categoryTree' => $categoryTree,
			'categoryExtras' => $categoryExtras
		];
		return $this->view('XenAddons\AMS:ArticleItem\AddChooser', 'xa_ams_article_add_chooser', $viewParams);
	}

	public function actionView(ParameterBag $params)
	{
		$this->assertNotEmbeddedImageRequest();
		
		$article = $this->assertViewableArticle($params->article_id, $this->getArticleViewExtraWith());
		
		// This has been added to handle legacy pages links to redirect to the new pages controller using the new page url structure.
		if ($article_page_id = $this->filter('article_page', 'int'))
		{
			$articlePage = $this->assertViewablePage($article_page_id);
				
			if ($articlePage && $articlePage->article_id == $article->article_id)
			{
				return $this->redirect($this->buildLink('ams/page', $articlePage));
			}
			else
			{
				return $this->redirect($this->buildLink('ams', $article));
			}
		}
		
		$category = $article->Category;

		$page = $this->filterPage($params->page);
		$perPage = $this->options()->xaAmsCommentsPerPage;
		
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams', $article, $page));
		
		$articleRepo = $this->getArticleRepo();
		$commentRepo = $this->getCommentRepo();
		
		$trimmedArticle = null;
		
		if (!$article->canViewFullArticle())
		{
			$snippet = $this->app->stringFormatter()->wholeWordTrim($article->message, $this->options()->xaAmsLimitedViewArticleLength);
			if (strlen($snippet) < strlen($article->message))
			{
				$trimmedArticle = $this->app->bbCode()->render($snippet, 'bbCodeClean', 'ams_article', null);
			}
		}

		$articlePages = $this->em()->getEmptyCollection();
		$isFullView = false;
		$nextPage = false;
		$previousPage = false;
		if ($article->page_count && !$trimmedArticle)
		{
			if ($this->options()->xaAmsViewFullArticle)
			{
				$isFullView = $this->filter('full', 'bool');
			}
			
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
				$nextPage = $article->getNextPage($articlePages);
				$previousPage = $article->getPreviousPage($articlePages);
			}
			
			$article->page_count = $article->page_count + 1;  // this counts the article, plus the additional pages to get the correct count for a multi page article!
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
		
		if ($article->canViewContributors())
		{
			$contributors = $article->Contributors;
		}
		else
		{
			$contributors = $this->em()->getEmptyCollection();
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
		
		if ($this->options()->xaAmsAuthorOtherArticlesCount && $article->User)
		{
			$authorOthers = $this->getArticleRepo()
				->findOtherArticlesByAuthor($article->User, $article->article_id, $excludeArticleIds)
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

		$poll = ($article->has_poll ? $article->Poll : null);
		
		/** @var \XF\Repository\UserAlert $userAlertRepo */
		$userAlertRepo = $this->repository('XF:UserAlert');
		$userAlertRepo->markUserAlertsReadForContent('ams_article', $article->article_id);
		$userAlertRepo->markUserAlertsReadForContent('ams_comment', $comments->keys());
		$userAlertRepo->markUserAlertsReadForContent('ams_rating', $latestReviews->keys());
		
		$viewParams = [
			'article' => $article,
			'category' => $article->Category,
			'trimmedArticle' => $trimmedArticle,
			'contributors' => $contributors,

			'articlePages' => $articlePages,
			'isFullView' => $isFullView,
			'nextPage' => $nextPage, 
			'previousPage' => $previousPage,
				
			'poll' => $poll,
			
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
		return $this->view('XenAddons\AMS:ArticleItem\View', 'xa_ams_article_view', $viewParams);
	}

	public function actionCoverImage(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id, ['CoverImage']);
	
		if (!$article->CoverImage)
		{
			return $this->notFound();
		}
	
		$this->request->set('no_canonical', 1);
	
		return $this->rerouteController('XF:Attachment', 'index', ['attachment_id' => $article->CoverImage->attachment_id]);
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

	public function actionPages(ParameterBag $params)
	{
		$this->assertNotEmbeddedImageRequest();
		
		$article = $this->assertViewableArticle($params->article_id);
		
		if (!$article->canEdit($error))
		{
			return $this->noPermission($error);
		}
			
		/** @var \XenAddons\AMS\Repository\ArticlePage $articlePageRepo */
		$articlePageRepo = $this->repository('XenAddons\AMS:ArticlePage');
		$articlePages = $articlePageRepo->findPagesInArticleManagePages($article)->with('full')->fetch();
	
		if ($articlePages)
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = \XF::repository('XF:Attachment');
			$attachmentRepo->addAttachmentsToContent($articlePages, 'ams_page');
		}
		
		$viewParams = [
			'article' => $article,
			'category' => $article->Category,
			'articlePages' => $articlePages,
		];
		
		return $this->view('XenAddons\AMS:ArticleItem\Pages', 'xa_ams_article_pages', $viewParams);
	}
		
	public function actionField(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id, $this->getArticleViewExtraWith());

		$fieldId = $this->filter('field', 'str');
		$tabFields = $article->getExtraFieldTabs();

		if (!isset($tabFields[$fieldId]))
		{
			return $this->redirect($this->buildLink('ams', $article));
		}

		/** @var \XF\CustomField\Set $fieldSet */
		$fieldSet = $article->custom_fields;
		$definition = $fieldSet->getDefinition($fieldId);
		$fieldValue = $fieldSet->getFieldValue($fieldId);

		$viewParams = [
			'article' => $article,
			'category' => $article->Category,

			'fieldId' => $fieldId,
			'fieldDefinition' => $definition,
			'fieldValue' => $fieldValue
		];
		return $this->view('XenAddons\AMS:ArticleItem\Field', 'xa_ams_article_field', $viewParams);
	}
	
	public function actionMap(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id, $this->getArticleViewExtraWith());
	
		if ($this->options()->xaAmsLocationDisplayType != 'map_own_tab'
			|| !$this->options()->xaAmsGoogleMapsEmbedApiKey
			|| !$article->Category->allow_location
			|| !$article->location
			|| !$article->canViewArticleMap()
		)
		{
			return $this->redirect($this->buildLink('ams', $article));
		}
	
		$viewParams = [
			'article' => $article,
			'category' => $article->Category,
		];
		return $this->view('XenAddons\AMS:ArticleItem\Map', 'xa_ams_article_map', $viewParams);
	}
	
	public function actionGallery(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id, $this->getArticleViewExtraWith());
	
		if ($this->options()->xaAmsIncludePagesImages)
		{
			$pagesImages = $this->getPageRepo()->getPagesImagesForArticle($article);
		}
		else
		{
			$pagesImages = $this->em()->getEmptyCollection();
		}
		
		if (!$article->hasImageAttachments() && !$pagesImages)
		{
			return $this->redirect($this->buildLink('ams', $article));
		}
	
		$viewParams = [
			'article' => $article,
			'category' => $article->Category,
			'pagesImages' => $pagesImages
		];
		return $this->view('XenAddons\AMS:ArticleItem\Gallery', 'xa_ams_article_gallery', $viewParams);
	}	

	public function actionRatings(ParameterBag $params)
	{
		$this->assertNotEmbeddedImageRequest();
		
		$article = $this->assertViewableArticle($params->article_id);
	
		if (!$article->canManageRatings($error))
		{
			return $this->noPermission($error);
		}
			
		$page = $this->filterPage();
		$perPage = 20;
	
		/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
		$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
		$ratingFinder = $ratingRepo->findRatingsInArticle($article);
	
		$total = $ratingFinder->total();
		if (!$total)
		{
			return $this->redirect($this->buildLink('ams', $article));
		}
	
		$this->assertValidPage($page, $perPage, $total, 'ams/ratings', $article);
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/ratings', $article, $page));
	
		$ratingFinder->limitByPage($page, $perPage);
		$ratings = $ratingFinder->fetch();
		
		/** @var \XF\Repository\UserAlert $userAlertRepo */
		$userAlertRepo = $this->repository('XF:UserAlert');
		$userAlertRepo->markUserAlertsReadForContent('ams_rating', $ratings->keys());
		
		$viewParams = [
			'article' => $article,
			'category' => $article->Category,
			'ratings' => $ratings,
		];
	
		return $this->view('XenAddons\AMS:ArticleItem\Ratings', 'xa_ams_article_ratings', $viewParams);
	}
	
	public function actionReviews(ParameterBag $params)
	{
		if (!$params->article_id)
		{
			return $this->redirectPermanently($this->buildLink('ams/latest-reviews'));
		}
		
		$this->assertNotEmbeddedImageRequest();
		
		$article = $this->assertViewableArticle($params->article_id, $this->getArticleViewExtraWith());

		$this->assertCanonicalUrl($this->buildLink('ams/reviews', $article));

		$reviewId = $this->filter('rating_id', 'uint');
		if ($reviewId)
		{
			/** @var \XenAddons\AMS\Entity\ArticleRating|null $review */
			$review = $this->em()->find('XenAddons\AMS:ArticleRating', $reviewId);
			if (!$review || $review->article_id != $article->article_id || !$review->is_review)
			{
				return $this->noPermission();
			}
			if (!$review->canView($error))
			{
				return $this->noPermission($error);
			}

			return $this->redirectPermanently($this->buildLink('ams/review', $review));
		}

		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsReviewsPerPage;

		/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
		$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
		$reviewFinder = $ratingRepo->findReviewsInArticle($article);

		$filters = $this->getReviewFilterInput();
		$this->applyReviewFilters($reviewFinder, $filters);
		
		if (!$filters)
		{
			$total = $article->real_review_count;
			if (!$total)
			{
				return $this->redirect($this->buildLink('ams', $article));
			}
		}
		else
		{
			$total = $reviewFinder->total();
		}
			
		$this->assertValidPage($page, $perPage, $total, 'ams/reviews', $article);
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/reviews', $article, $page));

		$reviewFinder->with('full')->limitByPage($page, $perPage);
		$reviews = $reviewFinder->fetch();
		
		/** @var \XF\Repository\UserAlert $userAlertRepo */
		$userAlertRepo = $this->repository('XF:UserAlert');
		$userAlertRepo->markUserAlertsReadForContent('ams_rating', $reviews->keys());
		
		$effectiveOrder = $filters['order'] ?? 'rating_date';
		
		/** @var \XF\Repository\Attachment $attachmentRepo */
		$attachmentRepo = \XF::repository('XF:Attachment');
		$attachmentRepo->addAttachmentsToContent($reviews, 'ams_rating');

		$canInlineModReviews = false;
		foreach ($reviews AS $review)
		{
			if ($review->canUseInlineModeration())
			{
				$canInlineModReviews = true;
				break;
			}
		}
		
		$viewParams = [
			'article' => $article,
			'reviews' => $reviews,
			
			'filters' => $filters,
			'reviewTabs' => $this->getReviewTabs($article, $filters, $effectiveOrder),
			'effectiveOrder' => $effectiveOrder,

			'page' => $page,
			'perPage' => $perPage,
			'total' => $total,
			
			'canInlineModReviews' => $canInlineModReviews
		];
		return $this->view('enAddons\AMS:ArticleItem\Reviews', 'xa_ams_article_reviews', $viewParams);
	}
	
	protected function getReviewTabs(
		\XenAddons\AMS\Entity\ArticleItem $article,
		array $filters,
		string $effectiveOrder
	): array
	{
		$tabs = [
			'latest' => [
				'selected' => ($effectiveOrder == 'rating_date'),
				'filters' => array_replace($filters, [
					'order' => 'rating_date',
					'direction' => 'desc'
				])
			],
			'helpful' => [
				'selected' => ($effectiveOrder == 'vote_score'),
				'filters' => array_replace($filters, [
					'order' => 'vote_score',
					'direction' => 'desc'
				])
			],
			'rating' => [
				'selected' => ($effectiveOrder == 'rating'),
				'filters' => array_replace($filters, [
					'order' => 'rating',
					'direction' => 'desc'
				])
			]
		];
	
		$defaultOrder = 'rating_date';
		$defaultDirection = 'desc';
	
		foreach ($tabs AS $tabId => &$tab)
		{
			if (isset($tab['filters']['order']) && $tab['filters']['order'] == $defaultOrder)
			{
				$tab['filters']['order'] = null;
			}
			if (isset($tab['filters']['direction']) && $tab['filters']['direction'] == $defaultDirection)
			{
				$tab['filters']['direction'] = null;
			}
		}
	
		return $tabs;
	}
	
	public function actionReviewsFilters(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
	
		$filters = $this->getReviewFilterInput();
	
		if ($this->filter('apply', 'bool'))
		{
			return $this->redirect($this->buildLink('ams/reviews', $article, $filters));
		}
	
		$viewParams = [
			'article' => $article,
			'filters' => $filters
		];
		return $this->view('XenAddons\AMS:ArticleItem\ReviewsFilters', 'xa_ams_article_reviews_filters', $viewParams);
	}
	
	protected function getReviewFilterInput()
	{
		$filters = [];
	
		$input = $this->filter([
			'rating' => 'uint',
			'order' => 'str',
			'direction' => 'str'
		]);
	
		if ($input['rating'] >= 1 && $input['rating'] <= 5)
		{
			$filters['rating'] = $input['rating'];
		}
	
		$sorts = $this->getAvailableReviewSorts();
	
		if ($input['order'] && isset($sorts[$input['order']]))
		{
			if (!in_array($input['direction'], ['asc', 'desc']))
			{
				$input['direction'] = 'desc';
			}
	
			if ($input['order'] != 'rating_date' || $input['direction'] != 'desc')
			{
				$filters['order'] = $input['order'];
				$filters['direction'] = $input['direction'];
			}
		}
	
		return $filters;
	}
	
	protected function getAvailableReviewSorts()
	{
		// maps [name of sort] => field in/relative to ArticleRating entity
		return [
			'rating_date' => 'rating_date',
			'vote_score' => 'vote_score',
			'rating' => 'rating'
		];
	}
	
	protected function applyReviewFilters(\XenAddons\AMS\Finder\ArticleRating $reviewFinder, array $filters)
	{
		if (!empty($filters['rating']))
		{
			$reviewFinder->where('rating', $filters['rating']);
		}
	
		$sorts = $this->getAvailableReviewSorts();
	
		if (!empty($filters['order']) && isset($sorts[$filters['order']]))
		{
			$reviewFinder->order($sorts[$filters['order']], $filters['direction']);
		}
	}

	/**
	 * @param \XenAddons\AMS\Entity\ArticleItem $article
	 *
	 * @return \XenAddons\AMS\Service\ArticleItem\AddPage
	 */
	protected function setupAddPage(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		/** @var \XenAddons\AMS\Service\ArticleItem\AddPage $pageAdder */
		$pageAdder = $this->service('XenAddons\AMS:ArticleItem\AddPage', $article);

		$pageAdder->setTitle($this->filter('title', 'str'));
		$pageAdder->setNavTitle($this->filter('nav_title', 'str'));
		
		$pageAdder->setMessage($this->plugin('XF:Editor')->fromInput('message'));
		
		$basicFields = $this->filter([
			'display_order' => 'int',
			'depth' => 'int',
			'page_state' => 'str',
			'cover_image_above_page' => 'bool',
			'display_byline' => 'bool',
			'meta_description' => 'str',
		]);
		$pageAdder->getPage()->bulkSet($basicFields);
		
		if ($article->Category->canUploadAndManagePageAttachments())
		{
			$pageAdder->setAttachmentHash($this->filter('attachment_hash', 'str'));
		}
		
		return $pageAdder;		
	}	
	
	public function actionAddPage(ParameterBag $params)
	{
		$visitorUserId = \XF::visitor()->user_id;
		
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canAddPage($error))
		{
			return $this->noPermission($error);
		}
		
		$category = $article->Category;
		
		if ($this->isPost())
		{
			$pageAdder = $this->setupAddPage($article);
			//$pageAdder->checkForSpam(); TODO add this in at some point! 
			
			if (!$pageAdder->validate($errors))
			{
				return $this->error($errors);
			}
		
			$page = $pageAdder->save();
			
			$pageAdder->sendNotifications();
		
			if ($page->page_state == 'draft')
			{
				return $this->redirect($this->buildLink('ams/pages', $article));
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
				$attachmentData = $attachmentRepo->getEditorData('ams_page', $category);
			}
			else
			{
				$attachmentData = null;
			}
			
			$page = $article->getNewPage();
				
			$viewParams = [
				'page' => $page,
				'article' => $article,
				'category' => $article->Category,
				'attachmentData' => $attachmentData,
			];
			return $this->view('XF:ArticleItem\AddPage', 'xa_ams_article_add_page', $viewParams);
		}		
	}

	public function actionPagePreview(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		$article = $this->assertViewableArticle($params->article_id);
	
		$pageAdder = $this->setupAddPage($article);
		if (!$pageAdder->validate($errors))
		{
			return $this->error($errors);
		}
	
		$page = $pageAdder->getPage();
	
		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');
	
		if ($article->Category && $article->Category->canUploadAndManagePageAttachments())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_page', $page, $tempHash);
			$attachments = $attachmentData['attachments'];
		}
	
		return $this->plugin('XF:BbCodePreview')->actionPreview(
			$page->message, 'ams_page', $article->User, $attachments, $article->canViewPageAttachments()
		);
	}
	
	/**
	 * @param \XenAddons\AMS\Entity\ArticleItem $article
	 *
	 * @return \XenAddons\AMS\Service\ArticleItem\Rate
	 */
	protected function setupArticleRate(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		/** @var \XenAddons\AMS\Service\ArticleItem\Rate $rater */
		$rater = $this->service('XenAddons\AMS:ArticleItem\Rate', $article);

		$rater->setMessage($this->plugin('XF:Editor')->fromInput('message'));
		
		$input = $this->filter([
			'rating' => 'uint',
			'pros' => 'str',
			'cons' => 'str',
			'is_anonymous' => 'bool'
		]);

		$rater->setRating($input['rating'], $input['pros'], $input['cons']);

		if ($article->Category->allow_anon_reviews && $input['is_anonymous'])
		{
			$rater->setIsAnonymous();
		}
		
		if ($article->Category->canUploadAndManageReviewImages())
		{
			$rater->setAttachmentHash($this->filter('attachment_hash', 'str'));
		}
		
		$customFields = $this->filter('custom_fields', 'array');
		$rater->setCustomFields($customFields);

		if ($article->canRatePreReg())
		{
			$rater->setIsPreRegAction(true);
		}
		
		return $rater;
	}

	public function actionRate(ParameterBag $params)
	{
		$visitorUserId = \XF::visitor()->user_id;

		$article = $this->assertViewableArticle($params->article_id);
		
		$isPreRegRate = $article->canRatePreReg();
		
		if (!$article->canRate($error) && !$isPreRegRate)
		{
			return $this->noPermission($error);
		}
		
		$category = $article->Category;
		
		if ($this->isPost())
		{
			$rater = $this->setupArticleRate($article);
			$rater->checkForSpam();
			
			if (!$rater->validate($errors))
			{
				return $this->error($errors);
			}
			
			if ($isPreRegRate)
			{
				/** @var \XF\ControllerPlugin\PreRegAction $preRegPlugin */
				$preRegPlugin = $this->plugin('XF:PreRegAction');
				return $preRegPlugin->actionPreRegAction(
					'XenAddons\AMS:ArticleItem\Rate',
					$article,
					$this->getPreRegRateActionData($rater)
				);
			}
			
			$rating = $rater->save();
			
			if ($rating->is_review)
			{
				$visitor = \XF::visitor();
				
				/** @var \XenAddons\AMS\Repository\ArticleWatch $watchRepo */
				$watchRepo = $this->repository('XenAddons\AMS:ArticleWatch');
				$watchRepo->autoWatchAmsArticleItem($article, $visitor);
			}
			else 
			{
				// If this is a Rating Only (not a review), we always want to force a Rating Only to a visible state
				if ($rating->rating_state == 'moderated')
				{
					$rating->rating_state = 'visible';
					$rating->save();
				}	
			}
			
			return $this->redirect($this->buildLink(
				$rating->is_review ? 'ams/reviews' : 'ams',
				$article
			));
		}
		else
		{
			$review = $article->getNewRating();
			
			if ($category && $category->canUploadAndManageReviewImages())
			{
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = $this->repository('XF:Attachment');
				$attachmentData = $attachmentRepo->getEditorData('ams_rating', $category);
			}
			else
			{
				$attachmentData = null;
			}
			
			$viewParams = [
				'review' => $review,
				'article' => $article,
				'category' => $article->Category,
				'attachmentData' => $attachmentData,
			];
			return $this->view('XF:ArticleItem\Rate', 'xa_ams_article_rate', $viewParams);
		}
	}
	
	protected function getPreRegRateActionData(\XenAddons\AMS\Service\ArticleItem\Rate $rater)
	{
		$rating = $rater->getRating();
	
		return [
			'rating' => $rating->rating,
			'message' => $rating->message,
			'pros' => $rating->pros,
			'cons' => $rating->cons,
			'custom_fields' => $rating->custom_fields->getFieldValues(),
			'is_anonymous' => $rating->is_anonymous
		];
	}
	
	public function actionReviewPreview(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		$article = $this->assertViewableArticle($params->article_id);
	
		$articleRater = $this->setupArticleRate($article);
		if (!$articleRater->validate($errors))
		{
			return $this->error($errors);
		}
	
		$review = $articleRater->getRating();
	
		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');
	
		if ($article->Category && $article->Category->canUploadAndManageReviewImages())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_rating', $review, $tempHash);
			$attachments = $attachmentData['attachments'];
		}
	
		return $this->plugin('XF:BbCodePreview')->actionPreview(
			$review->message, 'ams_rating', $article->User, $attachments, $article->canViewReviewImages()
		);
	}	
	
	public function actionLeaveContributorsTeam(ParameterBag $params): AbstractReply
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canLeaveContributorsTeam($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\ContributorsManager $contributorsManager */
			$contributorsManager = $this->service(
				'XenAddons\AMS:ArticleItem\ContributorsManager',
				$article
			);
			$contributorsManager->leaveContributorsTeam(\XF::visitor());
	
			if (!$contributorsManager->validate($errors))
			{
				return $this->error($errors);
			}
	
			$contributorsManager->save();
	
			return $this->redirect($this->buildLink('ams', $article));
		}
	
		$category = $article->Category;
	
		$viewParams = [
			'article' => $article,
			'category' => $category,
		];
		return $this->view(
			'XenAddons\AMS:ArticleItem\LeaveContributorsTeam',
			'xa_ams_article_leave_contributors_team',
			$viewParams
		);
	}
	
	public function actionManageContributors(ParameterBag $params): AbstractReply
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canManageContributors($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$input = $this->filter([
				'add_co_authors' => 'str',
				'add_contributors' => 'str',
				'remove_contributors' => 'array-uint'
			]);
	
			/** @var \XenAddons\AMS\Service\ArticleItem\ContributorsManager $contributorsManager */
			$contributorsManager = $this->service(
				'XenAddons\AMS:ArticleItem\ContributorsManager',
				$article
			);

			if ($article->canAddCoAuthors())
			{
				$contributorsManager->addCoAuthors($input['add_co_authors']);
			}
			
			if ($article->canAddContributors())
			{
				$contributorsManager->addContributors($input['add_contributors']);
			}
	
			if ($article->canRemoveContributors())
			{
				$contributorsManager->removeContributors($input['remove_contributors']);
			}
	
			if (!$contributorsManager->validate($errors))
			{
				return $this->error($errors);
			}
	
			$contributorsManager->save();
	
			return $this->redirect($this->buildLink('ams', $article));
		}
	
		$category = $article->Category;
		$contributors = $article->Contributors;
		$maxContributors = $article->getMaxContributorCount();
	
		$viewParams = [
			'article' => $article,
			'category' => $category,
			'contributors' => $contributors,
			'maxContributors' => $maxContributors,
		];
		return $this->view(
			'XenAddons\AMS:ArticleItem\ManageContributors',
			'xa_ams_article_manage_contributors',
			$viewParams
		);
	}	

	/**
	 * @param \XenAddons\AMS\Entity\ArticleItem $article
	 *
	 * @return \XenAddons\AMS\Service\ArticleItem\Edit
	 */
	protected function setupArticleEdit(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		/** @var \XenAddons\AMS\Service\ArticleItem\Edit $editor */
		$editor = $this->service('XenAddons\AMS:ArticleItem\Edit', $article);

		$prefixId = $this->filter('prefix_id', 'uint');
		if ($prefixId != $article->prefix_id && !$article->Category->isPrefixUsable($prefixId))
		{
			$prefixId = 0; // not usable, just blank it out
		}
		$editor->setPrefix($prefixId);

		$editor->setTitle($this->filter('title', 'str'));
		
		$editor->setMessage($this->plugin('XF:Editor')->fromInput('message'));
		
		$customFields = $this->filter('custom_fields', 'array');
		$editor->setCustomFields($customFields);
		
		if ($article->Category->allow_author_rating && $article->canSetAuthorRating())
		{
			$editor->setAuthorRating($this->filter('author_rating', 'float'));
		}
		
		$originalSourceInput = $this->filter([
			'os_article_author' => 'str',
			'os_article_title' => 'str',
			'os_article_date' => 'datetime',
			'os_source_name' => 'str',
			'os_source_url' => 'str',
		]);
		
		$basicFields = $this->filter([
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
		$basicFields['original_source'] = $originalSourceInput;
		$article->edit_date = time();
		$article->bulkSet($basicFields);
		
		if ($article->Category->canUploadAndManageArticleAttachments())
		{
			$editor->setAttachmentHash($this->filter('attachment_hash', 'str'));
		}

		if ($this->filter('post_as_update', 'bool'))
		{
			$editor->setPostThreadUpdate(true, $this->filter('update_message', 'str'));
		}
		
		if ($this->filter('author_alert', 'bool') && $article->canSendModeratorActionAlert())
		{
			$editor->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
		}

		return $editor;
	}

	public function actionEdit(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canEdit($error))
		{
			return $this->noPermission($error);
		}

		$category = $article->Category;

		if ($this->isPost())
		{
			$editor = $this->setupArticleEdit($article);
			$editor->checkForSpam();

			if (!$editor->validate($errors))
			{
				return $this->error($errors);
			}

			$editor->save();
			
			if ($this->filter('post_as_update', 'bool'))
			{
				$editor->sendNotifications();
			}

			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			if ($category && $category->canUploadAndManageArticleAttachments())
			{
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = $this->repository('XF:Attachment');
				$attachmentData = $attachmentRepo->getEditorData('ams_article', $article);
			}
			else
			{
				$attachmentData = null;
			}

			$viewParams = [
				'article' => $article,
				'category' => $category,
				'attachmentData' => $attachmentData,
				'prefixes' => $category->getUsablePrefixes($article->prefix_id)
			];
			return $this->view('XF:ArticleItem\Edit', 'xa_ams_article_edit', $viewParams);
		}
	}

	public function actionPreview(ParameterBag $params)
	{
		$this->assertPostOnly();

		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canEdit($error))
		{
			return $this->noPermission($error);
		}

		$editor = $this->setupArticleEdit($article);

		if (!$editor->validate($errors))
		{
			return $this->error($errors);
		}

		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');

		$category = $article->Category;
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

	/**
	 * @param \XenAddons\AMS\Entity\ArticleItem $article
	 * @param \XenAddons\AMS\Entity\Category $category
	 *
	 * @return \XenAddons\AMS\Service\ArticleItem\Move
	 */
	protected function setupArticleMove(\XenAddons\AMS\Entity\ArticleItem $article, \XenAddons\AMS\Entity\Category $category)
	{
		$options = $this->filter([
			'notify_watchers' => 'bool',
			'author_alert' => 'bool',
			'author_alert_reason' => 'str',
			'prefix_id' => 'uint'
		]);

		/** @var \XenAddons\AMS\Service\ArticleItem\Move $mover */
		$mover = $this->service('XenAddons\AMS:ArticleItem\Move', $article);

		if ($options['author_alert'])
		{
			$mover->setSendAlert(true, $options['author_alert_reason']);
		}

		if ($options['notify_watchers'])
		{
			$mover->setNotifyWatchers();
		}

		if ($options['prefix_id'] !== null)
		{
			$mover->setPrefix($options['prefix_id']);
		}

		$mover->addExtraSetup(function($article, $category)
		{
			$article->title = $this->filter('title', 'str');
		});

		return $mover;
	}

	public function actionMove(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canMove($error))
		{
			return $this->noPermission($error);
		}

		$category = $article->Category;

		if ($this->isPost())
		{
			$targetCategoryId = $this->filter('target_category_id', 'uint');

			/** @var \XenAddons\AMS\Entity\Category $targetCategory */
			$targetCategory = $this->app()->em()->find('XenAddons\AMS:Category', $targetCategoryId);
			if (!$targetCategory || !$targetCategory->canView())
			{
				return $this->error(\XF::phrase('requested_category_not_found'));
			}

			$this->setupArticleMove($article, $targetCategory)->move($targetCategory);

			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$categoryRepo = $this->getCategoryRepo();
			$categories = $categoryRepo->getViewableCategories();

			$viewParams = [
				'article' => $article,
				'category' => $category,
				'prefixes' => $category->getUsablePrefixes(),
				'categoryTree' => $categoryRepo->createCategoryTree($categories)
			];
			return $this->view('XenAddons\AMS:ArticleItem\Move', 'xa_ams_article_move', $viewParams);
		}
	}

	public function actionTags(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canEditTags($error))
		{
			return $this->noPermission($error);
		}

		/** @var \XF\Service\Tag\Changer $tagger */
		$tagger = $this->service('XF:Tag\Changer', 'ams_article', $article);

		if ($this->isPost())
		{
			$tagger->setEditableTags($this->filter('tags', 'str'));
			if ($tagger->hasErrors())
			{
				return $this->error($tagger->getErrors());
			}

			$tagger->save();

			if ($this->filter('_xfInlineEdit', 'bool'))
			{
				$viewParams = [
					'article' => $article
				];
				$reply = $this->view('XenAddons\AMS:ArticleItem\TagsInline', 'xa_ams_article_tags_list', $viewParams);
				$reply->setJsonParam('message', \XF::phrase('your_changes_have_been_saved'));
				return $reply;
			}
			else
			{
				return $this->redirect($this->buildLink('ams', $article));
			}
		}
		else
		{
			$grouped = $tagger->getExistingTagsByEditability();

			$viewParams = [
				'article' => $article,
				'category' => $article->Category,
				'editableTags' => $grouped['editable'],
				'uneditableTags' => $grouped['uneditable']
			];
			return $this->view('XenAddons\AMS:ArticleItem\Tags', 'xa_ams_article_tags', $viewParams);
		}
	}

	public function actionWatch(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canWatch($error))
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
				$config = [
					'email_subscribe' => $this->filter('email_subscribe', 'bool')
				];
			}

			/** @var \XenAddons\AMS\Repository\ArticleWatch $watchRepo */
			$watchRepo = $this->repository('XenAddons\AMS:ArticleWatch');
			$watchRepo->setWatchState($article, $visitor, $action, $config);
			
			// Check to see if the blog entry has an associated discussion thread and auto watch it
			if ($article->Discussion)
			{
				if ($action == 'watch')
				{
					// This needs to be based on the viewing users auto watch preferences as we don't want to force things they do not want!
					$this->repository('XF:ThreadWatch')->autoWatchThread($article->Discussion, $visitor, false); // set to false for 'interaction_watch_state' vs 'creation_watch_state'
				}
				elseif ($action == 'delete')
				{
					$this->repository('XF:ThreadWatch')->setWatchState($article->Discussion, $visitor, $action);
				}
			}

			$redirect = $this->redirect($this->buildLink('ams', $article));
			$redirect->setJsonParam('switchKey', $action == 'delete' ? 'watch' : 'unwatch');
			return $redirect;
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'isWatched' => !empty($article->Watch[$visitor->user_id]),
				'category' => $article->Category
			];
			return $this->view('XenAddons\AMS:ArticleItem\Watch', 'xa_ams_article_watch', $viewParams);
		}
	}

	public function actionReassign(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canReassign($error))
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

			$canTargetView = \XF::asVisitor($user, function() use ($article)
			{
				return $article->canView();
			});
			if (!$canTargetView)
			{
				return $this->error(\XF::phrase('xa_ams_new_owner_must_be_able_to_view_this_article'));
			}

			/** @var \XenAddons\AMS\Service\ArticleItem\Reassign $reassigner */
			$reassigner = $this->service('XenAddons\AMS:ArticleItem\Reassign', $article);

			if ($this->filter('alert', 'bool'))
			{
				$reassigner->setSendAlert(true, $this->filter('alert_reason', 'str'));
			}

			$reassigner->reassignTo($user);

			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\Reassign', 'xa_ams_article_reassign', $viewParams);
		}
	}

	public function actionQuickFeature(ParameterBag $params)
	{
		$this->assertPostOnly();

		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canFeatureUnfeature($error))
		{
			return $this->error($error);
		}

		/** @var \XenAddons\AMS\Service\ArticleItem\Feature $featurer */
		$featurer = $this->service('XenAddons\AMS:ArticleItem\Feature', $article);

		if ($article->Featured)
		{
			$featurer->unfeature();
			$featured = false;
			$text = \XF::phrase('xa_ams_article_quick_feature');
		}
		else
		{
			$featurer->feature();
			$featured = true;
			$text = \XF::phrase('xa_ams_article_quick_unfeature');
		}

		$reply = $this->redirect($this->getDynamicRedirect());
		$reply->setJsonParams([
			'text' => $text,
			'featured' => $featured
		]);
		return $reply;
	}
	
	public function actionBookmark(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
	
		/** @var \XF\ControllerPlugin\Bookmark $bookmarkPlugin */
		$bookmarkPlugin = $this->plugin('XF:Bookmark');
	
		return $bookmarkPlugin->actionBookmark(
			$article, $this->buildLink('ams/bookmark', $article)
		);
	}
	
	public function actionChangeDates(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canChangeDates($error))
		{
			return $this->noPermission($error);
		}	

		$category = $article->Category;
		
		if ($this->isPost())
		{
			// TODO probably move this process into a service in a future version!
			
			$publishDateInput = $this->filter([
				'article_publish_date' => 'str',
				'article_publish_hour' => 'int',
				'article_publish_minute' => 'int',
				'article_timezone' => 'str'
			]);
			
			$lastUpdateDateInput = $this->filter([
				'article_last_update_date' => 'str',
				'article_last_update_hour' => 'int',
				'article_last_update_minute' => 'int'
			]);
			
			$tz = new \DateTimeZone($publishDateInput['article_timezone']);
				
			$publishDate = $publishDateInput['article_publish_date'];
			$publishHour = $publishDateInput['article_publish_hour'];
			$publishMinute = $publishDateInput['article_publish_minute'];
			$publishDate = new \DateTime("$publishDate $publishHour:$publishMinute", $tz);
			$publishDate = $publishDate->format('U');
			
			$lastUpdateDate = $lastUpdateDateInput['article_last_update_date'];
			$lastUpdateHour = $lastUpdateDateInput['article_last_update_hour'];
			$lastUpdateMinute = $lastUpdateDateInput['article_last_update_minute'];
			$lastUpdateDate = new \DateTime("$lastUpdateDate $lastUpdateHour:$lastUpdateMinute", $tz);
			$lastUpdateDate = $lastUpdateDate->format('U');
			
			// check if either dates are attempting to be set to the future
			if ($publishDate > \XF::$time || $lastUpdateDate > \XF::$time)
			{
				// Throw error as can not change publish date or last update date into the future, only the past!
				return $this->error(\XF::phraseDeferred('xa_ams_can_not_change_date_into_the_future'));
			}
			
			$article->publish_date = $publishDate;
			$article->publish_date_timezone = $publishDateInput['article_timezone'];
			
			// if the last update date is older then the publish date, then use the publish date to set the last update date!
			if ($lastUpdateDate < $publishDate)
			{
				$article->last_update = $publishDate;			
			}
			else 
			{
				$article->last_update = $lastUpdateDate;				
			}
			
			$article->save();
			
			return $this->redirect($this->buildLink('ams', $article));
		}
		else 
		{
			$hours = [];
			for ($i = 0; $i < 24; $i++)
			{
				$hh = str_pad($i, 2, '0', STR_PAD_LEFT);
				$hours[$hh] = $hh;
			}
				
			$minutes = [];
			for ($i = 0; $i < 60; $i += 5)
			{
				$mm = str_pad($i, 2, '0', STR_PAD_LEFT);
				$minutes[$mm] = $mm;
			}
				
			/** @var \XF\Data\TimeZone $tzData */
			$tzData = $this->app->data('XF:TimeZone');
			$timeZones = $tzData->getTimeZoneOptions();
				
			$articlePublishDate = new \DateTime('@' . $article->publish_date);
			$articlePublishDate->setTimezone(new \DateTimeZone($article->publish_date_timezone));
			$articlePublishHour = $articlePublishDate->format('H');
			$articlePublishMinute = $articlePublishDate->format('i');
				
			$articleLastUpdateDate = new \DateTime('@' . $article->last_update);
			$articleLastUpdateDate->setTimezone(new \DateTimeZone($article->publish_date_timezone));
			$articleLastUpdateHour = $articleLastUpdateDate->format('H');
			$articleLastUpdateMinute = $articleLastUpdateDate->format('i');
			
			$viewParams = [
				'article' => $article,
				'category' => $category,
				
				'articlePublishDate' => $articlePublishDate,
				'articlePublishHour' => $articlePublishHour,
				'articlePublishMinute' => $articlePublishMinute,
				
				'articleLastUpdateDate' => $articleLastUpdateDate,
				'articleLastUpdateHour' => $articleLastUpdateHour,
				'articleLastUpdateMinute' => $articleLastUpdateMinute,
				
				'hours' => $hours,
				'minutes' => $minutes,
				'timeZones' => $timeZones,
			];
			return $this->view('XenAddons\AMS:ArticleItem\ChangeDates', 'xa_ams_article_change_dates', $viewParams);			
		}
	}
	
	public function actionLockUnlockComments(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canLockUnlockComments($error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			if ($article->comments_open)
			{
				$article->comments_open = 0;
				$article->save();
			}
			else
			{
				$article->comments_open = 1;
				$article->save();
			}
		
			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\LockUnlockComments', 'xa_ams_article_lock_unlock_comments', $viewParams);
		}
	}

	public function actionLockUnlockRatings(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canLockUnlockRatings($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			if ($article->ratings_open)
			{
				$article->ratings_open = 0;
				$article->save();
			}
			else
			{
				$article->ratings_open = 1;
				$article->save();
			}
	
			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\LockUnlockRatings', 'xa_ams_article_lock_unlock_ratings', $viewParams);
		}
	}

	public function actionSetCoverImage(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canSetCoverImage($error))
		{
			return $this->noPermission($error);
		}
		
		if ($this->isPost())
		{
			$article->cover_image_id = $this->filter('attachment_id', 'int');
			$article->cover_image_above_article = $this->filter('cover_image_above_article', 'int');
			$article->save();
			
			return $this->redirect($this->buildLink('ams', $article));			
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\SetCoverImage', 'xa_ams_article_set_cover_image', $viewParams);
		}		
	}
		
	public function actionDelete(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canDelete('soft', $error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			$type = $this->filter('hard_delete', 'bool') ? 'hard' : 'soft';
			$reason = $this->filter('reason', 'str');

			if (!$article->canDelete($type, $error))
			{
				return $this->noPermission($error);
			}

			/** @var \XenAddons\AMS\Service\ArticleItem\Delete $deleter */
			$deleter = $this->service('XenAddons\AMS:ArticleItem\Delete', $article);

			if ($this->filter('author_alert', 'bool'))
			{
				$deleter->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
			}

			$deleter->delete($type, $reason);

			$this->plugin('XF:InlineMod')->clearIdFromCookie('ams_article', $article->article_id);

			return $this->redirect($this->buildLink('ams/categories', $article->Category));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\Delete', 'xa_ams_article_delete', $viewParams);
		}
	}

	public function actionUndelete(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canUndelete($error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			if ($article->article_state == 'deleted')
			{
				$article->article_state = 'visible';
				$article->save();
			}

			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\Undelete', 'xa_ams_article_undelete', $viewParams);
		}
	}

	public function actionApprove(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canApproveUnapprove($error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\Approve $approver */
			$approver = \XF::service('XenAddons\AMS:ArticleItem\Approve', $article);
			$approver->setNotifyRunTime(1); // may be a lot happening
			$approver->approve();

			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\Approve', 'xa_ams_article_approve', $viewParams);
		}
	}
	
	public function actionPublishDraft(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		
		if (!$article->canPublishDraft($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\PublishDraft $draftPublisher */
			$draftPublisher = \XF::service('XenAddons\AMS:ArticleItem\PublishDraft', $article);
			$draftPublisher->setNotifyRunTime(1); // may be a lot happening
			$draftPublisher->publishDraft();
	
			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\PublishDraft', 'xa_ams_article_publish_draft', $viewParams);
		}
	}	
	
	public function actionPublishDraftScheduled(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		
		if (!$article->canPublishDraftScheduled($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$publishDateInput = $this->filter([
				'article_publish_date' => 'str',
				'article_publish_hour' => 'int',
				'article_publish_minute' => 'int',
				'article_timezone' => 'str'
			]);
				
			$tz = new \DateTimeZone($publishDateInput['article_timezone']);
	
			$publishDate = $publishDateInput['article_publish_date'];
			$publishHour = $publishDateInput['article_publish_hour'];
			$publishMinute = $publishDateInput['article_publish_minute'];
			$publishDate = new \DateTime("$publishDate $publishHour:$publishMinute", $tz);
			$publishDate = $publishDate->format('U');
				
			if ($publishDate <= \XF::$time)
			{
				// Throw error as can not set publish date into the past/present, only the future!
				return $this->error(\XF::phraseDeferred('xa_ams_scheduled_publish_date_must_be_set_into_the_future'));
			}

			$article->article_state = 'awaiting';
			$article->publish_date = $publishDate;
			$article->publish_date_timezone = $publishDateInput['article_timezone'];
			$article->edit_date = \XF::$time;
			$article->last_update = \XF::$time;
			
			$article->save();
				
			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$hours = [];
			for ($i = 0; $i < 24; $i++)
			{
				$hh = str_pad($i, 2, '0', STR_PAD_LEFT);
				$hours[$hh] = $hh;
			}
			
			$minutes = [];
			for ($i = 0; $i < 60; $i += 5)
			{
				$mm = str_pad($i, 2, '0', STR_PAD_LEFT);
				$minutes[$mm] = $mm;
			}
			
			/** @var \XF\Data\TimeZone $tzData */
			$tzData = $this->app->data('XF:TimeZone');
			$timeZones = $tzData->getTimeZoneOptions();
			
			$viewParams = [
				'article' => $article,
				'category' => $article->Category,
				
				'hours' => $hours,
				'minutes' => $minutes,
				'timeZones' => $timeZones,
			];
			return $this->view('XF:ArticleItem\PublishDraftScheduled', 'xa_ams_article_publish_draft_scheduled', $viewParams);
		}
	}
	
	public function actionChangeScheduledPublishDate(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		
		if (!$article->canChangeScheduledPublishDate($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$publishDateInput = $this->filter([
				'article_publish_date' => 'str',
				'article_publish_hour' => 'int',
				'article_publish_minute' => 'int',
				'article_timezone' => 'str'
			]);
				
			$tz = new \DateTimeZone($publishDateInput['article_timezone']);
	
			$publishDate = $publishDateInput['article_publish_date'];
			$publishHour = $publishDateInput['article_publish_hour'];
			$publishMinute = $publishDateInput['article_publish_minute'];
			$publishDate = new \DateTime("$publishDate $publishHour:$publishMinute", $tz);
			$publishDate = $publishDate->format('U');
				
			if ($publishDate <= \XF::$time)
			{
				// Throw error as can not set publish date into the past/present, only the future!
				return $this->error(\XF::phraseDeferred('xa_ams_scheduled_publish_date_must_be_set_into_the_future'));
			}
				
			$article->publish_date = $publishDate;
			$article->publish_date_timezone = $publishDateInput['article_timezone'];
			$article->edit_date = \XF::$time;
			$article->last_update = \XF::$time;
				
			$article->save();
				
			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$hours = [];
			for ($i = 0; $i < 24; $i++)
			{
				$hh = str_pad($i, 2, '0', STR_PAD_LEFT);
				$hours[$hh] = $hh;
			}
	
			$minutes = [];
			for ($i = 0; $i < 60; $i += 5)
			{
				$mm = str_pad($i, 2, '0', STR_PAD_LEFT);
				$minutes[$mm] = $mm;
			}
	
			/** @var \XF\Data\TimeZone $tzData */
			$tzData = $this->app->data('XF:TimeZone');
			$timeZones = $tzData->getTimeZoneOptions();
	
			$articlePublishDate = new \DateTime('@' . $article->publish_date);
			$articlePublishDate->setTimezone(new \DateTimeZone($article->publish_date_timezone));
			$articlePublishHour = $articlePublishDate->format('H');
			$articlePublishMinute = $articlePublishDate->format('i');
								
			$viewParams = [
				'article' => $article,
				'category' => $article->Category,
	
				'articlePublishDate' => $articlePublishDate,
				'articlePublishHour' => $articlePublishHour,
				'articlePublishMinute' => $articlePublishMinute,
	
				'hours' => $hours,
				'minutes' => $minutes,
				'timeZones' => $timeZones,
			];
			return $this->view('XenAddons\AMS:ArticleItem\ChangeScheduledPublishDate', 'xa_ams_article_change_scheduled_publish_date', $viewParams);
		}
	}	
	
	public function actionIp(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		$breadcrumbs = $article->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Ip $ipPlugin */
		$ipPlugin = $this->plugin('XF:Ip');
		return $ipPlugin->actionIp($article, $breadcrumbs);
	}
	
	public function actionReport(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canReport($error))
		{
			return $this->noPermission($error);
		}
	
		/** @var \XF\ControllerPlugin\Report $reportPlugin */
		$reportPlugin = $this->plugin('XF:Report');
		return $reportPlugin->actionReport(
			'ams_article', $article,
			$this->buildLink('ams/report', $article),
			$this->buildLink('ams', $article)
		);
	}

	public function actionWarn(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
	
		if (!$article->canWarn($error))
		{
			return $this->noPermission($error);
		}
	
		$breadcrumbs = $article->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Warn $warnPlugin */
		$warnPlugin = $this->plugin('XF:Warn');
		return $warnPlugin->actionWarn(
			'ams_article', $article,
			$this->buildLink('ams/warn', $article),
			$breadcrumbs
		);
	}	
	
	/**
	 * @param \XenAddons\AMS\Entity\ArticleItem $article
	 *
	 * @return \XenAddons\AMS\Service\ArticleItem\ReplyBan|null
	 */
	protected function setupArticleReplyBan(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		$input = $this->filter([
			'username' => 'str',
			'ban_length' => 'str',
			'ban_length_value' => 'uint',
			'ban_length_unit' => 'str',

			'send_alert' => 'bool',
			'reason' => 'str'
		]);
	
		if (!$input['username'])
		{
			return null;
		}
	
		/** @var \XF\Entity\User $user */
		$user = $this->finder('XF:User')->where('username', $input['username'])->fetchOne();
		if (!$user)
		{
			throw $this->exception(
				$this->notFound(\XF::phrase('requested_user_x_not_found', ['name' => $input['username']]))
			);
		}
	
		/** @var \XenAddons\AMS\Service\ArticleItem\ReplyBan $replyBanService */
		$replyBanService = $this->service('XenAddons\AMS:ArticleItem\ReplyBan', $article, $user);
	
		if ($input['ban_length'] == 'temporary')
		{
			$replyBanService->setExpiryDate($input['ban_length_unit'], $input['ban_length_value']);
		}
		else
		{
			$replyBanService->setExpiryDate(0);
		}
	
		$replyBanService->setSendAlert($input['send_alert']);
		$replyBanService->setReason($input['reason']);
	
		return $replyBanService;
	}
	
	public function actionReplyBans(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canReplyBan($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$delete = $this->filter('delete', 'array-bool');
			$delete = array_filter($delete);
	
			$replyBanService = $this->setupArticleReplyBan($article);
			if ($replyBanService)
			{
				if (!$replyBanService->validate($errors))
				{
					return $this->error($errors);
				}
	
				$replyBanService->save();
	
				// don't try to delete the record we just added
				unset($delete[$replyBanService->getUser()->user_id]);
			}
	
			if ($delete)
			{
				$replyBans = $article->ReplyBans;
				foreach (array_keys($delete) AS $userId)
				{
					if (isset($replyBans[$userId]))
					{
						$replyBans[$userId]->delete();
					}
				}
			}
	
			return $this->redirect($this->getDynamicRedirect($this->buildLink('ams', $article), false));
		}
		else
		{
			/** @var \XenAddons\AMS\Repository\ArticleReplyBan $replyBanRepo */
			$replyBanRepo = $this->repository('XenAddons\AMS:ArticleReplyBan');
			$replyBanFinder = $replyBanRepo->findReplyBansForArticle($article)->order('ban_date');
	
			$viewParams = [
				'article' => $article,
				'category' => $article->Category,
				'bans' => $replyBanFinder->fetch()
			];
			return $this->view('XenAddons\AMS:ArticleItem\ReplyBans', 'xa_ams_article_reply_bans', $viewParams);
		}
	}
	
	public function actionChangeThread(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canChangeDiscussionThread($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\ChangeDiscussion $changer */
			$changer = $this->service('XenAddons\AMS:ArticleItem\ChangeDiscussion', $article);
	
			$threadAction = $this->filter('thread_action', 'str');
			if ($threadAction == 'disconnect')
			{
				$changer->disconnectDiscussion();
			}
			else
			{
				$threadUrl = $this->filter('thread_url', 'str');
	
				if (!$changer->changeThreadByUrl($threadUrl, true, $error))
				{
					return $this->error($error);
				}
			}
	
			return $this->redirect($this->buildLink('ams', $article));
		}
		else
		{
			$viewParams = [
				'article' => $article,
				'category' => $article->Category
			];
			return $this->view('XF:ArticleItem\ChangeThread', 'xa_ams_article_change_thread', $viewParams);
		}
	}
	
	public function actionModeratorActions(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		if (!$article->canViewModeratorLogs($error))
		{
			return $this->noPermission($error);
		}
	
		$breadcrumbs = $article->getBreadcrumbs();
		$title = $article->title;
	
		$this->request()->set('page', $params->page);
	
		/** @var \XF\ControllerPlugin\ModeratorLog $modLogPlugin */
		$modLogPlugin = $this->plugin('XF:ModeratorLog');
		return $modLogPlugin->actionModeratorActions(
			$article,
			['ams/moderator-actions', $article],
			$title, $breadcrumbs
		);
	}	
	
	public function actionHistory(ParameterBag $params)
	{
		return $this->rerouteController('XF:EditHistory', 'index', [
			'content_type' => 'ams_article',
			'content_id' => $params->article_id
		]);
	}
	
	public function actionReact(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactSimple($article, 'ams');
	}
	
	public function actionReactions(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
	
		$breadcrumbs = $article->getBreadcrumbs();
		$title = \XF::phrase('xa_ams_members_who_reacted_to_article', ['title' => $article->title]);
	
		$this->request()->set('page', $params->page);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactions(
			$article,
			'ams/reactions',
			$title, $breadcrumbs
		);
	}

	public function actionPrefixes(ParameterBag $params)
	{
		$this->assertPostOnly();

		$categoryId = $this->filter('val', 'uint');

		/** @var \XenAddons\AMS\Entity\Category $category */
		$category = $this->em()->find('XenAddons\AMS:Category', $categoryId,
			'Permissions|' . \XF::visitor()->permission_combination_id
		);
		if (!$category)
		{
			return $this->notFound(\XF::phrase('requested_category_not_found'));
		}

		if (!$category->canView($error))
		{
			return $this->noPermission($error);
		}

		$viewParams = [
			'category' => $category,
			'prefixes' => $category->getUsablePrefixes()
		];
		return $this->view('XenAddons\AMS:Category\Prefixes', 'xa_ams_category_prefixes', $viewParams);
	}
	
	public function actionMarkRead()
	{
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
			$categoryRepo = $this->getCategoryRepo();
			$articleRepo = $this->getArticleRepo();
	
			$categoryList = $categoryRepo->getViewableCategories();
			$categoryIds = $categoryList->keys();
	
			$articleRepo->markArticlesReadByVisitor($categoryIds, $markDate);
			$articleRepo->markAllArticleCommentsReadByVisitor($categoryIds, $markDate);
	
			return $this->redirect(
				$this->buildLink('ams'),
				\XF::phrase('xa_ams_all_articles_marked_as_read')
			);
		}
		else
		{
			$viewParams = [
				'date' => $markDate
			];
			return $this->view('XenAddons\AMS:ArticleItem\MarkRead', 'xa_ams_article_mark_read', $viewParams);
		}	
	}
	
	public function actionDialogYours()
	{
		$categoryRepo = $this->getCategoryRepo();
	
		$categoryList = $categoryRepo->getViewableCategories();
		$categoryIds = $categoryList->keys();
	
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
	
		$articleRepo = $this->getArticleRepo();
		$articlesList = $articleRepo->findArticlesForUser(\XF::visitor(), $categoryIds, ['visibility' => false])
			->where('article_state', 'visible')
			->limitByPage($page, $perPage, 1);
	
		$articles = $articlesList->fetch();
	
		$hasMore = false;
		if ($articles->count() > $perPage)
		{
			$hasMore = true;
			$articles = $articles->slice(0, $perPage);
		}
	
		$viewParams = [
			'articles' => $articles,
			'page' => $page,
			'hasMore' => $hasMore
		];
		return $this->view('XenAddons\AMS:Article\Dialog\Yours', 'xa_ams_dialog_your_articles', $viewParams);
	}
	
	public function actionDialogBrowse()
	{
		$categoryRepo = $this->getCategoryRepo();
	
		$categoryList = $categoryRepo->getViewableCategories();
		$categoryIds = $categoryList->keys();
	
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
	
		$articleRepo = $this->getArticleRepo();
		$articlesList = $articleRepo->findArticlesForArticleList($categoryIds, ['visibility' => false])
			->where('article_state', 'visible')
			->where('user_id', '<>', \XF::visitor()->user_id)
			->limitByPage($page, $perPage, 1);
	
		$articles = $articlesList->fetch();
	
		$hasMore = false;
		if ($articles->count() > $perPage)
		{
			$hasMore = true;
			$articles = $articles->slice(0, $perPage);
		}
	
		$viewParams = [
			'articles' => $articles,
			'page' => $page,
			'hasMore' => $hasMore
		];
		return $this->view('XenAddons\AMS:Article\Dialog\Browse', 'xa_ams_dialog_browse_articles', $viewParams);
	}	
	
	public function actionDialogYourPages()
	{
		$categoryRepo = $this->getCategoryRepo();
	
		$categoryList = $categoryRepo->getViewableCategories();
		$categoryIds = $categoryList->keys();
	
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
	
		$articlePageRepo = $this->getPageRepo();
		$articlePagesList = $articlePageRepo->findArticlePagesForUser(\XF::visitor(), $categoryIds, ['visibility' => false])
			->where('page_state', 'visible')
			->where('Article.article_state', 'visible')
			->limitByPage($page, $perPage, 1);
	
		$articlePages = $articlePagesList->fetch();
	
		$hasMore = false;
		if ($articlePages->count() > $perPage)
		{
			$hasMore = true;
			$articlePages = $articlePages->slice(0, $perPage);
		}
	
		$viewParams = [
			'articlePages' => $articlePages,
			'page' => $page,
			'hasMore' => $hasMore
		];
		return $this->view('XenAddons\AMS:Article\Dialog\YourPages', 'xa_ams_dialog_your_pages', $viewParams);
	}
	
	public function actionDialogBrowsePages()
	{
		$categoryRepo = $this->getCategoryRepo();
	
		$categoryList = $categoryRepo->getViewableCategories();
		$categoryIds = $categoryList->keys();
	
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
	
		$articlePageRepo = $this->getPageRepo();
		$articlePagesList = $articlePageRepo->findArticlePagesForArticlePageList($categoryIds, ['visibility' => false])
			->where('page_state', 'visible')
			->where('Article.article_state', 'visible')
			->where('user_id', '<>', \XF::visitor()->user_id)
			->limitByPage($page, $perPage, 1);
	
		$articlePages = $articlePagesList->fetch();
	
		$hasMore = false;
		if ($articlePages->count() > $perPage)
		{
			$hasMore = true;
			$articlePages = $articlePages->slice(0, $perPage);
		}
	
		$viewParams = [
			'articlePages' => $articlePages,
			'page' => $page,
			'hasMore' => $hasMore
		];
		return $this->view('XenAddons\AMS:Article\Dialog\BrowsePages', 'xa_ams_dialog_browse_pages', $viewParams);
	}
	
	public function actionPollCreate(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
	
		$breadcrumbs = $article->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionCreate('ams_article', $article, $breadcrumbs);
	}	
	
	public function actionPollEdit(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		$poll = $article->Poll;
	
		$breadcrumbs = $article->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionEdit($poll, $breadcrumbs);
	}
	
	public function actionPollDelete(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		$poll = $article->Poll;
	
		$breadcrumbs = $article->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionDelete($poll, $breadcrumbs);
	}
	
	public function actionPollVote(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		$poll = $article->Poll;
	
		$breadcrumbs = $article->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionVote($poll, $breadcrumbs);
	}
	
	public function actionPollResults(ParameterBag $params)
	{
		$article = $this->assertViewableArticle($params->article_id);
		$poll = $article->Poll;
	
		$breadcrumbs = $article->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionResults($poll, $breadcrumbs);
	}	

	protected function getAmsRss()
	{
		$limit = $this->options()->discussionsPerPage;
	
		$articleRepo = $this->getArticleRepo();
		$articleList = $articleRepo->findArticlesForRssFeed()->limit($limit * 3);
	
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
			'category' => null,
			'order' => $order,
			'articles' => $articles
		]);
	}
	
	public function actionArticlesQueue(ParameterBag $params)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		
		if (!$visitor->canViewAmsArticlesQueue($error))
		{
			throw $this->exception($this->noPermission($error));
		}
		
		/** @var \XenAddons\AMS\ControllerPlugin\ArticlesQueue $articlesQueuePlugin */
		$articlesQueuePlugin = $this->plugin('XenAddons\AMS:ArticlesQueue');
	
		$categoryParams = $articlesQueuePlugin->getCategoryListData();
		$viewableCategoryIds = $categoryParams['categories']->keys();
		
		$listParams = $articlesQueuePlugin->getArticlesQueueData($viewableCategoryIds);
	
		$this->assertValidPage($listParams['page'], $listParams['perPage'], $listParams['total'], 'ams/articles-queue');
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/articles-queue', null, $listParams['page']));
		
		$viewParams = $categoryParams + $listParams;
	
		return $this->view('XenAddons\AMS:ArticlesQueue', 'xa_ams_articles_queue', $viewParams);
	}
	
	public function actionArticlesQueueFilters()
	{
		/** @var \XenAddons\AMS\ControllerPlugin\ArticlesQueue $articlesQueuePlugin */
		$articlesQueuePlugin = $this->plugin('XenAddons\AMS:ArticlesQueue');
	
		return $articlesQueuePlugin->actionFilters();
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