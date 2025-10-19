<?php

namespace XenAddons\AMS\InlineMod;

use XF\InlineMod\AbstractHandler;
use XF\Mvc\Entity\Entity;

class ArticleItem extends AbstractHandler
{
	public function getPossibleActions()
	{
		$actions = [];

		$actions['delete'] = $this->getActionHandler('XenAddons\AMS:ArticleItem\Delete');

		$actions['undelete'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_undelete_articles'),
			'canUndelete',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
				if ($entity->article_state == 'deleted')
				{
					$entity->article_state = 'visible';
					$entity->save();
				}
			}
		);

		$actions['approve'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_approve_articles'),
			'canApproveUnapprove',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
				if ($entity->article_state == 'moderated')
				{
					/** @var \XenAddons\AMS\Service\ArticleItem\Approve $approver */
					$approver = \XF::service('XenAddons\AMS:ArticleItem\Approve', $entity);
					$approver->setNotifyRunTime(1); // may be a lot happening
					$approver->approve();
				}
			}
		);

		$actions['unapprove'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_unapprove_articles'),
			'canApproveUnapprove',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
				if ($entity->article_state == 'visible')
				{
					$entity->article_state = 'moderated';
					$entity->save();
				}
			}
		);

		$actions['feature'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_feature_articles'),
			'canFeatureUnfeature',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Service\ArticleItem\Feature $featurer */
				$featurer = $this->app->service('XenAddons\AMS:ArticleItem\Feature', $entity);
				$featurer->feature();
			}
		);

		$actions['unfeature'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_unfeature_articles'),
			'canFeatureUnfeature',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Service\ArticleItem\Feature $featurer */
				$featurer = $this->app->service('XenAddons\AMS:ArticleItem\Feature', $entity);
				$featurer->unfeature();
			}
		);

		$actions['reassign'] = $this->getActionHandler('XenAddons\AMS:ArticleItem\Reassign');
		$actions['move'] = $this->getActionHandler('XenAddons\AMS:ArticleItem\Move');
		$actions['merge'] = $this->getActionHandler('XenAddons\AMS:ArticleItem\Merge');
		$actions['apply_prefix'] = $this->getActionHandler('XenAddons\AMS:ArticleItem\ApplyPrefix');

		return $actions;
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return ['Category', 'Category.Permissions|' . $visitor->permission_combination_id];
	}
}