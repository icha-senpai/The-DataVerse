<?php

namespace XenAddons\AMS\Spam\Cleaner;

use XF\Spam\Cleaner\AbstractHandler;

class Comment extends AbstractHandler
{
	public function canCleanUp(array $options = [])
	{
		return !empty($options['delete_messages']);
	}

	public function cleanUp(array &$log, &$error = null)
	{
		$app = \XF::app();

		$commentsFinder = $app->finder('XenAddons\AMS:Comment');
		$comments = $commentsFinder
			->where('user_id', $this->user->user_id)
			->fetch();

		if ($comments->count())
		{
			$commentIds = $comments->pluckNamed('comment_id');
			$submitter = $app->container('spam.contentSubmitter');
			$submitter->submitSpam('ams_comment', $commentIds);

			$deleteType = $app->options()->spamMessageAction == 'delete' ? 'hard' : 'soft';

			$log['ams_comment'] = [
				'deleteType' => $deleteType,
				'commentIds' => []
			];

			foreach ($comments AS $commentId => $comment)
			{
				$log['ams_comment']['commentIds'][] = $commentId;

				/** @var \XenAddons\AMS\Entity\Comment $comment */
				$comment->setOption('log_moderator', false);
				if ($deleteType == 'soft')
				{
					$comment->comment_state = 'deleted';
					$comment->save();
				}
				else
				{
					$comment->delete();
				}
			}
		}

		return true;
	}

	public function restore(array $log, &$error = null)
	{
		$commentsFinder = \XF::app()->finder('XenAddons\AMS:Comment');

		if ($log['deleteType'] == 'soft')
		{
			$comments = $commentsFinder->where('comment_id', $log['commentIds'])->fetch();
			foreach ($comments AS $comment)
			{
				/** @var \XenAddons\AMS\Entity\Comment $comment */
				$comment->setOption('log_moderator', false);
				$comment->comment_state = 'visible';
				$comment->save();
			}
		}

		return true;
	}
}