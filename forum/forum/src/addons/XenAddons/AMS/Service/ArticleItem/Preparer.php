<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class Preparer extends \XF\Service\AbstractService
{
	/**
	 * @var ArticleItem
	 */
	protected $articleItem;

	protected $attachmentHash;

	protected $logIp = true;

	protected $mentionedUsers = [];

	public function __construct(\XF\App $app, ArticleItem $articleItem)
	{
		parent::__construct($app);
		$this->setArticleItem($articleItem);
	}
	
	public function setArticleItem(ArticleItem $articleItem)
	{
		$this->articleItem = $articleItem;
	}

	public function getArticleItem()
	{
		return $this->articleItem;
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
			$user = $this->articleItem->User ?: $this->repository('XF:User')->getGuestUser();
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
		$this->articleItem->message = $preparer->prepare($message, $checkValidity);
		$this->articleItem->embed_metadata = $preparer->getEmbedMetadata();
		$this->mentionedUsers = $preparer->getMentionedUsers();

		return $preparer->pushEntityErrorIfInvalid($this->articleItem);
	}

	/**
	 * @param bool $format
	 *
	 * @return \XF\Service\Message\Preparer
	 */
	protected function getMessagePreparer($format = true)
	{
		$options = $this->app->options();

		if ($options->messageMaxLength && $options->xaAmsArticleMaxLength)
		{
			$ratio = ceil($options->xaAmsArticleMaxLength / $options->messageMaxLength);
			$maxImages = $options->messageMaxImages * $ratio;
			$maxMedia = $options->messageMaxMedia * $ratio;
		}
		else
		{
			$maxImages = 100;
			$maxMedia = 30;
		}

		/** @var \XF\Service\Message\Preparer $preparer */
		$preparer = $this->service('XF:Message\Preparer', 'ams_article', $this->articleItem);
		$preparer->setConstraint('maxLength', $options->xaAmsArticleMaxLength);
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
		$articleItem = $this->articleItem;

		/** @var \XF\Entity\User $user */
		$user = $articleItem->User ?: $this->repository('XF:User')->getGuestUser($articleItem->username);

		$message = $articleItem->title . "\n" . $articleItem->message;

		$checker = $this->app->spam()->contentChecker();
		$checker->check($user, $message, [
			'permalink' => $this->app->router('public')->buildLink('canonical:ams', $articleItem),
			'content_type' => 'ams_article'
		]);

		$decision = $checker->getFinalDecision();
		switch ($decision)
		{
			case 'moderated':
				$articleItem->article_state = 'moderated';
				break;

			case 'denied':
				$checker->logSpamTrigger('ams_article', null);
				$articleItem->error(\XF::phrase('your_content_cannot_be_submitted_try_later'));
				break;
		}
	}

	public function validateFiles(&$error = null)
	{
		$articleItem = $this->articleItem;
		$category = $articleItem->Category;
		if (!$category)
		{
			throw new \LogicException("Could not find category for article");
		}
		
		if ($this->attachmentHash && $category->require_article_image)
		{
			$totalImageAttachments = $this->finder('XF:Attachment')
				->with('Data', true)
				->where('temp_hash', $this->attachmentHash)
				->where('Data.width', '>', 0)
				->total();
			
			if (!$totalImageAttachments)
			{
				$error = \XF::phrase('xa_ams_you_must_upload_at_least_one_image_attachment');
				return false;
			}
		}
	
		return true;
	}
	
	public function afterInsert()
	{
		if ($this->attachmentHash)
		{
			$this->associateAttachments($this->attachmentHash);
		}
		
		$this->updateCoverImageIfNeeded();

		if ($this->logIp)
		{
			$ip = ($this->logIp === true ? $this->app->request()->getIp() : $this->logIp);
			$this->writeIpLog($ip);
		}

		$articleItem = $this->articleItem;
		$checker = $this->app->spam()->contentChecker();

		$checker->logContentSpamCheck('ams_article', $articleItem->article_id);
		$checker->logSpamTrigger('ams_article', $articleItem->article_id);

	}

	public function afterUpdate()
	{
		if ($this->attachmentHash)
		{
			$this->associateAttachments($this->attachmentHash);
		}

		$this->updateCoverImageIfNeeded();
		
		$articleItem = $this->articleItem;
		$checker = $this->app->spam()->contentChecker();

		$checker->logSpamTrigger('ams_article', $articleItem->article_id);
	}
	
	protected function associateAttachments($hash)
	{
		$articleItem = $this->articleItem;
	
		/** @var \XF\Service\Attachment\Preparer $inserter */
		$inserter = $this->service('XF:Attachment\Preparer');
		$associated = $inserter->associateAttachmentsWithContent($hash, 'ams_article', $articleItem->article_id);
	
		if ($associated)
		{
			$articleItem->fastUpdate('attach_count', $articleItem->attach_count + $associated);
		}
	}	
	
	protected function updateCoverImageIfNeeded()
	{
		$articleItem = $this->articleItem;
		$attachments = $this->articleItem->Attachments;
	
		$imageAttachments = [];
		$fileAttachments = [];
	
		foreach ($attachments AS $key => $attachment)
		{
			if ($attachment['thumbnail_url'])
			{
				$imageAttachments[$key] = $attachment;
			}
			else
			{
				$fileAttachments[$key] = $attachment;
			}
		}
	
		if (!$this->articleItem->cover_image_id) 
		{
			// Things to do if no cover image id is set
			
			if ($imageAttachments)
			{
				foreach ($imageAttachments AS $imageAttachment)
				{
					$coverImageId = $imageAttachment['attachment_id'];
					break;
				}
	
				if ($coverImageId)
				{
					$articleItem->fastUpdate('cover_image_id', $coverImageId);
				}
			}
		}
		elseif ($this->articleItem->cover_image_id) 
		{
			// Things to check/do if a cover image id is set
			
			if (!$imageAttachments) 
			{
				// if there are no longer any image attachments, then there can't be a cover image, so set the cover image id to 0
				
				$articleItem->fastUpdate('cover_image_id',0);
			}
			elseif (array_key_exists($this->articleItem->cover_image_id, $imageAttachments))
			{
				// do nothing as the cover image exists. 
			}
			else
			{
				// if it gets to this point, lets set the first attachment as the cover image id since the old cover image has been removed!
				
				foreach ($imageAttachments AS $imageAttachment)
				{
					$coverImageId = $imageAttachment['attachment_id'];
					break;
				}
				
				if ($coverImageId)
				{
					$articleItem->fastUpdate('cover_image_id', $coverImageId);
				}				
			}
		}
	}	

	protected function writeIpLog($ip)
	{
		$articleItem = $this->articleItem;

		/** @var \XF\Repository\IP $ipRepo */
		$ipRepo = $this->repository('XF:Ip');
		$ipEnt = $ipRepo->logIp($articleItem->user_id, $ip, 'ams_article', $articleItem->article_id);
		if ($ipEnt)
		{
			$articleItem->fastUpdate('ip_id', $ipEnt->ip_id);
		}
	}
}