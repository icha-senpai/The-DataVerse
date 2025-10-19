<?php

namespace XenAddons\AMS\Notifier\Review;

use XF\Notifier\AbstractNotifier;

class ArticleWatch extends AbstractNotifier
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleRating
	 */
	protected $review;

	public function __construct(\XF\App $app, \XenAddons\AMS\Entity\ArticleRating $review)
	{
		parent::__construct($app);

		$this->review = $review;
	}

	public function canNotify(\XF\Entity\User $user)
	{
		$review = $this->review;

		if ($user->user_id == $review->user_id || $user->isIgnoring($review->user_id))
		{
			return false;
		}

		return true;
	}

	public function sendAlert(\XF\Entity\User $user)
	{
		$review = $this->review;

		return $this->basicAlert(
			$user, $review->user_id, $review->username, 'ams_rating', $review->rating_id, 'insert'
		);
	}

	public function sendEmail(\XF\Entity\User $user)
	{
		if (!$user->email || $user->user_state != 'valid')
		{
			return false;
		}

		$review = $this->review;

		$params = [
			'review' => $review,
			'content' => $review->Content,
			'receiver' => $user
		];

		$this->app()->mailer()->newMail()
			->setToUser($user)
			->setTemplate('xa_ams_watched_article_review', $params)
			->queue();

		return true;
	}

	public function getDefaultNotifyData()
	{
		$review = $this->review;
		$content = $review->Content;

		if (!$content)
		{
			return [];
		}

		$finder = $this->app()->finder('XenAddons\AMS:ArticleWatch');

		$finder->where('article_id', $review->article_id)
			->where('User.user_state', '=', 'valid')
			->where('User.is_banned', '=', 0);

		$activeLimit = $this->app()->options()->watchAlertActiveOnly;
		if (!empty($activeLimit['enabled']))
		{
			$finder->where('User.last_activity', '>=', \XF::$time - 86400 * $activeLimit['days']);
		}

		$notifyData = [];
		foreach ($finder->fetchColumns(['user_id', 'email_subscribe']) AS $watch)
		{
			$notifyData[$watch['user_id']] = [
				'alert' => true,
				'email' => (bool)$watch['email_subscribe']
			];
		}

		return $notifyData;
	}
}