<?php

namespace XenAddons\AMS\InlineMod;

use XF\InlineMod\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Series extends AbstractHandler
{
	public function getPossibleActions()
	{
		$actions = [];

		$actions['delete'] = $this->getActionHandler('XenAddons\AMS:Series\Delete');

		$actions['undelete'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_undelete_series'),
			'canUndelete',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\Series $entity */
				if ($entity->series_state == 'deleted')
				{
					$entity->series_state = 'visible';
					$entity->save();
				}
			}
		);

		$actions['approve'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_approve_series'),
			'canApproveUnapprove',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\Series $entity */
				if ($entity->series_state == 'moderated')
				{
					/** @var \XenAddons\AMS\Service\Series\Approve $approver */
					$approver = \XF::service('XenAddons\AMS:Series\Approve', $entity);
					$approver->setNotifyRunTime(1); // may be a lot happening
					$approver->approve();
				}
			}
		);

		$actions['unapprove'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_unapprove_series'),
			'canApproveUnapprove',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\Series $entity */
				if ($entity->series_state == 'visible')
				{
					$entity->series_state = 'moderated';
					$entity->save();
				}
			}
		);

		$actions['feature'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_feature_series'),
			'canFeatureUnfeature',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Service\Series\Feature $featurer */
				$featurer = $this->app->service('XenAddons\AMS:Series\Feature', $entity);
				$featurer->feature();
			}
		);

		$actions['unfeature'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_unfeature_series'),
			'canFeatureUnfeature',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Service\Series\Feature $featurer */
				$featurer = $this->app->service('XenAddons\AMS:Series\Feature', $entity);
				$featurer->unfeature();
			}
		);

		$actions['reassign'] = $this->getActionHandler('XenAddons\AMS:Series\Reassign');

		return $actions;
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return [];
	}
}