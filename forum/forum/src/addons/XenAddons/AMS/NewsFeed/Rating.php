<?php

namespace XenAddons\AMS\NewsFeed;

use XF\Mvc\Entity\Entity;
use XF\NewsFeed\AbstractHandler;

class Rating extends AbstractHandler
{
	public function isPublishable(Entity $entity, $action)
	{
		/** @var \XenAddons\AMS\Entity\ArticleRating $entity */
		if (!$entity->is_review)
		{
			return false;
		}

		return true;
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return ['Article', 'Article.User', 'Article.Category', 'Article.Category.Permissions|' . $visitor->permission_combination_id];
	}
}