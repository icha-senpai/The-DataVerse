<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class Edit extends \XF\Service\AbstractService
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
	
	protected $oldMessage;
	
	protected $performValidations = true;
	
	protected $logDelay;
	protected $logEdit = true;
	protected $logHistory = true;
	
	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, SeriesItem $series)
	{
		parent::__construct($app);

		$this->series = $this->setUpSeries($series);
	}

	protected function setUpSeries(SeriesItem $series)
	{
		$this->series = $series;
		
		$this->seriesPreparer = $this->service('XenAddons\AMS:Series\Preparer', $this->series);		
		
		return $series;
	}

	public function getSeries()
	{
		return $this->series;
	}
	
	public function logDelay($logDelay)
	{
		$this->logDelay = $logDelay;
	}
	
	public function logEdit($logEdit)
	{
		$this->logEdit = $logEdit;
	}
	
	public function logHistory($logHistory)
	{
		$this->logHistory = $logHistory;
	}
	
	protected function setupEditHistory()
	{
		$series = $this->series;
	
		$series->edit_count++;
	
		$options = $this->app->options();
		if ($options->editLogDisplay['enabled'] && $this->logEdit)
		{
			$delay = is_null($this->logDelay) ? $options->editLogDisplay['delay'] * 60 : $this->logDelay;
			if ($series->create_date + $delay <= \XF::$time)
			{
				$series->last_edit_date = \XF::$time;
				$series->last_edit_user_id = \XF::visitor()->user_id;
			}
		}
	
		if ($options->editHistory['enabled'] && $this->logHistory)
		{
			$this->oldMessage = $series->message;
		}
	}
	
	public function getSeriesPreparer()
	{
		return $this->seriesPreparer;
	}
	
	public function setPerformValidations($perform)
	{
		$this->performValidations = (bool)$perform;
	}
	
	public function getPerformValidations()
	{
		return $this->performValidations;
	}
	
	public function setTitle($title)
	{
		$this->series->title = $title;
	}

	public function setMessage($message, $format = true)
	{
		if (!$this->series->isChanged('message'))
		{
			$this->setupEditHistory();
		}
		return $this->seriesPreparer->setMessage($message, $format);
	}
	
	public function setAttachmentHash($hash)
	{
		$this->seriesPreparer->setAttachmentHash($hash);
	}
	
	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}
	
	public function checkForSpam()
	{
		if ($this->series->series_state == 'visible' && \XF::visitor()->isSpamCheckRequired())
		{
			$this->seriesPreparer->checkForSpam();
		}
	}
	
	protected function finalSetup()
	{
	}

	protected function _validate()
	{
		$series = $this->series;

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
		$visitor = \XF::visitor();
		
		$db = $this->db();
		$db->beginTransaction();
		
		$series->save(true, false);
		
		$this->seriesPreparer->afterUpdate();
		
		if ($this->oldMessage)
		{
			/** @var \XF\Repository\EditHistory $repo */
			$repo = $this->repository('XF:EditHistory');
			$repo->insertEditHistory('ams_series', $series, $visitor, $this->oldMessage, $this->app->request()->getIp());
		}
		
		if ($series->isVisible() && $this->alert && $series->user_id != \XF::visitor()->user_id)
		{
			/** @var \XenAddons\AMS\Repository\Series $seriesRepo */
			$seriesRepo = $this->repository('XenAddons\AMS:Series');
			$seriesRepo->sendModeratorActionAlert($this->series, 'edit', $this->alertReason);
		}
		
		$db->commit();

		return $series;
	}
	
	public function sendNotifications()
	{
		if ($this->series->isVisible())
		{
			/** @var \XenAddons\AMS\Service\SeriesItem\Notify $notifier */
			$notifier = $this->service('XenAddons\AMS:SeriesItem\Notify', $this->series, 'update');
			$notifier->notifyAndEnqueue(3);
		}
	}
}