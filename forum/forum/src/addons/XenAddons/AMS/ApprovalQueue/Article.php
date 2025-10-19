<?php

namespace XenAddons\AMS\ApprovalQueue;

use XF\ApprovalQueue\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Article extends AbstractHandler
{
	protected function canActionContent(Entity $content, &$error = null)
	{
		/** @var $content \XenAddons\AMS\Entity\ArticleItem */
		return $content->canApproveUnapprove($error);
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return ['Category', 'Category.Permissions|' . $visitor->permission_combination_id, 'User'];
	}

	public function actionApprove(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		/** @var \XenAddons\AMS\Service\ArticleItem\Approve $approver */
		$approver = \XF::service('XenAddons\AMS:ArticleItem\Approve', $article);
		$approver->setNotifyRunTime(1); // may be a lot happening
		$approver->approve();
	}

	public function actionDelete(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		$this->quickUpdate($article, 'article_state', 'deleted');
	}
}