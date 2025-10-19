<?php

namespace XenAddons\AMS\ContentVote;

use XF\ContentVote\AbstractHandler;
use XF\Mvc\Entity\Entity;

class ArticleRating extends AbstractHandler
{
	public function isCountedForContentUser(Entity $entity)
	{
		/** @var \XenAddons\AMS\Entity\ArticleRating $entity */

		if ($entity->is_anonymous)
		{
			return false;
		}

		return $entity->isVisible();
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();
		return ['Article', 'Article.Category', 'Article.Category.Permissions|' . $visitor->permission_combination_id];
	}
}