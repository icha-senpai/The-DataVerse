<?php

namespace XenAddons\AMS\Alert;

use XF\Alert\AbstractHandler;

class Comment extends AbstractHandler
{
	public function getOptOutActions()
	{
		return [
			'insert',
			'quote',
			'mention',
			'reaction'
		];
	}

	public function getOptOutDisplayOrder()
	{
		return 5113;
	}
}