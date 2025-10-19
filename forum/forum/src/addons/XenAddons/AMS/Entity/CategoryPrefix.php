<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\AbstractPrefixMap;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int category_id
 * @property int prefix_id
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ArticlePrefix Prefix
 * @property \XenAddons\AMS\Entity\Category Category
 */
class CategoryPrefix extends AbstractPrefixMap
{
	public static function getContainerKey()
	{
		return 'category_id';
	}

	public static function getStructure(Structure $structure)
	{
		self::setupDefaultStructure($structure, 'xf_xa_ams_category_prefix', 'XenAddons\AMS:CategoryPrefix', 'XenAddons\AMS:ArticlePrefix');

		$structure->relations['Category'] = [
			'entity' => 'XenAddons\AMS:Category',
			'type' => self::TO_ONE,
			'conditions' => 'category_id',
			'primary' => true
		];

		return $structure;
	}
}