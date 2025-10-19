<?php

namespace XenAddons\AMS\Cron;

class Publisher
{
	
	public static function runPublishScheduledArticles()
	{
		$app = \XF::app();

		/** @var \XenAddons\AMS\Repository\Article $artilceRepo */
		$artilceRepo = $app->repository('XenAddons\AMS:Article');
		$artilceRepo->publishScheduledArticles();
	}
}