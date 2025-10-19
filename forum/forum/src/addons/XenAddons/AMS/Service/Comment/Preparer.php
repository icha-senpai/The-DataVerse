<?php

namespace XenAddons\AMS\Service\Comment;

use XenAddons\AMS\Entity\Comment;

class Preparer extends \XF\Service\AbstractService
{
	/**
	 * @var Comment
	 */
	protected $comment;
	
	protected $attachmentHash;

	protected $logIp = true;

	protected $quotedComments = [];

	protected $mentionedUsers = [];

	public function __construct(\XF\App $app, Comment $comment)
	{
		parent::__construct($app);
		$this->setComment($comment);
	}

	public function setComment(Comment $comment)
	{
		$this->comment = $comment;
	}

	public function getComment()
	{
		return $this->comment;
	}

	public function logIp($logIp)
	{
		$this->logIp = $logIp;
	}

	public function getQuotedComments()
	{
		return $this->quotedComments;
	}

	public function getQuotedUserIds()
	{
		if (!$this->quotedComments)
		{
			return [];
		}

		$commentIds = array_keys($this->quotedComments);
		$quotedUserIds = [];

		$db = $this->db();
		$commentUserMap = $db->fetchPairs("
			SELECT comment_id, user_id
			FROM xf_xa_ams_comment
			WHERE comment_id IN (" . $db->quote($commentIds) .")
		");
		foreach ($commentUserMap AS $commentId => $userId)
		{
			if (!isset($this->quotedComments[$commentId]) || !$userId)
			{
				continue;
			}

			$quote = $this->quotedComments[$commentId];
			if (!isset($quote['member']) || $quote['member'] == $userId)
			{
				$quotedUserIds[] = $userId;
			}
		}

		return $quotedUserIds;
	}

	public function getMentionedUsers($limitPermissions = true)
	{
		if ($limitPermissions && $this->comment)
		{
			/** @var \XF\Entity\User $user */
			$user = $this->comment->User ?: $this->repository('XF:User')->getGuestUser();
			return $user->getAllowedUserMentions($this->mentionedUsers);
		}
		else
		{
			return $this->mentionedUsers;
		}
	}

	public function getMentionedUserIds($limitPermissions = true)
	{
		return array_keys($this->getMentionedUsers($limitPermissions));
	}

	public function setMessage($message, $format = true)
	{
		$preparer = $this->getMessagePreparer($format);
		$this->comment->message = $preparer->prepare($message);
		$this->comment->embed_metadata = $preparer->getEmbedMetadata();

		$this->quotedComments = $preparer->getQuotesKeyed('ams-comment');
		$this->mentionedUsers = $preparer->getMentionedUsers();

		return $preparer->pushEntityErrorIfInvalid($this->comment);
	}

	/**
	 * @param bool $format
	 *
	 * @return \XF\Service\Message\Preparer
	 */
	protected function getMessagePreparer($format = true)
	{
		$options = $this->app->options();
		
		if ($options->messageMaxLength && $options->xaAmsCommentMaxLength)
		{
			$ratio = ceil($options->xaAmsCommentMaxLength / $options->messageMaxLength);
			$maxImages = $options->messageMaxImages * $ratio;
			$maxMedia = $options->messageMaxMedia * $ratio;
		}
		else
		{
			$maxImages = 100;
			$maxMedia = 30;
		}
		
		/** @var \XF\Service\Message\Preparer $preparer */
		$preparer = $this->service('XF:Message\Preparer', 'ams_comment', $this->comment);
		$preparer->setConstraint('maxLength', $options->xaAmsCommentMaxLength);
		$preparer->setConstraint('maxImages', $maxImages);
		$preparer->setConstraint('maxMedia', $maxMedia);
		
		if (!$format)
		{
			$preparer->disableAllFilters();
		}

		return $preparer;
	}
	
	public function setAttachmentHash($hash)
	{
		$this->attachmentHash = $hash;
	}

	public function checkForSpam()
	{
		$comment = $this->comment;
		$content = $this->comment->Content;

		/** @var \XF\Entity\User $user */
		$user = $comment->User ?: $this->repository('XF:User')->getGuestUser($comment->username);

		$message = $comment->message;

		$router = $this->app->router('public');
		$checker = $this->app->spam()->contentChecker();
		$checker->check($user, $message, [
			'permalink' => $router->buildLink('canonical:ams', $content),
			'content_type' => 'ams_comment'
		]);

		$decision = $checker->getFinalDecision();
		switch ($decision)
		{
			case 'moderated':
				$comment->comment_state = 'moderated';
				break;

			case 'denied':
				$checker->logSpamTrigger('ams_comment', $comment->comment_id);
				$comment->error(\XF::phrase('your_content_cannot_be_submitted_try_later'));
				break;
		}
	}

	public function afterInsert()
	{
		if ($this->attachmentHash)
		{
			$this->associateAttachments($this->attachmentHash);
		}
		
		if ($this->logIp)
		{
			$ip = ($this->logIp === true ? $this->app->request()->getIp() : $this->logIp);
			$this->writeIpLog($ip);
		}

		$comment = $this->comment;
		$checker = $this->app->spam()->contentChecker();

		$checker->logContentSpamCheck('ams_comment', $comment->comment_id);
		$checker->logSpamTrigger('ams_comment', $comment->comment_id);
	}

	public function afterUpdate()
	{
		if ($this->attachmentHash)
		{
			$this->associateAttachments($this->attachmentHash);
		}
		
		$comment = $this->comment;
		$checker = $this->app->spam()->contentChecker();

		$checker->logSpamTrigger('ams_comment', $comment->comment_id);
	}
	
	protected function associateAttachments($hash)
	{
		$comment = $this->comment;
	
		/** @var \XF\Service\Attachment\Preparer $inserter */
		$inserter = $this->service('XF:Attachment\Preparer');
		$associated = $inserter->associateAttachmentsWithContent($hash, 'ams_comment', $comment->comment_id);
	
		if ($associated)
		{
			$comment->fastUpdate('attach_count', $comment->attach_count + $associated);
		}
	}

	protected function writeIpLog($ip)
	{
		$comment = $this->comment;

		/** @var \XF\Repository\IP $ipRepo */
		$ipRepo = $this->repository('XF:Ip');
		$ipEnt = $ipRepo->logIp($comment->user_id, $ip, 'ams_comment', $comment->comment_id);
		if ($ipEnt)
		{
			$comment->fastUpdate('ip_id', $ipEnt->ip_id);
		}
	}
}