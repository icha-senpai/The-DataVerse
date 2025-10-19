<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XF\PrintableException;

class SeriesPart extends Repository
{
	public function findPartsInSeries(\XenAddons\AMS\Entity\SeriesItem $series, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\SeriesPart $finder */
		$finder = $this->finder('XenAddons\AMS:SeriesPart');
		$finder->inSeries($series, $limits)
			->where('Article.article_state', 'visible')
			->setDefaultOrder('display_order', 'asc');

		return $finder;
	}
	
	public function findPartsInSeriesManageSeries(\XenAddons\AMS\Entity\SeriesItem $series, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\SeriesPart $finder */
		$finder = $this->finder('XenAddons\AMS:SeriesPart');
		$finder->inSeries($series, $limits)
		->setDefaultOrder('display_order', 'asc');
	
		return $finder;
	}

	public function findPartsInSeriesDeleteSeries(\XenAddons\AMS\Entity\SeriesItem $series, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\SeriesPart $finder */
		$finder = $this->finder('XenAddons\AMS:SeriesPart');
		$finder->inSeries($series, $limits)
		->setDefaultOrder('display_order', 'asc');
	
		return $finder;
	}
}