<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class Deleter extends \XF\Service\AbstractService
{
	/**
	 * @var SeriesItem
	 */
	protected $series;

	/**
	 * @var \XF\Entity\User|null
	 */
	protected $user;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, SeriesItem $series)
	{
		parent::__construct($app);
		$this->setSeries($series);
	}

	public function setSeries(SeriesItem $series)
	{
		$this->series = $series;
	}
	
	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function getSeries()
	{
		return $this->series;
	}

	public function setUser(\XF\Entity\User $user = null)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function delete($type, $reason = '')
	{
		$user = $this->user ?: \XF::visitor();
		$wasVisible = $this->series->isVisible();
		
		if ($type == 'soft')
		{
			$result = $this->series->softDelete($reason, $user);
		}
		else
		{
			$result = $this->series->delete();
		}
		
		if ($result && $wasVisible && $this->alert && $this->series->user_id != $user->user_id)
		{
			/** @var \XenAddons\AMS\Repository\Series $seriesRepo */
			$seriesRepo = $this->repository('XenAddons\AMS:Series');
			$seriesRepo->sendModeratorActionAlert($this->series, 'delete', $this->alertReason);
		}
		
		return $result;
	}
}