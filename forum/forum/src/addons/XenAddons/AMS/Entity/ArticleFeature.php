<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int article_id
 * @property int feature_date
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ArticleItem Article
 */
class ArticleFeature extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article_feature';
		$structure->shortName = 'XenAddons\AMS:ArticleFeature';
		$structure->primaryKey = 'article_id';
		$structure->columns = [
			'article_id' => ['type' => self::UINT, 'required' => true],
			'feature_date' => ['type' => self::UINT, 'default' => \XF::$time]
		];
		$structure->getters = [];
		$structure->relations = [
			'Article' => [
				'entity' => 'XenAddons\AMS:ArticleItem',
				'type' => self::TO_ONE,
				'conditions' => 'article_id',
				'primary' => true
			]
		];

		return $structure;
	}
}