<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class Preparer extends \XF\Service\AbstractService
{
	/**
	 * @var SeriesItem
	 */
	protected $seriesItem;
	
	protected $attachmentHash;
	
	protected $logIp = false;

	public function __construct(\XF\App $app, SeriesItem $seriesItem)
	{
		parent::__construct($app);
		$this->setSeriesItem($seriesItem);
	}
	
	public function setSeriesItem(SeriesItem $seriesItem)
	{
		$this->seriesItem = $seriesItem;
	}

	public function getSeriesItem()
	{
		return $this->seriesItem;
	}
	
	public function logIp($logIp)
	{
		$this->logIp = $logIp;
	}

	public function setMessage($message, $format = true, $checkValidity = true)
	{
		$preparer = $this->getMessagePreparer($format);
		$this->seriesItem->message = $preparer->prepare($message, $checkValidity);
		$this->seriesItem->embed_metadata = $preparer->getEmbedMetadata();
	
		return $preparer->pushEntityErrorIfInvalid($this->seriesItem);
	}
	
	/**
	 * @param bool $format
	 *
	 * @return \XF\Service\Message\Preparer
	 */
	protected function getMessagePreparer($format = true)
	{
		$options = $this->app->options();
	
		if ($options->messageMaxLength && $options->xaAmsSeriesDetailsMaxLength)
		{
			$ratio = ceil($options->xaAmsSeriesDetailsMaxLength / $options->messageMaxLength);
			$maxImages = $options->messageMaxImages * $ratio;
			$maxMedia = $options->messageMaxMedia * $ratio;
		}
		else
		{
			$maxImages = 100;
			$maxMedia = 30;
		}
	
		/** @var \XF\Service\Message\Preparer $preparer */
		$preparer = $this->service('XF:Message\Preparer', 'ams_series', $this->seriesItem);
		$preparer->setConstraint('maxLength', $options->xaAmsSeriesDetailsMaxLength);
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
		$seriesItem = $this->seriesItem;
	
		/** @var \XF\Entity\User $user */
		$user = $seriesItem->User ?: $this->repository('XF:User')->getGuestUser($seriesItem->username);
	
		$message = $seriesItem->title . "\n" . $seriesItem->message;
	
		$checker = $this->app->spam()->contentChecker();
		$checker->check($user, $message, [
			'permalink' => $this->app->router('public')->buildLink('canonical:ams/series', $seriesItem),
			'content_type' => 'ams_series'
		]);
	
		$decision = $checker->getFinalDecision();
		switch ($decision)
		{
			case 'moderated':
				$seriesItem->series_state = 'moderated';
				break;
	
			case 'denied':
				$checker->logSpamTrigger('ams_series', null);
				$seriesItem->error(\XF::phrase('your_content_cannot_be_submitted_try_later'));
				break;
		}
	}
	
	public function validateFiles(&$error = null)
	{
		$seriesItem = $this->seriesItem;
	
		/*if ($this->attachmentHash && $this->app->options()->xaAmsSeriesRequireCoverImage)
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
		}*/
	
		return true;
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
		
		$seriesItem = $this->seriesItem;
		
		$checker = $this->app->spam()->contentChecker();
		
		$checker->logContentSpamCheck('ams_series', $seriesItem->series_id);
		$checker->logSpamTrigger('ams_series', $seriesItem->series_id);
	}

	public function afterUpdate()
	{
		if ($this->attachmentHash)
		{
			$this->associateAttachments($this->attachmentHash);
		}
		
		$seriesItem = $this->seriesItem;
		
		$checker = $this->app->spam()->contentChecker();
		
		$checker->logSpamTrigger('ams_series', $seriesItem->series_id);
	}
	
	protected function associateAttachments($hash)
	{
		$seriesItem = $this->seriesItem;
	
		/** @var \XF\Service\Attachment\Preparer $inserter */
		$inserter = $this->service('XF:Attachment\Preparer');
		$associated = $inserter->associateAttachmentsWithContent($hash, 'ams_series', $seriesItem->series_id);
	
		if ($associated)
		{
			$seriesItem->fastUpdate('attach_count', $seriesItem->attach_count + $associated);
		}
	}
	
	protected function writeIpLog($ip)
	{
		$seriesItem = $this->seriesItem;
	
		/** @var \XF\Repository\IP $ipRepo */
		$ipRepo = $this->repository('XF:Ip');
		$ipEnt = $ipRepo->logIp($seriesItem->user_id, $ip, 'ams_series', $seriesItem->series_id);
		if ($ipEnt)
		{
			$seriesItem->fastUpdate('ip_id', $ipEnt->ip_id);
		}
	}	
}