<?php

namespace XenAddons\AMS\XF\Admin\Controller;

class User extends XFCP_User
{
	protected function userSaveProcess(\XF\Entity\User $user)
	{
		$form = parent::userSaveProcess($user);
		
		$input = $this->filter([
			'profile' => [
				'xa_ams_about_author' => 'str',
				'xa_ams_author_name' => 'str',
			],
		]);
		
		/** @var \XF\Entity\UserProfile $userProfile */
		$userProfile = $user->getRelationOrDefault('Profile');
		$userProfile->setOption('admin_edit', true);
		$form->setupEntityInput($userProfile, $input['profile']);
		
		return $form;
	}
}