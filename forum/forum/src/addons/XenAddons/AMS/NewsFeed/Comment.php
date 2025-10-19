<?php

namespace XenAddons\AMS\NewsFeed;

use XF\Mvc\Entity\Entity;
use XF\NewsFeed\AbstractHandler;

class Comment extends AbstractHandler
{
	public function isPublishable(Entity $entity, $action)
	{
		if ($action == 'insert')
		{

		}

		return true;
	}

	public function getEntityWith()
	{
		return ['User'];
	}
}