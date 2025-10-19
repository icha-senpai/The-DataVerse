<?php

namespace XenAddons\AMS\Like;

use XF\Like\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Rating extends AbstractHandler
{
	public function likesCounted(Entity $entity)
	{
		return ($entity->rating_state == 'visible');
	}
}