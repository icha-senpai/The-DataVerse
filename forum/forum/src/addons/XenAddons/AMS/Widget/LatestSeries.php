<?php

namespace XenAddons\AMS\Widget;

use XF\Widget\AbstractWidget;

class LatestSeries extends AbstractWidget
{
	protected $defaultOptions = [
		'order' => 'last_part_date',
		'limit' => 5,
		'part_count' => 1,
		'cutOffDays' => 0,
		'style' => 'simple',
		'require_series_icon' => false,
		'block_title_link' => ''
	];

	protected function getDefaultTemplateParams($context)
	{
		$params = parent::getDefaultTemplateParams($context);
		if ($context == 'options')
		{
		}
		return $params;
	}

	public function render()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		if (!method_exists($visitor, 'canViewAmsArticles') || !$visitor->canViewAmsArticles())
		{
			return '';
		}

		$options = $this->options;
		$limit = $options['limit'];
		$minRequiredPartCount = $options['part_count'];
		$cutOffDays = $options['cutOffDays'];
		$order = $options['order'] ? : 'last_part_date';

		/** @var \XenAddons\AMS\Finder\SeriesItem $finder */
		$finder = $this->finder('XenAddons\AMS:SeriesItem');
		$finder
			->with('User');
		
		if ($minRequiredPartCount > 0)
		{
			$finder->where('part_count', '>=', $minRequiredPartCount);
		}
		
		if ($options['require_series_icon'])
		{
			$finder->where('icon_date', '!=', 0);
		}

		if ($cutOffDays)
		{
			$cutOffDate = \XF::$time - ($cutOffDays * 86400);
			
			if ($order == 'create_date')
			{
				$finder->where('create_date', '>', $cutOffDate);
			}
			else 
			{
				$finder->where('last_part_date', '>', $cutOffDate);
			}	
		}
		
		if ($order == 'random')
		{
			$finder->order($finder->expression('RAND()'));
		}
		else
		{
			$finder->order($order, 'desc');
		}
		
		$series = $finder->fetch(max($limit * 2, 10));

		/** @var \XenAddons\AMS\Entity\SeriesItem $seriesItem */
		foreach ($series AS $seriesId => $seriesItem)
		{
			if (!$seriesItem->canView() || $visitor->isIgnoring($seriesItem->user_id))
			{
				unset($series[$seriesId]);
			}
		}

		$total = $series->count();
		$series = $series->slice(0, $limit, true);
		
		// check to see if there is a block_title_link to be used for the block header! 
		if (isset ($options['block_title_link']) && $options['block_title_link'])
		{
			$link = $options['block_title_link'];
		}
		else 
		{
			$router = $this->app->router('public');
			$link = $router->buildLink('ams/series');
		}
		
		$viewParams = [
			'title' => $this->getTitle(),
			'link' => $link,
			'series' => $series,
			'seriesCount' => $series->count(),
			'style' => $options['style'],
		];
		return $this->renderer('xa_ams_widget_latest_series', $viewParams);
	}

	public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
			'order' => 'str',
			'limit' => 'uint',
			'part_count' => 'uint',
			'cutOffDays' => 'uint',
			'style' => 'str',
			'require_series_icon' => 'bool',
			'block_title_link' => 'str'
		]);
		if ($options['limit'] < 1)
		{
			$options['limit'] = 1;
		}

		return true;
	}
}