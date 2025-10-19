<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class Create extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\SeriesItem
	 */
	protected $series;
	
	/**
	 * @var \XenAddons\AMS\Service\Series\Preparer
	 */
	protected $seriesPreparer;
	
	protected $performValidations = true;

	public function __construct(\XF\App $app, SeriesItem $series)
	{
		parent::__construct($app);
		
		$this->series = $this->setUpSeries($series);
	}

	protected function setUpSeries(SeriesItem $series)
	{
		$this->series = $series;
		
		$this->seriesPreparer = $this->service('XenAddons\AMS:Series\Preparer', $this->series);		
		
		$visitor = \XF::visitor();
		$this->series->user_id = $visitor->user_id;
		$this->series->username = $visitor->username;
		
		$this->series->series_state = $this->series->getNewSeriesState();
		
		return $series;
	}

	public function getSeries()
	{
		return $this->series;
	}
	
	public function setPerformValidations($perform)
	{
		$this->performValidations = (bool)$perform;
	}
	
	public function getPerformValidations()
	{
		return $this->performValidations;
	}
	
	public function setIsAutomated()
	{
		$this->logIp(false);
		$this->setPerformValidations(false);
	}
	
	public function setTitle($title)
	{
		$this->series->set('title', $title,
			['forceConstraint' => $this->performValidations ? false : true]
		);
	}
	
	public function setMessage($message, $format = true)
	{
		$this->seriesPreparer->setMessage($message, $format, $this->performValidations);
	}
	
	public function setSeriesState($seriesState)
	{
		$this->series->series_state = $seriesState;
	}
	
	public function setSeriesAttachmentHash($hash)
	{
		$this->seriesPreparer->setAttachmentHash($hash);
	}
	
	public function setDescription($description)
	{
		$this->series->description = $description;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}
	
	public function logIp($logIp)
	{
		$this->seriesPreparer->logIp($logIp);
	}
	
	public function checkForSpam()
	{
		if (\XF::visitor()->isSpamCheckRequired())
		{
			$this->seriesPreparer->checkForSpam();
		}
	}
	
	protected function finalSetup()
	{
	}

	protected function _validate()
	{
		$this->finalSetup();

		$series = $this->series;
		
		if (!$series->user_id)
		{
			/** @var \XF\Validator\Username $validator */
			$validator = $this->app->validator('Username');
			$series->username = $validator->coerceValue($series->username);
		
			if ($this->performValidations && !$validator->isValid($series->username, $error))
			{
				return [
					$validator->getPrintableErrorValue($error)
				];
			}
		}

		$series->preSave();
		$errors = $series->getErrors();

		if ($this->performValidations)
		{
		}

		return $errors;
	}

	protected function _save()
	{
		$series = $this->series;
		
		$db = $this->db();
		$db->beginTransaction();

		$series->save(true, false);

		$this->seriesPreparer->afterInsert();

		$db->commit();

		return $series;
	}
	
	public function sendNotifications()
	{
		if ($this->series->isVisible())
		{
			/** @var \XenAddons\UBS\Service\Series\Notify $notifier */
			//$notifier = $this->service('XenAddons\AMS:Series\Notify', $this->series, 'series');
			//$notifier->notifyAndEnqueue(3);
		}
	}
}