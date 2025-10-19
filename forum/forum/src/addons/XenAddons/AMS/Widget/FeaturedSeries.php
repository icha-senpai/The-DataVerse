<?php

namespace XenAddons\AMS\Widget;

use XF\Widget\AbstractWidget;

class FeaturedSeries extends AbstractWidget
{
	protected $defaultOptions = [
		'limit' => 5,
		'part_count' => 1,
		'style' => 'simple',
		'require_series_icon' => false,
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
		
		/** @var \XenAddons\AMS\Finder\SeriesItem $finder */
		$finder = $this->finder('XenAddons\AMS:SeriesItem');
		$finder
			->with('Featured', true)
			->with('User')
			->order($finder->expression('RAND()'));
		
		if ($minRequiredPartCount > 0)
		{
			$finder->where('part_count', '>=', $minRequiredPartCount);
		}
		
		if ($options['require_series_icon'])
		{
			$finder->where('icon_date', '!=', 0);
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
		
		$viewParams = [
			'title' => $this->getTitle(),
			'series' => $series,
			'seriesCount' => $series->count(),
			'style' => $options['style'],
		];
		return $this->renderer('xa_ams_widget_featured_series', $viewParams);
	}

	public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
			'limit' => 'uint',
			'part_count' => 'uint',
			'style' => 'str',
			'require_series_icon' => 'bool'
		]);
		if ($options['limit'] < 1)
		{
			$options['limit'] = 1;
		}

		return true;
	}
	
	/**
	 * @return \XenAddons\AMS\Repository\Series
	 */
	protected function getSeriesRepo()
	{
		return $this->repository('XenAddons\AMS:Series');
	}
}