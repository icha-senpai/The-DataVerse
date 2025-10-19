<?php

namespace XenAddons\AMS\Cron;

/**
 * Cron entry for ams feed importer.
 */
class Feeder
{
	/**
	 * Imports feeds.
	 */
	public static function importFeeds()
	{
		$app = \XF::app();

		/** @var \XenAddons\AMS\Repository\Feed $feedRepo */
		$feedRepo = $app->repository('XenAddons\AMS:Feed');

		$dueFeeds = $feedRepo->findDueFeeds()->fetch();
		if ($dueFeeds->count())
		{
			$app->jobManager()->enqueueUnique('amsFeederImport', 'XenAddons\AMS:Feeder', [], false);
		}
	}
}