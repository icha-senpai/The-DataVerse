<?php

namespace XenAddons\AMS\Alert;

use XF\Alert\AbstractHandler;

class Article extends AbstractHandler
{
	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return ['Category', 'Category.Permissions|' . $visitor->permission_combination_id];
	}

	public function getOptOutActions()
	{
		return [
			'insert',
			'mention',
			'reaction'
		];
	}

	public function getOptOutDisplayOrder()
	{
		return 5111;
	}
}