<?php

namespace XenAddons\AMS\Reaction;

use XF\Reaction\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Article extends AbstractHandler
{
	public function reactionsCounted(Entity $entity)
	{
		return ($entity->article_state == 'visible');
	}
}