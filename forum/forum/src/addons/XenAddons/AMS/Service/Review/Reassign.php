<?php

namespace XenAddons\AMS\Service\Review;

use XenAddons\AMS\Entity\ArticleRating;

class Reassign extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleRating
	 */
	protected $rating;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, ArticleRating $rating)
	{
		parent::__construct($app);
		$this->rating = $rating;
	}

	public function getArticleRating()
	{
		return $this->rating;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function reassignTo(\XF\Entity\User $newUser)
	{
		$rating = $this->rating;
		$oldUser = $rating->User;
		$reassigned = ($rating->user_id != $newUser->user_id);

		$rating->user_id = $newUser->user_id;
		$rating->username = $newUser->username;
		$rating->save();

		if ($reassigned && $rating->isVisible() && $this->alert) 
		{
			if ($oldUser && \XF::visitor()->user_id != $oldUser->user_id)
			{	
				/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
				$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
				$ratingRepo->sendModeratorActionAlert(
					$this->rating, 'reassign_from', $this->alertReason, ['to' => $newUser->username], $oldUser
				);
			}
			
			if (\XF::visitor()->user_id != $newUser->user_id)
			{
				/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
				$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
				$ratingRepo->sendModeratorActionAlert(
					$this->rating, 'reassign_to', $this->alertReason, [], $newUser
				);
			}
		}

		return $reassigned;
	}
}