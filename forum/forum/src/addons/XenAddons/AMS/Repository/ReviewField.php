<?php

namespace XenAddons\AMS\Repository;

use XF\Repository\AbstractField;

class ReviewField extends AbstractField
{
	protected function getRegistryKey()
	{
		return 'xa_amsReviewFields';
	}

	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ReviewField';
	}

	public function getDisplayGroups()
	{
		return [
			'top' => \XF::phrase('xa_ams_top_below_rating'),
			'middle' => \XF::phrase('xa_ams_middle_above_review'),
			'bottom' => \XF::phrase('xa_ams_bottom_below_review'),
			'self_place' => \XF::phrase('xa_ams_self_placement'),
		];
	}
}