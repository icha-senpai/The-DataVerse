<?php

namespace XenAddons\AMS\Like;

use XF\Like\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Comment extends AbstractHandler
{
	public function likesCounted(Entity $entity)
	{
		return ($entity->comment_state == 'visible');
	}
}