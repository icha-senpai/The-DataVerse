<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class Feature extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\SeriesItem
	 */
	protected $series;

	public function __construct(\XF\App $app, SeriesItem $series)
	{
		parent::__construct($app);
		$this->series = $series;
	}

	public function getSeries()
	{
		return $this->series;
	}

	public function feature()
	{
		$db = $this->db();
		$db->beginTransaction();

			$affected = $db->insert('xf_xa_ams_series_feature', [
			'series_id' => $this->series->series_id,
			'feature_date' => \XF::$time
		], false, 'feature_date = VALUES(feature_date)');
		
		if ($affected == 1)
		{
			$this->onNewFeature();
		}

		$db->commit();
	}

	protected function onNewFeature()
	{
		$this->series->last_feature_date = \XF::$time;
		$this->series->save();
		
		$this->app->logger()->logModeratorAction('ams_series', $this->series, 'feature');
	}

	public function unfeature()
	{
		$db = $this->db();
		$db->beginTransaction();

			$affected = $db->delete('xf_xa_ams_series_feature', 'series_id = ?', $this->series->series_id);
		if ($affected)
		{
			$this->onUnfeature();
		}

		$db->commit();
	}

	protected function onUnfeature()
	{
		$this->app->logger()->logModeratorAction('ams_series', $this->series, 'unfeature');
	}
}