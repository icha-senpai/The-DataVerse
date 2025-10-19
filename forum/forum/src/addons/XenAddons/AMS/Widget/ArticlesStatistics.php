<?php

namespace XenAddons\AMS\Widget;

use XF\Widget\AbstractWidget;

class ArticlesStatistics extends AbstractWidget
{
	public function render()
	{
			/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		if (!method_exists($visitor, 'canViewAmsArticles') || !$visitor->canViewAmsArticles())
		{
			return '';
		}

		$simpleCache = $this->app->simpleCache();
		
		$viewParams = [
			'statsCache' => $simpleCache['XenAddons/AMS']['statisticsCache']
		];
		return $this->renderer('xa_ams_widget_articles_statistics', $viewParams);
	}

	public function getOptionsTemplate()
	{
		return;
	}
}