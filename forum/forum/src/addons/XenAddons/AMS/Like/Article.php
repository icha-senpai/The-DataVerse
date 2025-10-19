<?php

namespace XenAddons\AMS\Like;

use XF\Like\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Article extends AbstractHandler
{
	public function likesCounted(Entity $entity)
	{
		return ($entity->article_state == 'visible');
	}
}