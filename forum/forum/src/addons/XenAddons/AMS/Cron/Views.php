<?php

namespace XenAddons\AMS\Cron;

class Views
{
	public static function runViewUpdate()
	{
		$app = \XF::app();

		/** @var \XenAddons\AMS\Repository\Article $articleRepo */
		$articleRepo = $app->repository('XenAddons\AMS:Article');
		$articleRepo->batchUpdateArticleViews();
	}
}