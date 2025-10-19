<?php

namespace XenAddons\AMS\Alert;

use XF\Alert\AbstractHandler;

class Page extends AbstractHandler
{
	public function getOptOutActions()
	{
		return [
			'insert',
			'reaction'
		];
	}

	public function getOptOutDisplayOrder()
	{
		return 5112;
	}
}