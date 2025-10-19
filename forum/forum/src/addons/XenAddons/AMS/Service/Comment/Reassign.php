<?php

namespace XenAddons\AMS\Service\Comment;

use XenAddons\AMS\Entity\Comment;

class Reassign extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\Comment
	 */
	protected $comment;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, Comment $comment)
	{
		parent::__construct($app);
		$this->comment = $comment;
	}

	public function getComment()
	{
		return $this->comment;
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
		$comment = $this->comment;
		$oldUser = $comment->User;
		$reassigned = ($comment->user_id != $newUser->user_id);

		$comment->user_id = $newUser->user_id;
		$comment->username = $newUser->username;
		$comment->save();

		if ($reassigned && $comment->isVisible() && $this->alert) 
		{
			if ($oldUser && \XF::visitor()->user_id != $oldUser->user_id)
			{	
				/** @var \XenAddons\AMS\Repository\Comment $commentRepo */
				$commentRepo = $this->repository('XenAddons\AMS:Comment');
				$commentRepo->sendModeratorActionAlert(
					$this->comment, 'reassign_from', $this->alertReason, ['to' => $newUser->username], $oldUser
				);
			}
			
			if (\XF::visitor()->user_id != $newUser->user_id)
			{
				/** @var \XenAddons\AMS\Repository\Comment $commentRepo */
				$commentRepo = $this->repository('XenAddons\AMS:Comment');
				$commentRepo->sendModeratorActionAlert(
					$this->comment, 'reassign_to', $this->alertReason, [], $newUser
				);
			}
		}

		return $reassigned;
	}
}