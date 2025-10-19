<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int article_id
 * @property string field_id
 * @property string field_value
 */
class ArticleFieldValue extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article_field_value';
		$structure->shortName = 'XenAddons\AMS:ArticleFieldValue';
		$structure->primaryKey = ['article_id', 'field_id'];
		$structure->columns = [
			'article_id' => ['type' => self::UINT, 'required' => true],
			'field_id' => ['type' => self::STR, 'maxLength' => 25,
				'match' => 'alphanumeric'
			],
			'field_value' => ['type' => self::STR, 'default' => '']
		];
		$structure->getters = [];
		$structure->relations = [];

		return $structure;
	}
}