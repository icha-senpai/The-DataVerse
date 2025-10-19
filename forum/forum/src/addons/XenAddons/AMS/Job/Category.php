<?php

namespace XenAddons\AMS\Job;

use XF\Job\AbstractRebuildJob;

class Category extends AbstractRebuildJob
{
	protected function getNextIds($start, $batch)
	{
		$db = $this->app->db();

		return $db->fetchAllColumn($db->limit(
			"
				SELECT category_id
				FROM xf_xa_ams_category
				WHERE category_id > ?
				ORDER BY category_id
			", $batch
		), $start);
	}

	protected function rebuildById($id)
	{
		/** @var \XenAddons\AMS\Entity\Category $category */
		$category = $this->app->em()->find('XenAddons\AMS:Category', $id);
		if ($category)
		{
			$category->rebuildCounters();
			$category->save();
		}
	}

	protected function getStatusType()
	{
		return \XF::phrase('xa_ams_article_categories');
	}
}