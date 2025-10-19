<?php

namespace XenAddons\AMS\Service\Comment;

use XF\Service\AbstractNotifier;
use XenAddons\AMS\Entity\Comment;

class Notifier extends AbstractNotifier
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

	public static function createForJob(array $extraData)
	{
		$comment = \XF::app()->find('XenAddons\AMS:Comment', $extraData['commentId']);
		if (!$comment)
		{
			return null;
		}

		return \XF::service('XenAddons\AMS:Comment\Notifier', $comment);
	}

	protected function getExtraJobData()
	{
		return [
			'commentId' => $this->comment->comment_id
		];
	}

	protected function loadNotifiers()
	{
		$notifiers = [
			'mention' => $this->app->notifier('XenAddons\AMS:Comment\Mention', $this->comment),
			'quote' => $this->app->notifier('XenAddons\AMS:Comment\Quote', $this->comment)
		];

		// if this is not the last comment, then another notification would have been triggered already
		if ($this->comment->isLastComment())
		{
			$notifiers['articleWatch'] = $this->app->notifier('XenAddons\AMS:Comment\ArticleWatch', $this->comment);
		}

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
			function() { return $this->comment->canView(); }
		);
	}
	
	// TODO should we be skipping users watching Category here?  (similar to posts)
}