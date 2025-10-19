<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null article_read_id
 * @property int user_id
 * @property int article_id
 * @property int article_read_date
 * 
 * RELATIONS
 * @property \XF\Entity\User User
 * @property \XenAddons\AMS\Entity\ArticleItem ArticleItem
 */
class ArticleRead extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article_read';
		$structure->shortName = 'XenAddons\AMS:ArticleRead';
		$structure->primaryKey = 'article_read_id';
		$structure->columns = [
			'article_read_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'article_id' => ['type' => self::UINT, 'required' => true],
			'article_read_date' => ['type' => self::UINT, 'required' => true]
		];
		$structure->getters = [];
		$structure->relations = [
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
			'ArticleItem' => [
				'entity' => 'XenAddons\AMS:ArticleItem',
				'type' => self::TO_ONE,
				'conditions' => 'article_id',
				'primary' => true
			],
		];
		$structure->options = [];

		return $structure;
	}
}