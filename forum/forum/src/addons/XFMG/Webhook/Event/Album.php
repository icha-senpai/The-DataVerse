<?php

namespace XFMG\Webhook\Event;

use XF\Webhook\Event\AbstractHandler;

class Album extends AbstractHandler
{
	public function getDisplayOrder(): int
	{
		return 320;
	}
}
