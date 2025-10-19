<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null comment_read_id
 * @property int user_id
 * @property int article_id
 * @property int comment_read_date
 */
class CommentRead extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_comment_read';
		$structure->shortName = 'XenAddons\AMS:CommentRead';
		$structure->primaryKey = 'comment_read_id';
		$structure->columns = [
			'comment_read_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'article_id' => ['type' => self::UINT, 'required' => true],
			'comment_read_date' => ['type' => self::UINT, 'required' => true]
		];
		$structure->getters = [];
		$structure->relations = [];
		$structure->options = [];

		return $structure;
	}
}