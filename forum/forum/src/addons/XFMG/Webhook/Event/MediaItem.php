<?php

namespace XFMG\Webhook\Event;

use XF\Webhook\Event\AbstractHandler;

class MediaItem extends AbstractHandler
{
	public function getDisplayOrder(): int
	{
		return 310;
	}
}
