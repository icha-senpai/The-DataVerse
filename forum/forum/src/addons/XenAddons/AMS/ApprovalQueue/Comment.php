<?php

namespace XenAddons\AMS\ApprovalQueue;

use XF\ApprovalQueue\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Comment extends AbstractHandler
{
	protected function canActionContent(Entity $content, &$error = null)
	{
		/** @var $content \XenAddons\AMS\Entity\Comment */
		return $content->canApproveUnapprove($error);
	}

	public function getEntityWith()
	{
		return ['Article', 'User'];
	}

	public function actionApprove(\XenAddons\AMS\Entity\Comment $comment)
	{
		$this->quickUpdate($comment, 'comment_state', 'visible');
	}

	public function actionDelete(\XenAddons\AMS\Entity\Comment $comment)
	{
		$this->quickUpdate($comment, 'comment_state', 'deleted');
	}
}