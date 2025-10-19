<?php

namespace XenAddons\AMS\Service\SeriesPart;

use XF\Service\AbstractNotifier;
use XenAddons\AMS\Entity\SeriesPart;

class Notifier extends AbstractNotifier
{
	/**
	 * @var SeriesPart
	 */
	protected $seriesPart;

	public function __construct(\XF\App $app, SeriesPart $seriesPart)
	{
		parent::__construct($app);

		$this->seriesPart = $seriesPart;
	}

	public static function createForJob(array $extraData)
	{
		$seriesPart = \XF::app()->find('XenAddons\AMS:SeriesPart', $extraData['partId']);
		if (!$seriesPart)
		{
			return null;
		}

		return \XF::service('XenAddons\AMS:SeriesPart\Notifier', $seriesPart);
	}

	protected function getExtraJobData()
	{
		return [
			'partId' => $this->seriesPart->part_id
		];
	}

	protected function loadNotifiers()
	{
		$notifiers = [];

		$notifiers['seriesWatch'] = $this->app->notifier('XenAddons\AMS:SeriesPart\SeriesWatch', $this->seriesPart);

		return $notifiers;
	}

	protected function loadExtraUserData(array $users)
	{
		return;
	}

	protected function canUserViewContent(\XF\Entity\User $user)
	{
		return \XF::asVisitor(
			$user,
			function() { return $this->seriesPart->canView(); }
		);
	}
}