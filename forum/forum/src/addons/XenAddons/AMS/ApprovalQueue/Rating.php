<?php

namespace XenAddons\AMS\ApprovalQueue;

use XF\ApprovalQueue\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Rating extends AbstractHandler
{
	protected function canActionContent(Entity $content, &$error = null)
	{
		/** @var $content \XenAddons\AMS\Entity\ArticleRating */
		return $content->canApproveUnapprove($error);
	}

	public function getEntityWith()
	{
		return ['Article', 'User'];
	}

	public function actionApprove(\XenAddons\AMS\Entity\ArticleRating $rating)
	{
		$this->quickUpdate($rating, 'rating_state', 'visible');
	}

	public function actionDelete(\XenAddons\AMS\Entity\ArticleRating $rating)
	{
		$this->quickUpdate($rating, 'rating_state', 'deleted');
	}
}