<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int user_id
 * @property int article_id
 * @property bool email_subscribe
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ArticleItem Article
 * @property \XF\Entity\User User
 */
class ArticleWatch extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article_watch';
		$structure->shortName = 'XenAddons\AMS:ArticleWatch';
		$structure->primaryKey = ['user_id', 'article_id'];
		$structure->columns = [
			'user_id' => ['type' => self::UINT, 'required' => true],
			'article_id' => ['type' => self::UINT, 'required' => true],
			'email_subscribe' => ['type' => self::BOOL, 'default' => false]
		];
		$structure->getters = [];
		$structure->relations = [
			'Article' => [
				'entity' => 'XenAddons\AMS:ArticleItem',
				'type' => self::TO_ONE,
				'conditions' => 'article_id',
				'primary' => true
			],
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
		];

		return $structure;
	}
}