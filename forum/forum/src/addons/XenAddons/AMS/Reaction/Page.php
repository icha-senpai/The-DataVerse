<?php

namespace XenAddons\AMS\Reaction;

use XF\Reaction\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Page extends AbstractHandler
{
	public function reactionsCounted(Entity $entity)
	{
		return ($entity->page_state == 'visible');
	}
}