<?php

namespace XenAddons\AMS\Cron;

class CleanUp
{
	public static function runHourlyCleanUp()
	{
		$app = \XF::app();
	
		/** @var \XenAddons\AMS\Repository\ArticleReplyBan $articleReplyBanRepo */
		$articleReplyBanRepo = $app->repository('XenAddons\AMS:ArticleReplyBan');
		$articleReplyBanRepo->cleanUpExpiredBans();
	}
	
	public static function runDailyCleanUp()
	{
		$app = \XF::app();

		/** @var \XenAddons\AMS\Repository\Article $artilceRepo */
		$artilceRepo = $app->repository('XenAddons\AMS:Article');
		$artilceRepo->pruneArticleReadLogs();
		$artilceRepo->autoUnfeatureArticles();
	}
}