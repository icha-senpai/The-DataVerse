<?php

namespace XenAddons\AMS\Notifier\Review;

use XF\Notifier\AbstractNotifier;
use XenAddons\AMS\Entity\ArticleRating;

class Mention extends AbstractNotifier
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

	public function canNotify(\XF\Entity\User $user)
	{
		return ($this->rating->isVisible() && $user->user_id != $this->rating->user_id);
	}

	public function sendAlert(\XF\Entity\User $user)
	{
		$rating = $this->rating;

		return $this->basicAlert(
			$user, $rating->user_id, $rating->username, 'ams_rating', $rating->rating_id, 'mention'
		);
	}
}