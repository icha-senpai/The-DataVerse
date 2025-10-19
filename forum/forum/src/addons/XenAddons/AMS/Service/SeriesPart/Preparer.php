<?php

namespace XenAddons\AMS\Service\SeriesPart;

use XenAddons\AMS\Entity\SeriesPart;

class Preparer extends \XF\Service\AbstractService
{
	/**
	 * @var SeriesPart
	 */
	protected $seriesPart;

	public function __construct(\XF\App $app, SeriesPart $seriesPart)
	{
		parent::__construct($app);
		$this->setSeriesPart($seriesPart);
	}
	
	public function setSeriesPart(SeriesPart $seriesPart)
	{
		$this->seriesPart = $seriesPart;
	}

	public function getSeriesPart()
	{
		return $this->seriesPart;
	}

	public function afterInsert()
	{
		$seriesPart = $this->seriesPart;
	}

	public function afterUpdate()
	{
		$seriesPart = $this->seriesPart;
	}

}