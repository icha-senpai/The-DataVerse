<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class Reassign extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\SeriesItem
	 */
	protected $series;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, SeriesItem $series)
	{
		parent::__construct($app);
		$this->series = $series;
	}

	public function getSeries()
	{
		return $this->series;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function reassignTo(\XF\Entity\User $newUser)
	{
		$series = $this->series;
		$oldUser = $series->User;
		$reassigned = ($series->user_id != $newUser->user_id);

		$series->user_id = $newUser->user_id;
		//$series->username = $newUser->username;  // TODO current the series record does not store the username.  Maybe add this in the future? 
		$series->save();

		if ($reassigned && $this->alert)
		{
			if ($oldUser && \XF::visitor()->user_id != $oldUser->user_id)
			{
				/** @var \XenAddons\AMS\Repository\Series $seriesRepo */
				$seriesRepo = $this->repository('XenAddons\AMS:Series');
				$seriesRepo->sendModeratorActionAlert(
					$this->series, 'reassign_from', $this->alertReason, ['to' => $newUser->username], $oldUser
				);
			}

			if (\XF::visitor()->user_id != $newUser->user_id)
			{
				/** @var \XenAddons\AMS\Repository\Series $seriesRepo */
				$seriesRepo = $this->repository('XenAddons\AMS:Series');
				$seriesRepo->sendModeratorActionAlert(
					$this->series, 'reassign_to', $this->alertReason, [], $newUser
				);
			}
		}

		return $reassigned;
	}
}