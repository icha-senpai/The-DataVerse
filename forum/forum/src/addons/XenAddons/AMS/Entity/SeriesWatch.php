<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int user_id
 * @property int series_id
 * @property string notify_on
 * @property bool send_alert
 * @property bool send_email
 * 
 * RELATIONS
 * @property \XenAddons\AMS\Entity\SeriesItem Series
 * @property \XF\Entity\User User
 */
class SeriesWatch extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_series_watch';
		$structure->shortName = 'XenAddons\AMS:SeriesWatch';
		$structure->primaryKey = ['user_id', 'series_id'];
		$structure->columns = [
			'user_id' => ['type' => self::UINT, 'required' => true],
			'series_id' => ['type' => self::UINT, 'required' => true],
			'notify_on' => ['type' => self::STR, 'default' => '',
				'allowedValues' => ['', 'series_part']
			],
			'send_alert' => ['type' => self::BOOL, 'default' => false],
			'send_email' => ['type' => self::BOOL, 'default' => false]
		];
		$structure->getters = [];
		$structure->relations = [
			'Series' => [
				'entity' => 'XenAddons\AMS:SeriesItem',
				'type' => self::TO_ONE,
				'conditions' => 'series_id',
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