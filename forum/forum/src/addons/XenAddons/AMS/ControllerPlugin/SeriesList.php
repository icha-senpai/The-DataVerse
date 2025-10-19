<?php

namespace XenAddons\AMS\ControllerPlugin;

use XF\ControllerPlugin\AbstractPlugin;

class SeriesList extends AbstractPlugin
{
	public function getseriesListData()
	{
		$seriesRepo = $this->getSeriesRepo();
		
		$allowOwnPending = true;  // Series owners will be able to see their pending AMS Series on the "Series Index" page.

		$seriesFinder = $seriesRepo->findSeriesForSeriesList([
			'allowOwnPending' => $allowOwnPending
		]);

		$filters = $this->getSeriesFilterInput();
		$this->applySeriesFilters($seriesFinder, $filters);
		
		// Featured Series are not fetched if any filters are applied!
		if (!$filters && $featuredLimit = $this->options()->xaAmsFeaturedSeriesLimit)
		{
			$featuredSeries = $seriesRepo->findFeaturedSeries()
				->fetch($featuredLimit)
				->filterViewable();
			
			if ($featuredSeries && $this->options()->xaAmsExcludeFeaturedSeriesFromListing)
			{
				$excludeSeriesIds = $featuredSeries->pluckNamed('series_id');
				$seriesFinder->where('series_id', '<>', $excludeSeriesIds);
			}
		}
		else
		{
			$featuredSeries = $this->em()->getEmptyCollection();
		}		

		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsSeriesPerPage;

		$seriesFinder->limitByPage($page, $perPage);
		
		$series = $seriesFinder->fetch()->filterViewable();
		$totalSeries = $seriesFinder->total();

		if (!empty($filters['creator_id']))
		{
			$creatorFilter = $this->em()->find('XF:User', $filters['creator_id']);
		}
		else
		{
			$creatorFilter = null;
		}
		
		$canInlineMod = false;
		foreach ($series AS $serieItem)
		{
			/** @var \XenAddons\AMS\Entity\Series $serieItem */
			if ($serieItem->canUseInlineModeration())
			{
				$canInlineMod = true;
				break;
			}
		}

		return [
			'series' => $series,
			'filters' => $filters,
			'creatorFilter' => $creatorFilter,
			'canInlineMod' => $canInlineMod,

			'total' => $totalSeries,
			'page' => $page,
			'perPage' => $perPage,

			'featuredSeries' => $featuredSeries
		];
	}

	public function applySeriesFilters(\XenAddons\AMS\Finder\SeriesItem $seriesFinder, array $filters)
	{
		if (!empty($filters['featured']))
		{
			$seriesFinder->where('Featured.feature_date', '>', 0);
		}
		
		if (!empty($filters['has_parts']))
		{
			$seriesFinder->where('part_count', '>', 0);
		}
		
		if (!empty($filters['community']))
		{
			$seriesFinder->where('community_series', '=', 1);
		}
		
		if (!empty($filters['state']))
		{
			switch ($filters['state'])
			{
				case 'visible':
					$seriesFinder->where('series_state', 'visible');
					break;
		
				case 'moderated':
					$seriesFinder->where('series_state', 'moderated');
					break;
		
				case 'deleted':
					$seriesFinder->where('series_state', 'deleted');
					break;
			}
		}
		
		if (!empty($filters['title']))
		{
			if ($filters['title'] == -1)
			{
				// fetch all series with titles that start with numeral
				$seriesFinder->whereOr(
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(0, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(1, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(2, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(3, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(4, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(5, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(6, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(7, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(8, '?%')],
					[$seriesFinder->columnUtf8('title'), 'LIKE', $seriesFinder->escapeLike(9, '?%')]
				);
			}
			else
			{
				$seriesFinder->where(
					$seriesFinder->columnUtf8('title'),
					'LIKE', $seriesFinder->escapeLike($filters['title'], '?%'));
			}
		}
		
		if (!empty($filters['creator_id']))
		{
			$seriesFinder->where('user_id', intval($filters['creator_id']));
		}

		$sorts = $this->getAvailableSeriesSorts();

		if (!empty($filters['order']) && isset($sorts[$filters['order']]))
		{
			$seriesFinder->order($sorts[$filters['order']], $filters['direction']);
		}
	}

	public function getSeriesFilterInput()
	{
		$filters = [];

		$input = $this->filter([
			'featured' => 'bool',
			'has_parts' => 'bool',
			'community' => 'bool',
			'state' => 'str',
			'title' => 'str',
			'creator' => 'str',
			'creator_id' => 'uint',
			'order' => 'str',
			'direction' => 'str'
		]);
		
		if ($input['featured'])
		{
			$filters['featured'] = true;
		}
		
		if ($input['has_parts'])
		{
			$filters['has_parts'] = true;
		}
		
		if ($input['community'])
		{
			$filters['community'] = true;
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

		$sorts = $this->getAvailableSeriesSorts();

		if ($input['order'] && isset($sorts[$input['order']]))
		{
			if (!in_array($input['direction'], ['asc', 'desc']))
			{
				$input['direction'] = 'desc';
			}

			$defaultOrder = 'last_part_date'; 
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
	
	public function getAvailableSeriesSorts()
	{
		// maps [name of sort] => field in/relative to Series entity
		return [
			'last_part_date' => 'last_part_date',
			'create_date' => 'create_date',
			'part_count' => 'part_count',
			'title' => 'title'
		];
	}

	public function actionFilters()
	{
		$filters = $this->getSeriesFilterInput();

		if ($this->filter('apply', 'bool'))
		{
			return $this->redirect($this->buildLink('ams/series', null, $filters));
		}

		if (!empty($filters['creator_id']))
		{
			$creatorFilter = $this->em()->find('XF:User', $filters['creator_id']);
		}
		else
		{
			$creatorFilter = null;
		}

		$showTitleFilters = $this->options()->xaAmsSeriesListShowTitleFilters;

		$defaultOrder = 'last_part_date'; 
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
			'filters' => $filters,
			'creatorFilter' => $creatorFilter,
			'showTitleFilters' => $showTitleFilters
		];
		return $this->view('XenAddons\AMS\Series:Filters', 'xa_ams_series_filters', $viewParams);
	}

	public function actionFeatured()
	{
		$finder = $this->getSeriesRepo()->findFeaturedSeries();
		$finder->order('Featured.feature_date', 'desc');

		$series = $finder->fetch()->filterViewable();

		$viewParams = [
			'series' => $series,
		];
		return $this->view('XenAddons\AMS\Series:Featured', 'xa_ams_series_featured', $viewParams);
	}

	/**
	 * @return \XenAddons\AMS\Repository\Series
	 */
	protected function getSeriesRepo()
	{
		return $this->repository('XenAddons\AMS:Series');
	}
}