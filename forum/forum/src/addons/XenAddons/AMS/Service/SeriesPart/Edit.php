<?php

namespace XenAddons\AMS\Service\SeriesPart;

use XenAddons\AMS\Entity\SeriesPart;

class Edit extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\SeriesPart
	 */
	protected $seriesPart;
	
	/**
	 * @var \XenAddons\AMS\Service\Page\Preparer
	 */
	protected $seriesPartPreparer;

	public function __construct(\XF\App $app, SeriesPart $seriesPart)
	{
		parent::__construct($app);

		$this->seriesPart = $this->setUpSeriesPart($seriesPart);
	}

	protected function setUpSeriesPart(SeriesPart $seriesPart)
	{
		$this->seriesPart = $seriesPart;
		
		$this->seriesPartPreparer = $this->service('XenAddons\AMS:SeriesPart\Preparer', $this->seriesPart);		
		
		return $seriesPart;
	}

	public function getSeriesPart()
	{
		return $this->seriesPart;
	}
	
	public function setTitle($title)
	{
		$this->seriesPart->title = $title;
	}

	protected function _validate()
	{
		$seriesPart = $this->seriesPart;

		$seriesPart->preSave();
		$errors = $seriesPart->getErrors();

		return $errors;
	}

	protected function _save()
	{
		$seriesPart = $this->seriesPart;
		$visitor = \XF::visitor();
		
		$seriesPart->save(true, false);
		
		$this->seriesPartPreparer->afterUpdate();

		return $seriesPart;
	}
}