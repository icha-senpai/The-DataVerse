<?php

namespace XenAddons\AMS\ControllerPlugin;

use XF\ControllerPlugin\AbstractPlugin;

class ArticlesQueue extends AbstractPlugin
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

	public function getArticlesQueueData(array $sourceCategoryIds)
	{
		$articleRepo = $this->getArticleRepo();

		$articleFinder = $articleRepo->findArticlesForArticlesQueue($sourceCategoryIds);

		$filters = $this->getArticleFilterInput();
		$this->applyArticleFilters($articleFinder, $filters);
		
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
		
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
		
		return [
			'articles' => $articles,
			'filters' => $filters,
			'creatorFilter' => $creatorFilter,

			'total' => $totalArticles,
			'page' => $page,
			'perPage' => $perPage,
		];
	}

	public function applyArticleFilters(\XenAddons\AMS\Finder\ArticleItem $articleFinder, array $filters)
	{
		if (!empty($filters['state']))
		{
			switch ($filters['state'])
			{
				case 'draft':
					$articleFinder->where('article_state', 'draft');
					break;
		
				case 'awaiting':
					$articleFinder->where('article_state', 'awaiting');
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

	public function getArticleFilterInput()
	{
		$filters = [];

		$input = $this->filter([
			'state' => 'str',
			'title' => 'str',	
			'prefix_id' => 'uint',
			'creator' => 'str',
			'creator_id' => 'uint',
			'last_days' => 'int',
			'order' => 'str',
			'direction' => 'str'
		]);
		
		if ($input['state'] && ($input['state'] == 'draft' || $input['state'] == 'awaiting'))
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

			$defaultOrder = 'last_update';
			
			$defaultDir = 'desc';

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
			'title' => 'title'
		];
	}

	public function actionFilters()
	{
		$filters = $this->getArticleFilterInput();
		
		if ($this->filter('apply', 'bool'))
		{
			return $this->redirect($this->buildLink('ams/articles-queue', null, $filters));
		}

		if (!empty($filters['creator_id']))
		{
			$creatorFilter = $this->em()->find('XF:User', $filters['creator_id']);
		}
		else
		{
			$creatorFilter = null;
		}

		$applicableCategories = $this->getCategoryRepo()->getViewableCategories();
		$applicableCategoryIds = $applicableCategories->keys();

		$availablePrefixIds = $this->repository('XenAddons\AMS:CategoryPrefix')->getPrefixIdsInContent($applicableCategoryIds);
		$prefixes = $this->repository('XenAddons\AMS:ArticlePrefix')->findPrefixesForList()
			->where('prefix_id', $availablePrefixIds)
			->fetch();
		
		$showTitleFilters = $this->options()->xaAmsShowTitleFilters;

		$defaultOrder = 'last_update';
		
		$defaultDir = 'desc';

		if (empty($filters['order']))
		{
			$filters['order'] = $defaultOrder;
		}
		if (empty($filters['direction']))
		{
			$filters['direction'] = $defaultDir;
		}

		$viewParams = [
			'prefixesGrouped' => $prefixes->groupBy('prefix_group_id'),
			'filters' => $filters,
			'creatorFilter' => $creatorFilter,
			'showTitleFilters' => $showTitleFilters,
		];
		return $this->view('XenAddons\AMS:ArticlesQueueFilters', 'xa_ams_articles_queue_filters', $viewParams);
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