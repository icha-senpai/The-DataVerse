<?php

namespace XenAddons\AMS\Service\SeriesPart;

use XenAddons\AMS\Entity\SeriesPart;

class Deleter extends \XF\Service\AbstractService
{
	/**
	 * @var SeriesPart
	 */
	protected $seriesPart;

	protected $user;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, SeriesPart $seriesPart)
	{
		parent::__construct($app);
		$this->setSeriesPart($seriesPart);
	}

	public function setSeriesPart(SeriesPart $seriesPart)
	{
		$this->seriesPart = $seriesPart;
	}
	
	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function getSeriesPart()
	{
		return $this->seriesPart;
	}

	public function setUser(\XF\Entity\User $user = null)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function delete()
	{
		$user = $this->user ?: \XF::visitor();
		
		$result = null;
		
		$result = $this->seriesPart->delete();
		
		return $result;
	}
}