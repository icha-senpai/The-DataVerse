<?php

namespace XenAddons\AMS\Service\Page;

use XenAddons\AMS\Entity\ArticlePage;

class Preparer extends \XF\Service\AbstractService
{
	/**
	 * @var ArticlePage
	 */
	protected $articlePage;
	
	protected $attachmentHash;
	
	protected $logIp = true;

	public function __construct(\XF\App $app, ArticlePage $articlePage)
	{
		parent::__construct($app);
		$this->setArticlePage($articlePage);
	}
	
	public function setArticlePage(ArticlePage $articlePage)
	{
		$this->articlePage = $articlePage;
	}

	public function getArticlePage()
	{
		return $this->articlePage;
	}
	
	public function logIp($logIp)
	{
		$this->logIp = $logIp;
	}
	
	public function setMessage($message, $format = true, $checkValidity = true)
	{
		$preparer = $this->getMessagePreparer($format);
		$this->articlePage->message = $preparer->prepare($message, $checkValidity);
		$this->articlePage->embed_metadata = $preparer->getEmbedMetadata();
	
		return $preparer->pushEntityErrorIfInvalid($this->articlePage);
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
		$preparer = $this->service('XF:Message\Preparer', 'ams_page', $this->articlePage);
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
		// TODO implement this at some point for pages! 
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
		
		$articlePage = $this->articlePage;
	}

	public function afterUpdate()
	{
		if ($this->attachmentHash)
		{
			$this->associateAttachments($this->attachmentHash);
		}
		
		$this->updateCoverImageIfNeeded();
		
		$articlePage = $this->articlePage;
	}
	
	protected function associateAttachments($hash)
	{
		$articlePage = $this->articlePage;
	
		/** @var \XF\Service\Attachment\Preparer $inserter */
		$inserter = $this->service('XF:Attachment\Preparer');
		$associated = $inserter->associateAttachmentsWithContent($hash, 'ams_page', $articlePage->page_id);
	
		if ($associated)
		{
			$articlePage->fastUpdate('attach_count', $articlePage->attach_count + $associated);
		}
	}
	
	protected function updateCoverImageIfNeeded()
	{
		$articlePage = $this->articlePage;
		$attachments = $this->articlePage->Attachments;
	
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
	
		if (!$this->articlePage->cover_image_id)
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
					$articlePage->fastUpdate('cover_image_id', $coverImageId);
				}
			}
		}
		elseif ($this->articlePage->cover_image_id)
		{
			// Things to check/do if a cover image id is set
				
			if (!$imageAttachments)
			{
				// if there are no longer any image attachments, then there can't be a cover image, so set the cover image id to 0
	
				$articlePage->fastUpdate('cover_image_id',0);
			}
			elseif (array_key_exists($this->articlePage->cover_image_id, $imageAttachments))
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
					$articlePage->fastUpdate('cover_image_id', $coverImageId);
				}
			}
		}
	}	
	
	protected function writeIpLog($ip)
	{
		$articlePage = $this->articlePage;
		
		$articleItem = $this->articlePage->Article;
	
		/** @var \XF\Repository\IP $ipRepo */
		$ipRepo = $this->repository('XF:Ip');
		$ipEnt = $ipRepo->logIp($articleItem->user_id, $ip, 'ams_page', $articlePage->page_id);
		if ($ipEnt)
		{
			$articlePage->fastUpdate('ip_id', $ipEnt->ip_id);
		}
	}	
}