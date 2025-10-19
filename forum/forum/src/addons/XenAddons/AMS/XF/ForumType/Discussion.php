<?php

namespace XenAddons\AMS\XF\ForumType;

use XF\Entity\Forum;

class Discussion extends XFCP_Discussion
{
	public function getExtraAllowedThreadTypes(Forum $forum): array
	{
		$allowed = parent::getExtraAllowedThreadTypes($forum);
		$allowed[] = 'ams_article';

		return $allowed;
	}

	public function getCreatableThreadTypes(Forum $forum): array
	{
		$creatable = parent::getCreatableThreadTypes($forum);
		$this->removeAmsArticleTypeFromList($creatable);

		return $creatable;
	}

	public function getFilterableThreadTypes(Forum $forum): array
	{
		$filterable = parent::getFilterableThreadTypes($forum);

		$amsArticleTarget = \XF::db()->fetchOne("
			SELECT 1
			FROM xf_xa_ams_category
			WHERE thread_node_id = ?
			LIMIT 1
		", $forum->node_id);
		if (!$amsArticleTarget)
		{
			$this->removeAmsArticleTypeFromList($filterable);
		}

		return $filterable;
	}

	protected function removeAmsArticleTypeFromList(array &$list)
	{
		$amsArticleKey = array_search('ams_article', $list);
		if ($amsArticleKey !== false)
		{
			unset($list[$amsArticleKey]);
		}
	}
}