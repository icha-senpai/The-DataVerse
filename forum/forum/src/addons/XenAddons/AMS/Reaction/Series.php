<?php

namespace XenAddons\AMS\Reaction;

use XF\Reaction\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Series extends AbstractHandler
{
	public function reactionsCounted(Entity $entity)
	{
		return true;
	}
}