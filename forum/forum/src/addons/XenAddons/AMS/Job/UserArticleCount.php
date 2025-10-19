<?php

namespace XenAddons\AMS\Job;

use XF\Job\AbstractRebuildJob;

class UserArticleCount extends AbstractRebuildJob
{
	protected function getNextIds($start, $batch)
	{
		$db = $this->app->db();

		return $db->fetchAllColumn($db->limit(
			"
				SELECT user_id
				FROM xf_user
				WHERE user_id > ?
				ORDER BY user_id
			", $batch
		), $start);
	}

	protected function rebuildById($id)
	{
		/** @var \XenAddons\AMS\Repository\Article $repo */
		$repo = $this->app->repository('XenAddons\AMS:Article');
		$articleCount = $repo->getUserArticleCount($id);
		
		/** @var \XenAddons\AMS\Repository\Series $repo */
		$repo = $this->app->repository('XenAddons\AMS:Series');
		$seriesCount = $repo->getUserSeriesCount($id);
		
		$this->app->db()->update('xf_user', [
			'xa_ams_article_count' => $articleCount,
			'xa_ams_series_count' => $seriesCount
		], 'user_id = ?', $id);
	}

	protected function getStatusType()
	{
		return \XF::phrase('xa_ams_user_counts');
	}
}