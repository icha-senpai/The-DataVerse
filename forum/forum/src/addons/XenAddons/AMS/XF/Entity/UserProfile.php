<?php

namespace XenAddons\AMS\XF\Entity;

use XF\Mvc\Entity\Structure;

class UserProfile extends XFCP_UserProfile
{

	public static function getStructure(Structure $structure)
	{
		$structure = parent::getStructure($structure);
		
		$structure->columns['xa_ams_about_author'] = ['type' => self::STR, 'maxLength' => 20000, 'default' => '',
			'verify' => 'verifyLongStringField',
			'censor' => true
		];
		$structure->columns['xa_ams_author_name'] = ['type' => self::STR, 'default' => '']; 
		
		return $structure;
	}
}