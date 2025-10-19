<?php

namespace XenAddons\AMS\ApprovalQueue;

use XF\ApprovalQueue\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Series extends AbstractHandler
{
	protected function canActionContent(Entity $content, &$error = null)
	{
		/** @var $content \XenAddons\AMS\Entity\SeriesItem */
		return $content->canApproveUnapprove($error);
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return ['User'];
	}

	public function actionApprove(\XenAddons\AMS\Entity\SeriesItem $series)
	{
		/** @var \XenAddons\AMS\Service\Series\Approve $approver */
		$approver = \XF::service('XenAddons\AMS:Series\Approve', $series);
		$approver->setNotifyRunTime(1); // may be a lot happening
		$approver->approve();
	}

	public function actionDelete(\XenAddons\AMS\Entity\SeriesItem $series)
	{
		$this->quickUpdate($series, 'series_state', 'deleted');
	}
}