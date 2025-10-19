<?php

namespace XenAddons\AMS\Sitemap;

use XF\Sitemap\AbstractHandler;
use XF\Sitemap\Entry;

class Series extends AbstractHandler
{
	public function getRecords($start)
	{
		$user = \XF::visitor();

		$ids = $this->getIds('xf_xa_ams_series', 'series_id', $start);

		$finder = $this->app->finder('XenAddons\AMS:SeriesItem');
		$series = $finder
			->where('series_id', $ids)
			->order('series_id')
			->fetch();

		return $series;
	}

	/**
	 * @param $record \XenAddons\AMS\Entity\SeriesItem
	 *
	 * @return Entry
	 */
	public function getEntry($record)
	{
		$url = $this->app->router('public')->buildLink('canonical:ams/series', $record);
		return Entry::create($url, [
			'lastmod' => $record->edit_date
		]);
	}

	public function isIncluded($record)
	{
		/** @var $record \XenAddons\AMS\Entity\SeriesItem */

		return $record->canView();
	}
}