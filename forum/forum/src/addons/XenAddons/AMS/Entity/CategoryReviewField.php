<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\AbstractFieldMap;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int category_id
 * @property int field_id
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ReviewField Field  // TODO might need to change this from Field to ReviewField !!!  
 * @property \XenAddons\AMS\Entity\Category Category
 */
class CategoryReviewField extends AbstractFieldMap
{
	public static function getContainerKey()
	{
		return 'category_id';
	}

	public static function getStructure(Structure $structure)
	{
		self::setupDefaultStructure($structure, 'xf_xa_ams_category_review_field', 'XenAddons\AMS:CategoryReviewField', 'XenAddons\AMS:ReviewField');

		$structure->relations['Category'] = [
			'entity' => 'XenAddons\AMS:Category',
			'type' => self::TO_ONE,
			'conditions' => 'category_id',
			'primary' => true
		];

		return $structure;
	}
}