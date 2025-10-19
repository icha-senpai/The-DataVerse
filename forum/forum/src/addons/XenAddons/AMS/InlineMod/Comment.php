<?php

namespace XenAddons\AMS\InlineMod;

use XF\InlineMod\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Comment extends AbstractHandler
{
	public function getPossibleActions()
	{
		$actions = [];

		$actions['delete'] = $this->getActionHandler('XenAddons\AMS:Comment\Delete');

		$actions['undelete'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_undelete_comments'),
			'canUndelete',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\Comment $entity */
				if ($entity->comment_state == 'deleted')
				{
					$entity->comment_state = 'visible';
					$entity->save();
				}
			}
		);

		$actions['approve'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_approve_comments'),
			'canApproveUnapprove',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\Comment $entity */
				if ($entity->comment_state == 'moderated')
				{
					$entity->comment_state = 'visible';
					$entity->save();
				}
			}
		);

		$actions['unapprove'] = $this->getSimpleActionHandler(
			\XF::phrase('xa_ams_unapprove_comments'),
			'canApproveUnapprove',
			function(Entity $entity)
			{
				/** @var \XenAddons\AMS\Entity\Comment $entity */
				if ($entity->comment_state == 'visible')
				{
					$entity->comment_state = 'moderated';
					$entity->save();
				}
			}
		);
		
		$actions['merge'] = $this->getActionHandler('XenAddons\AMS:Comment\Merge');

		return $actions;
	}

	public function getEntityWith()
	{
		return 'User';
	}
}