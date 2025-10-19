<?php

namespace XenAddons\AMS\Service\Review;

use XF\Service\AbstractNotifier;
use XenAddons\AMS\Entity\ArticleRating;

class Notifier extends AbstractNotifier
{
	/**
	 * @var ArticleRating
	 */
	protected $rating;

	public function __construct(\XF\App $app, ArticleRating $rating)
	{
		parent::__construct($app);

		$this->rating = $rating;
	}

	public static function createForJob(array $extraData)
	{
		$rating = \XF::app()->find('XenAddons\AMS:ArticleRating', $extraData['ratingId']);
		if (!$rating)
		{
			return null;
		}

		return \XF::service('XenAddons\AMS:Review\Notifier', $rating);
	}

	protected function getExtraJobData()
	{
		return [
			'ratingId' => $this->rating->rating_id
		];
	}

	protected function loadNotifiers()
	{
		$notifiers = [
			'mention' => $this->app->notifier('XenAddons\AMS:Review\Mention', $this->rating)
		];

		$notifiers['articleWatch'] = $this->app->notifier('XenAddons\AMS:Review\ArticleWatch', $this->rating);


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
			function() { return $this->rating->canView(); }
		);
	}
}