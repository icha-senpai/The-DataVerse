<?php

namespace XenAddons\AMS\XF\Pub\Controller;

class Account extends XFCP_Account
{
	protected function accountDetailsSaveProcess(\XF\Entity\User $visitor)
	{
		$form = parent::accountDetailsSaveProcess($visitor);
		
		$input = $this->filter([
			'profile' => [
				'xa_ams_about_author' => 'str',
				'xa_ams_author_name' => 'str',
			],
		]);
		
		$input['profile']['xa_ams_about_author'] = $this->plugin('XF:Editor')->fromInput('xa_ams_about_author');
		
		/** @var \XF\Entity\UserProfile $userProfile */
		$userProfile = $visitor->getRelationOrDefault('Profile');
		$form->setupEntityInput($userProfile, $input['profile']);
		
		return $form;
	}
}
