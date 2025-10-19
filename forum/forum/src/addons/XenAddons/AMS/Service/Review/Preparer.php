<?php

namespace XenAddons\AMS\Service\Review;

use XenAddons\AMS\Entity\ArticleRating;

class Preparer extends \XF\Service\AbstractService
{
	/**
	 * @var ArticleRating
	 */
	protected $articleRating;

	protected $attachmentHash;

	protected $logIp = true;

	protected $mentionedUsers = [];

	public function __construct(\XF\App $app, ArticleRating $articleRating)
	{
		parent::__construct($app);
		$this->setArticleRating($articleRating);
	}
	
	public function setArticleRating(ArticleRating $articleRating)
	{
		$this->articleRating = $articleRating;
	}

	public function getArticleRating()
	{
		return $this->articleRating;
	}

	public function logIp($logIp)
	{
		$this->logIp = $logIp;
	}

	public function getMentionedUsers($limitPermissions = true)
	{
		if ($limitPermissions)
		{
			/** @var \XF\Entity\User $user */
			$user = $this->articleRating->User ?: $this->repository('XF:User')->getGuestUser();
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
	
	public function setMessage($message, $format = true, $checkValidity = true)
	{
		$preparer = $this->getMessagePreparer($format);
		$this->articleRating->message = $preparer->prepare($message, $checkValidity);
		$this->articleRating->embed_metadata = $preparer->getEmbedMetadata();

		$this->mentionedUsers = $preparer->getMentionedUsers();

		return $preparer->pushEntityErrorIfInvalid($this->articleRating);
	}

	/**
	 * @param bool $format
	 *
	 * @return \XF\Service\Message\Preparer
	 */
	protected function getMessagePreparer($format = true)
	{
		$options = $this->app->options();

		if ($options->messageMaxLength && $options->xaAmsReviewMaxLength)
		{
			$ratio = ceil($options->xaAmsReviewMaxLength / $options->messageMaxLength);
			$maxImages = $options->messageMaxImages * $ratio;
			$maxMedia = $options->messageMaxMedia * $ratio;
		}
		else
		{
			$maxImages = 100;
			$maxMedia = 30;
		}

		/** @var \XF\Service\Message\Preparer $preparer */
		$preparer = $this->service('XF:Message\Preparer', 'ams_rating', $this->articleRating);
		$preparer->setConstraint('allowEmpty', true);
		$preparer->setConstraint('maxLength', $options->xaAmsReviewMaxLength);
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
		$articleRating = $this->articleRating;

		/** @var \XF\Entity\User $user */
		$user = $articleRating->User ?: $this->repository('XF:User')->getGuestUser($articleRating->username);

		$message = $articleRating->title . "\n" . $articleRating->message;

		$checker = $this->app->spam()->contentChecker();
		$checker->check($user, $message, [
			'permalink' => $this->app->router('public')->buildLink('canonical:ams', $articleRating),
			'content_type' => 'ams_article'
		]);

		$decision = $checker->getFinalDecision();
		switch ($decision)
		{
			case 'moderated':
				$articleRating->rating_state = 'moderated';
				break;

			case 'denied':
				$checker->logSpamTrigger('ams_article', null);
				$articleRating->error(\XF::phrase('your_content_cannot_be_submitted_try_later'));
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

		$articleRating = $this->articleRating;
		$checker = $this->app->spam()->contentChecker();

		$checker->logContentSpamCheck('ams_rating', $articleRating->rating_id);
		$checker->logSpamTrigger('ams_rating', $articleRating->rating_id);

	}

	public function afterUpdate()
	{
		if ($this->attachmentHash)
		{
			$this->associateAttachments($this->attachmentHash);
		}
		
		$articleRating = $this->articleRating;
		$checker = $this->app->spam()->contentChecker();

		$checker->logSpamTrigger('ams_rating', $articleRating->rating_id);
	}
	
	protected function associateAttachments($hash)
	{
		$articleRating = $this->articleRating;
	
		/** @var \XF\Service\Attachment\Preparer $inserter */
		$inserter = $this->service('XF:Attachment\Preparer');
		$associated = $inserter->associateAttachmentsWithContent($hash, 'ams_rating', $articleRating->rating_id);
	
		if ($associated)
		{
			$articleRating->fastUpdate('attach_count', $articleRating->attach_count + $associated);
		}
	}	

	protected function writeIpLog($ip)
	{
		$articleRating = $this->articleRating;

		/** @var \XF\Repository\IP $ipRepo */
		$ipRepo = $this->repository('XF:Ip');
		$ipEnt = $ipRepo->logIp($articleRating->user_id, $ip, 'ams_rating', $articleRating->rating_id);
		if ($ipEnt)
		{
			$articleRating->fastUpdate('ip_id', $ipEnt->ip_id);
		}
	}
}