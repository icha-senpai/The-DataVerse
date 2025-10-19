<?php

namespace XFMG\Webhook\Event;

use XF\Webhook\Event\AbstractHandler;

class Comment extends AbstractHandler
{
	public function getDisplayOrder(): int
	{
		return 330;
	}
}
