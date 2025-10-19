<?php

namespace XenAddons\AMS\Service\Review;

use XenAddons\AMS\Entity\ArticleRating;

class Deleter extends \XF\Service\AbstractService
{
	/**
	 * @var ArticleRating
	 */
	protected $rating;

	protected $user;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, ArticleRating $rating)
	{
		parent::__construct($app);
		$this->setComment($rating);
	}

	public function setComment(ArticleRating $rating)
	{
		$this->rating = $rating;
	}

	public function getRating()
	{
		return $this->rating;
	}

	public function setUser(\XF\Entity\User $user = null)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function delete($type, $reason = '')
	{
		$user = $this->user ?: \XF::visitor();

		$result = null;

		$wasVisible = $this->rating->rating_state == 'visible';

		if ($type == 'soft')
		{
			$result = $this->rating->softDelete($reason, $user);
		}
		else
		{
			$result = $this->rating->delete();
		}

		if ($result && $wasVisible && $this->alert && $this->rating->user_id != $user->user_id)
		{
			/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
			$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
			$ratingRepo->sendModeratorActionAlert($this->rating, 'delete', $this->alertReason);
		}

		return $result;
	}
}