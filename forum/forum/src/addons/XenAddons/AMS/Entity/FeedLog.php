<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int feed_id
 * @property string unique_id
 * @property string hash
 * @property int article_id
 */
class FeedLog extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_feed_log';
		$structure->shortName = 'XenAddons\AMS:FeedLog';
		$structure->primaryKey = ['feed_id', 'unique_id'];
		$structure->columns = [
			'feed_id' => ['type' => self::UINT, 'required' => true],
			'unique_id' => ['type' => self::STR, 'maxLength' => 250, 'required' => true],
			'hash' => ['type' => self::STR, 'required' => true],
			'article_id' => ['type' => self::UINT, 'default' => 0]
		];
		$structure->getters = [];
		$structure->relations = [];

		return $structure;
	}
}