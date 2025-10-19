<?php

namespace XenAddons\AMS\Repository;

use XF\Repository\AbstractField;

class ArticleField extends AbstractField
{
	protected function getRegistryKey()
	{
		return 'xa_amsArticleFields';
	}

	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ArticleField';
	}

	public function getDisplayGroups()
	{
		return [
			'header' => \XF::phrase('xa_ams_article_header'),
			'above_article' => \XF::phrase('xa_ams_above_article'),
			'below_article' => \XF::phrase('xa_ams_below_article'),
			'sidebar' => \XF::phrase('xa_ams_sidebar_block'),
			'new_tab' => \XF::phrase('xa_ams_own_tab'),
			'self_place' => \XF::phrase('xa_ams_self_placement'),
		];
	}
}