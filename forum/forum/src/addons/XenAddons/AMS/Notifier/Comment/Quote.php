<?php

namespace XenAddons\AMS\Notifier\Comment;

use XF\Notifier\AbstractNotifier;
use XenAddons\AMS\Entity\Comment;

class Quote extends AbstractNotifier
{
	/**
	 * @var Comment
	 */
	protected $comment;

	public function __construct(\XF\App $app, Comment $comment)
	{
		parent::__construct($app);

		$this->comment = $comment;
	}

	public function canNotify(\XF\Entity\User $user)
	{
		return ($this->comment->isVisible() && $user->user_id != $this->comment->user_id);
	}

	public function sendAlert(\XF\Entity\User $user)
	{
		$comment = $this->comment;

		return $this->basicAlert(
			$user, $comment->user_id, $comment->username, 'ams_comment', $comment->comment_id, 'quote'
		);
	}
}