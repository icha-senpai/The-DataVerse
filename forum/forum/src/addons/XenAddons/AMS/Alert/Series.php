<?php

namespace XenAddons\AMS\Alert;

use XF\Alert\AbstractHandler;

class Series extends AbstractHandler
{
	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return [];
	}

	public function getOptOutActions()
	{
		return [
			'insert',
			'reaction'
		];
	}

	public function getOptOutDisplayOrder()
	{
		return 5115;
	}
}