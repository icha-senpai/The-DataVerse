<?php

namespace XenAddons\AMS\ControllerPlugin;

use XF\ControllerPlugin\AbstractPlugin;

class ArticleList extends AbstractPlugin
{
	public function getCategoryListData(\XenAddons\AMS\Entity\Category $category = null)
	{
		$categoryRepo = $this->getCategoryRepo();
		$categories = $categoryRepo->getViewableCategories();
		$categoryTree = $categoryRepo->createCategoryTree($categories);
		$categoryExtras = $categoryRepo->getCategoryListExtras($categoryTree);

		return [
			'categories' => $categories,
			'categoryTree' => $categoryTree,
			'categoryExtras' => $categoryExtras
		];
	}

	public function getArticleListData(array $sourceCategoryIds, \XenAddons\AMS\Entity\Category $category = null)
	{
		$articleRepo = $this->getArticleRepo();

		$allowOwnPending = is_callable([$this->controller, 'hasContentPendingApproval'])
			? $this->controller->hasContentPendingApproval()
			: true;

		$articleFinder = $articleRepo->findArticlesForArticleList($sourceCategoryIds, [
			'allowOwnPending' => $allowOwnPending
		], $category);

		$filters = $this->getArticleFilterInput($category);
		$this->applyArticleFilters($articleFinder, $filters);
		
		// Featured Articles are not fetched if any filters are applied!
		if (!$filters && $featuredLimit = $this->options()->xaAmsFeaturedArticlesLimit)
		{
			$featuredLimit = $this->options()->xaAmsFeaturedArticlesDisplayType == 'featured_grid' ? 3 : $featuredLimit;
				
			$featuredArticles = $articleRepo->findFeaturedArticles($sourceCategoryIds)
				->fetch($featuredLimit)
				->filterViewable();
			
			if ($featuredArticles && $this->options()->xaAmsExcludeFeaturedArticlesFromListing)
			{
				$excludeArticleIds = $featuredArticles->pluckNamed('article_id');
				$articleFinder->where('article_id', '<>', $excludeArticleIds);
			}
		}
		else
		{
			$featuredArticles = $this->em()->getEmptyCollection();
		}
		$featuredArticlesCount = $featuredArticles->count();

		$article_view_layout = false;
		if ($category && isset($category->layout_type) && $category->layout_type)
		{
			if ($category->layout_type == 'article_view')
			{
				$article_view_layout = true;
			}
		}
		else
		{
			if ($this->options()->xaAmsArticleListLayoutType == 'article_view')
			{
				$article_view_layout = true;
			}
		}
		
		$grid_view_layout = false;
		if ($category && isset($category->layout_type) && $category->layout_type)
		{
			if ($category->layout_type == 'grid_view')
			{
				$grid_view_layout = true;
			}
		}
		else
		{
			if ($this->options()->xaAmsArticleListLayoutType == 'grid_view')
			{
				$grid_view_layout = true;
			}
		}
		
		$tile_view_layout = false;
		if ($category && isset($category->layout_type) && $category->layout_type)
		{
			if ($category->layout_type == 'tile_view')
			{
				$tile_view_layout = true;
			}
		}
		else
		{
			if ($this->options()->xaAmsArticleListLayoutType == 'tile_view')
			{
				$tile_view_layout = true;
			}
		}
		
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
		
		if ($article_view_layout)
		{
			$perPage = $this->options()->xaAmsArticlesPerPageAV;
		}
		elseif ($grid_view_layout)
		{
			$perPage = $this->options()->xaAmsArticlesPerPageGV;
		}
		elseif ($tile_view_layout)
		{
			$perPage = $this->options()->xaAmsArticlesPerPageTV;
		}
		
		$articleFinder->limitByPage($page, $perPage);
		
		$articles = $articleFinder->fetch()->filterViewable();
		$totalArticles = $articleFinder->total();
		


		if (!empty($filters['creator_id']))
		{
			$creatorFilter = $this->em()->find('XF:User', $filters['creator_id']);
		}
		else
		{
			$creatorFilter = null;
		}
		
		foreach ($articles AS $article)
		{
			if (!$article->canViewFullArticle())
			{
				$snippet = $this->app->stringFormatter()->wholeWordTrim($article->message, $this->options()->xaAmsLimitedViewArticleLength);
				if (strlen($snippet) < strlen($article->message))
				{
					$article->message = $this->app->bbCode()->render($snippet, 'bbCodeClean', 'ams_article', null);
				}
			}
		}

		$canInlineMod = false;
		foreach ($articles AS $article)
		{
			/** @var \XenAddons\AMS\Entity\ArticleItem $article */
			if ($article->canUseInlineModeration())
			{
				$canInlineMod = true;
				break;
			}
		}
		
		return [
			'articles' => $articles,
			'filters' => $filters,
			'creatorFilter' => $creatorFilter,
			'canInlineMod' => $canInlineMod,

			'total' => $totalArticles,
			'page' => $page,
			'perPage' => $perPage,

			'featuredArticles' => $featuredArticles,
			'featuredArticlesCount' => $featuredArticlesCount
		];
	}

	public function applyArticleFilters(\XenAddons\AMS\Finder\ArticleItem $articleFinder, array $filters)
	{
		if (!empty($filters['featured']))
		{
			$articleFinder->where('Featured.feature_date', '>', 0);
		}
		
		if (!empty($filters['is_rated']))
		{
			$articleFinder->where('rating_count', '>', 0);
		}
		
		if (!empty($filters['has_reviews']))
		{
			$articleFinder->where('review_count', '>', 0);
		}
		
		if (!empty($filters['has_comments']))
		{
			$articleFinder->where('comment_count', '>', 0);
		}
		
		if (!empty($filters['state']))
		{
			switch ($filters['state'])
			{
				case 'visible':
					$articleFinder->where('article_state', 'visible');
					break;
		
				case 'moderated':
					$articleFinder->where('article_state', 'moderated');
					break;
		
				case 'deleted':
					$articleFinder->where('article_state', 'deleted');
					break;
			}
		}
		
		if (!empty($filters['title']))
		{		
			if ($filters['title'] == -1)
			{
				// fetch all articles with titles that start with numeral
				$articleFinder->whereOr(
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(0, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(1, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(2, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(3, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(4, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(5, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(6, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(7, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(8, '?%')],
					[$articleFinder->columnUtf8('title'), 'LIKE', $articleFinder->escapeLike(9, '?%')]
				);						
			}
			else 
			{
				$articleFinder->where(
					$articleFinder->columnUtf8('title'),
					'LIKE', $articleFinder->escapeLike($filters['title'], '?%'));
			}
		}		
		
		if (!empty($filters['rating_avg']))
		{
			switch ($filters['rating_avg'])
			{
				case '5':
					$articleFinder->where('rating_avg', '>=', 5);
					break;
		
				case '4':
					$articleFinder->where('rating_avg', '>=', 4);
					break;
		
				case '3':
					$articleFinder->where('rating_avg', '>=', 3);
					break;
		
				case '2':
					$articleFinder->where('rating_avg', '>=', 2);
					break;
			}
		}
		
		if (!empty($filters['prefix_id']))
		{
			$articleFinder->where('prefix_id', intval($filters['prefix_id']));
		}

		if (!empty($filters['creator_id']))
		{
			$articleFinder->where('user_id', intval($filters['creator_id']));
		}
		
		if (!empty($filters['last_days']))
		{
			if ($filters['last_days'] > 0)
			{
				$articleFinder->where('last_update', '>=', \XF::$time - ($filters['last_days'] * 86400));
			}
		}

		$sorts = $this->getAvailableArticleSorts();

		if (!empty($filters['order']) && isset($sorts[$filters['order']]))
		{
			$articleFinder->order($sorts[$filters['order']], $filters['direction']);
		}
		// else the default order has already been applied
	}

	public function getArticleFilterInput(\XenAddons\AMS\Entity\Category $category = null)
	{
		$filters = [];

		$input = $this->filter([
			'featured' => 'bool',
			'is_rated' => 'bool',
			'has_reviews' => 'bool',
			'has_comments' => 'bool',
			'state' => 'str',
			'title' => 'str',	
			'rating_avg' => 'int',
			'prefix_id' => 'uint',
			'creator' => 'str',
			'creator_id' => 'uint',
			'last_days' => 'int',
			'order' => 'str',
			'direction' => 'str'
		]);

		if ($input['featured'])
		{
			$filters['featured'] = true;
		}
		
		if ($input['is_rated'])
		{
			$filters['is_rated'] = true;
		}
		
		if ($input['has_reviews'])
		{
			$filters['has_reviews'] = true;
		}
		
		if ($input['has_comments'])
		{
			$filters['has_comments'] = true;
		}
		
		if ($input['state'] && ($input['state'] == 'visible' || $input['state'] == 'moderated' || $input['state'] == 'deleted'))
		{
			$filters['state'] = $input['state'];
		}
		
		if ($input['title'])
		{
			if (in_array($input['title'], $this->getAvailableTitleLimits()))
			{
				$filters['title'] = $input['title'];
			}
		}
		
		if ($input['rating_avg'] && ($input['rating_avg'] == 5 || $input['rating_avg'] == 4 || $input['rating_avg'] == 3 || $input['rating_avg'] == 2))
		{
			$filters['rating_avg'] = $input['rating_avg'];
		}
		
		if ($input['prefix_id'])
		{
			$filters['prefix_id'] = $input['prefix_id'];
		}

		if ($input['creator_id'])
		{
			$filters['creator_id'] = $input['creator_id'];
		}
		else if ($input['creator'])
		{
			$user = $this->em()->findOne('XF:User', ['username' => $input['creator']]);
			if ($user)
			{
				$filters['creator_id'] = $user->user_id;
			}
		}
		
		if ($input['last_days'] > 0) 
		{
			if (in_array($input['last_days'], $this->getAvailableDateLimits()))
			{
				$filters['last_days'] = $input['last_days'];
			}
		}	

		$sorts = $this->getAvailableArticleSorts();

		if ($input['order'] && isset($sorts[$input['order']]))
		{
			if (!in_array($input['direction'], ['asc', 'desc']))
			{
				$input['direction'] = 'desc';
			}

			if ($category && $category->article_list_order)
			{
				$defaultOrder = $category->article_list_order ?: 'publish_date';
			}
			else
			{
				$defaultOrder = $this->options()->xaAmsListDefaultOrder ?: 'publish_date';
			}
			
			$defaultDir = $defaultOrder == 'title' ? 'asc' : 'desc';

			if ($input['order'] != $defaultOrder || $input['direction'] != $defaultDir)
			{
				$filters['order'] = $input['order'];
				$filters['direction'] = $input['direction'];
			}
		}

		return $filters;
	}
	
	protected function getAvailableTitleLimits()
	{
		// TODO maybe expand this to an option so that other languages can set different characters
		
		return [-1, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
	}
	
	protected function getAvailableDateLimits()
	{
		return [-1, 7, 14, 30, 60, 90, 182, 365];
	}

	public function getAvailableArticleSorts()
	{
		return [
			'publish_date' => 'publish_date',
			'last_update' => 'last_update',
			'rating_weighted' => 'rating_weighted',
			'reaction_score' => 'reaction_score',
			'comment_count' => 'comment_count',
			'view_count' => 'view_count',
			'title' => 'title'
		];
	}

	public function actionFilters(\XenAddons\AMS\Entity\Category $category = null)
	{
		$filters = $this->getArticleFilterInput();

		if ($this->filter('apply', 'bool'))
		{
			return $this->redirect($this->buildLink(
				$category ? 'ams/categories' : 'ams',
				$category,
				$filters
			));
		}

		if (!empty($filters['creator_id']))
		{
			$creatorFilter = $this->em()->find('XF:User', $filters['creator_id']);
		}
		else
		{
			$creatorFilter = null;
		}

		$applicableCategories = $this->getCategoryRepo()->getViewableCategories($category);
		$applicableCategoryIds = $applicableCategories->keys();
		if ($category)
		{
			$applicableCategoryIds[] = $category->category_id;
		}

		$availablePrefixIds = $this->repository('XenAddons\AMS:CategoryPrefix')->getPrefixIdsInContent($applicableCategoryIds);
		$prefixes = $this->repository('XenAddons\AMS:ArticlePrefix')->findPrefixesForList()
			->where('prefix_id', $availablePrefixIds)
			->fetch();
		
		$showTitleFilters = $this->options()->xaAmsShowTitleFilters;
		$showRatingFilters = $this->options()->xaAmsShowRatingFilters;

		if ($category && $category->article_list_order)
		{
			$defaultOrder = $category->article_list_order ?: 'publish_date';
		}
		else
		{
			$defaultOrder = $this->options()->xaAmsListDefaultOrder ?: 'publish_date';
		}
		
		$defaultDir = $defaultOrder == 'title' ? 'asc' : 'desc';

		if (empty($filters['order']))
		{
			$filters['order'] = $defaultOrder;
		}
		if (empty($filters['direction']))
		{
			$filters['direction'] = $defaultDir;
		}

		$viewParams = [
			'category' => $category,
			'prefixesGrouped' => $prefixes->groupBy('prefix_group_id'),
			'filters' => $filters,
			'creatorFilter' => $creatorFilter,
			'showTitleFilters' => $showTitleFilters,
			'showRatingFilters' => $showRatingFilters
		];
		return $this->view('XenAddons\AMS:Filters', 'xa_ams_filters', $viewParams);
	}

	public function actionFeatured(\XenAddons\AMS\Entity\Category $category = null)
	{
		$viewableCategoryIds = $this->getCategoryRepo()->getViewableCategoryIds($category);

		$finder = $this->getArticleRepo()->findFeaturedArticles($viewableCategoryIds);
		$finder->order('Featured.feature_date', 'desc');

		$articles = $finder->fetch()->filterViewable();

		$canInlineMod = false;
		foreach ($articles AS $article)
		{
			/** @var \XenAddons\AMS\\Entity\ArticleItem $article */
			if ($article->canUseInlineModeration())
			{
				$canInlineMod = true;
				break;
			}
		}

		$viewParams = [
			'category' => $category,
			'articles' => $articles,
			'canInlineMod' => $canInlineMod
		];
		return $this->view('XenAddons\AMS:Featured', 'xa_ams_featured', $viewParams);
	}

	/**
	 * @return \XenAddons\AMS\Repository\Article
	 */
	protected function getArticleRepo()
	{
		return $this->repository('XenAddons\AMS:Article');
	}

	/**
	 * @return \XenAddons\AMS\Repository\Category
	 */
	protected function getCategoryRepo()
	{
		return $this->repository('XenAddons\AMS:Category');
	}
}