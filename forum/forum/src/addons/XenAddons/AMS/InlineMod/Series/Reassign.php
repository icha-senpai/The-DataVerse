<?php

namespace XenAddons\AMS\InlineMod\Series;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;

class Reassign extends AbstractAction
{
	protected $targetUser;
	protected $targetUserId;

	public function getTitle()
	{
		return \XF::phrase('xa_ams_reassign_series...');
	}
	
	protected function canApplyInternal(AbstractCollection $entities, array $options, &$error)
	{
		$result = parent::canApplyInternal($entities, $options, $error);
		
		if ($result)
		{
			if ($options['confirmed'] && !$options['target_user_id'])
			{
				$error = \XF::phrase('requested_user_not_found');
				return false;
			}
		}
		
		return $result;
	}

	protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\Series $entity */
		return $entity->canReassign($error);
	}

	protected function applyToEntity(Entity $entity, array $options)
	{
		$user = $this->getTargetUser($options['target_user_id']);
		if (!$user)
		{
			throw new \InvalidArgumentException("No target specified");
		}

		/** @var \XenAddons\AMS\Service\Series\Reassign $reassigner */
		$reassigner = $this->app()->service('XenAddons\AMS:Series\Reassign', $entity);

		if ($options['alert'])
		{
			$reassigner->setSendAlert(true, $options['alert_reason']);
		}

		$reassigner->reassignTo($user);
	}

	public function getBaseOptions()
	{
		return [
			'target_user_id' => 0,
			'confirmed' => false,
			'alert' => false,
			'alert_reason' => ''
		];
	}

	public function renderForm(AbstractCollection $entities, \XF\Mvc\Controller $controller)
	{
		$viewParams = [
			'seriesItems' => $entities,
			'total' => count($entities)
		];
		return $controller->view('XenAddons\AMS:Public:InlineMod\Series\Reassign', 'xa_ams_inline_mod_series_reassign', $viewParams);
	}

	public function getFormOptions(AbstractCollection $entities, Request $request)
	{
		$username = $request->filter('username', 'str');
		$user = $this->app()->em()->findOne('XF:User', ['username' => $username]);

		$options = [
			'target_user_id' => $user ? $user->user_id : 0,
			'confirmed' => true,
			'alert' => $request->filter('alert', 'bool'),
			'alert_reason' => $request->filter('alert_reason', 'str')
		];

		return $options;
	}

	/**
	 * @param integer $userId
	 * 
	 * @return null|\XF\Entity\User
	 */
	protected function getTargetUser($userId)
	{
		$userId = intval($userId);

		if ($this->targetUserId && $this->targetUserId == $userId)
		{
			return $this->targetUser;
		}
		if (!$userId)
		{
			return null;
		}

		$user = $this->app()->em()->find('XF:User', $userId);
		if (!$user)
		{
			throw new \InvalidArgumentException("Invalid target user ($userId)");
		}

		$this->targetUserId = $userId;
		$this->targetUser = $user;

		return $this->targetUser;
	}
}