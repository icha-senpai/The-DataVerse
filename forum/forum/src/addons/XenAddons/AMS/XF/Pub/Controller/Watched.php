<?php

namespace XenAddons\AMS\XF\Pub\Controller;

class Watched extends XFCP_Watched
{
	public function actionAmsArticles()  
	{
		$this->setSectionContext('xa_ams');

		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;

		/** @var \XenAddons\AMS\Repository\Article $articleRepo */
		$articleRepo = $this->repository('XenAddons\AMS:Article');
		$finder = $articleRepo->findArticlesForWatchedList();

		$total = $finder->total();
		$articles = $finder->limitByPage($page, $perPage)->fetch();

		$viewParams = [
			'page' => $page,
			'perPage' => $perPage,
			'total' => $total,
			'articles' => $articles->filterViewable()
		];
		return $this->view('XenAddons\AMS:Watched\Articles', 'xa_ams_watched_articles', $viewParams);
	}

	public function actionAmsArticlesManage()
	{
		$this->setSectionContext('xa_ams');

		if (!$state = $this->filter('state', 'str'))
		{
			return $this->redirect($this->buildLink('watched/ams-articles')); 
		}

		if ($this->isPost())
		{
			/** @var \XenAddons\AMS\Repository\ArticleWatch $articleWatchRepo */
			$articleWatchRepo = $this->repository('XenAddons\AMS:ArticleWatch');

			if ($action = $this->getAmsArticleWatchActionConfig($state, $articles))
			{
				$articleWatchRepo->setWatchStateForAll(\XF::visitor(), $action, $articles);
			}

			return $this->redirect($this->buildLink('watched/ams-articles')); 
		}
		else
		{
			$viewParams = [
				'state' => $state
			];
			return $this->view('XenAddons\AMS:Watched\ArticlesManage', 'watched_ams_articles_manage', $viewParams);  
		}
	}

	public function actionAmsArticlesUpdate()
	{
		$this->assertPostOnly();
		$this->setSectionContext('xa_ams');

		/** @var \XenAddons\AMS\Repository\ArticleWatch $watchRepo */
		$watchRepo = $this->repository('XenAddons\AMS:ArticleWatch');

		$inputAction = $this->filter('watch_action', 'str');
		$action = $this->getAmsArticleWatchActionConfig($inputAction, $config);

		if ($action)
		{
			$ids = $this->filter('ids', 'array-uint');
			$articles = $this->em()->findByIds('XenAddons\AMS:ArticleItem', $ids);
			$visitor = \XF::visitor();

			/** @var \XenAddons\AMS\Entity\ArticleItem $article */
			foreach ($articles AS $article)
			{
				$watchRepo->setWatchState($article, $visitor, $action, $config);
			}
		}

		return $this->redirect(
			$this->getDynamicRedirect($this->buildLink('watched/ams-articles'))
		);
	}

	protected function getAmsArticleWatchActionConfig($inputAction, array &$config = null)
	{
		$config = [];

		switch ($inputAction)
		{
			case 'email_subscribe:on':
				$config = ['email_subscribe' => 1];
				return 'update';

			case 'email_subscribe:off':
				$config = ['email_subscribe' => 0];
				return 'update';

			case 'delete':
				return 'delete';

			default:
				return null;
		}
	}

	public function actionAmsCategories()
	{
		$this->setSectionContext('xa_ams');

		$watchedFinder = $this->finder('XenAddons\AMS:CategoryWatch');
		$watchedCategories = $watchedFinder->where('user_id', \XF::visitor()->user_id)
			->keyedBy('category_id')
			->fetch();

		/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
		$categoryRepo = $this->repository('XenAddons\AMS:Category');
		$categories = $categoryRepo->getViewableCategories();
		$categoryTree = $categoryRepo->createCategoryTree($categories);
		$categoryExtras = $categoryRepo->getCategoryListExtras($categoryTree);

		$viewParams = [
			'watchedCategories' => $watchedCategories,

			'categoryTree' => $categoryTree,
			'categoryExtras' => $categoryExtras
		];
		return $this->view('XenAddons\AMS:Watched\Categories', 'xa_ams_watched_categories', $viewParams);
	}

	public function actionAmsCategoriesUpdate()
	{
		$this->assertPostOnly();
		$this->setSectionContext('xa_ams');

		/** @var \XenAddons\AMS\Repository\CategoryWatch $watchRepo */
		$watchRepo = $this->repository('XenAddons\AMS:CategoryWatch');

		$inputAction = $this->filter('watch_action', 'str');
		$action = $this->getAmsCategoryWatchActionConfig($inputAction, $config);

		if ($action)
		{
			$visitor = \XF::visitor();

			$ids = $this->filter('ids', 'array-uint');
			$categories = $this->em()->findByIds('XenAddons\AMS:Category', $ids);

			/** @var \XenAddons\AMS\Entity\Category $category */
			foreach ($categories AS $category)
			{
				$watchRepo->setWatchState($category, $visitor, $action, $config);
			}
		}

		return $this->redirect(
			$this->getDynamicRedirect($this->buildLink('watched/ams-article-categories'))
		);
	}

	protected function getAmsCategoryWatchActionConfig($inputAction, array &$config = null)
	{
		$config = [];

		$parts = explode(':', $inputAction, 2);

		$inputAction = $parts[0];
		$boolSwitch = isset($parts[1]) ? ($parts[1] == 'on') : false;

		switch ($inputAction)
		{
			case 'send_email':
			case 'send_alert':
			case 'include_children':
				$config = [$inputAction => $boolSwitch];
				return 'update';

			case 'delete':
				return 'delete';

			default:
				return null;
		}
	}
	
	
	// Series Watch
	
	public function actionAmsSeries()
	{
		$this->setSectionContext('xa_ams');

		$watchedFinder = $this->finder('XenAddons\AMS:SeriesWatch');
		$watchedSeries = $watchedFinder->where('user_id', \XF::visitor()->user_id)
			->keyedBy('series_id')
			->fetch();
		
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsSeriesPerPage;
		
		/** @var \XenAddons\AMS\Repository\Series $seriesRepo */
		$seriesRepo = $this->repository('XenAddons\AMS:Series');
		$finder = $seriesRepo->findSeriesForWatchedList();
		
		$total = $finder->total();
		$series = $finder->limitByPage($page, $perPage)->fetch();
		
		$viewParams = [
			'watchedSeries' => $watchedSeries,
		
			'page' => $page,
			'perPage' => $perPage,
			'total' => $total,
			'series' => $series->filterViewable(),
		];

		return $this->view('XenAddons\AMS:Watched\Series', 'xa_ams_watched_series', $viewParams);
	}
	
	public function actionAmsSeriesUpdate()
	{
		$this->assertPostOnly();
		$this->setSectionContext('xa_ams');
	
		/** @var \XenAddons\AMS\Repository\SeriesWatch $watchRepo */
		$watchRepo = $this->repository('XenAddons\AMS:SeriesWatch');
	
		$inputAction = $this->filter('watch_action', 'str');
		$action = $this->getAmsSeriesWatchActionConfig($inputAction, $config);
	
		if ($action)
		{
			$visitor = \XF::visitor();
	
			$ids = $this->filter('ids', 'array-uint');
			$series = $this->em()->findByIds('XenAddons\AMS:SeriesItem', $ids);
	
			/** @var \XenAddons\AMS\Entity\SeriesItem $seriesItem */
			foreach ($series AS $seriesItem)
			{
				$watchRepo->setWatchState($seriesItem, $visitor, $action, $config);
			}
		}
	
		return $this->redirect(
			$this->getDynamicRedirect($this->buildLink('watched/ams-article-series'))
		);
	}
	
	protected function getAmsSeriesWatchActionConfig($inputAction, array &$config = null)
	{
		$config = [];
	
		$parts = explode(':', $inputAction, 2);
	
		$inputAction = $parts[0];
		$boolSwitch = isset($parts[1]) ? ($parts[1] == 'on') : false;
	
		switch ($inputAction)
		{
			case 'send_email':
			case 'send_alert':
				$config = [$inputAction => $boolSwitch];
				return 'update';
	
			case 'delete':
				return 'delete';
	
			default:
				return null;
		}
	}	
}