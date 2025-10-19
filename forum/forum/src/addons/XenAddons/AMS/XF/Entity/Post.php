<?php

namespace XenAddons\AMS\XF\Entity;

use XF\Mvc\Entity\Structure;

class Post extends XFCP_Post
{
	public function getBbCodeRenderOptions($context, $type)
	{
		$options = parent::getBbCodeRenderOptions($context, $type);
		$options['amsArticles'] =  $this->AmsArticles;
		$options['amsPages'] =  $this->AmsPages;
		$options['amsSeries'] =  $this->AmsSeries;

		return $options;
	}

	public function getAmsArticles()
	{
		return isset($this->_getterCache['AmsArticles']) ? $this->_getterCache['AmsArticles'] : null;
	}
	
	public function getAmsPages()
	{
		return isset($this->_getterCache['AmsPages']) ? $this->_getterCache['AmsPages'] : null;
	}
	
	public function getAmsSeries()
	{
		return isset($this->_getterCache['AmsSeries']) ? $this->_getterCache['AmsSeries'] : null;
	}

	public function setAmsArticles(array $amsArticles)
	{
		$this->_getterCache['AmsArticles'] = $amsArticles;
	}
	
	public function setAmsPages(array $amsPages)
	{
		$this->_getterCache['AmsPages'] = $amsPages;
	}
	
	public function setAmsSeries(array $AmsSeries)
	{
		$this->_getterCache['AmsSeries'] = $AmsSeries;
	}

	public static function getStructure(Structure $structure)
	{
		$structure = parent::getStructure($structure);

		$structure->getters['AmsArticles'] = true;
		$structure->getters['AmsPages'] = true;
		$structure->getters['AmsSeries'] = true;

		return $structure;
	}
}