<?php

namespace XenAddons\AMS\Permission;

use XF\Mvc\Entity\Entity;
use XF\Permission\TreeContentPermissions;

class CategoryPermissions extends TreeContentPermissions
{
	public function getContentType()
	{
		return 'ams_category';
	}

	public function getAnalysisTypeTitle()
	{
		return \XF::phrase('xa_ams_article_category_permissions');
	}

	public function getContentTitle(Entity $entity)
	{
		return $entity->title;
	}

	public function isValidPermission(\XF\Entity\Permission $permission)
	{
		return ($permission->permission_group_id == 'xa_ams');  
	}

	public function getContentTree()
	{
		/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
		$categoryRepo = $this->builder->em()->getRepository('XenAddons\AMS:Category');
		return $categoryRepo->createCategoryTree($categoryRepo->findCategoryList()->fetch());
	}

	protected function getFinalPerms($contentId, array $calculated, array &$childPerms)
	{
		if (!isset($calculated['xa_ams']))
		{
			$calculated['xa_ams'] = [];
		}

		$final = $this->builder->finalizePermissionValues($calculated['xa_ams']);

		if (empty($final['view']))
		{
			$childPerms['xa_ams']['view'] = 'deny';
		}

		return $final;
	}

	protected function getFinalAnalysisPerms($contentId, array $calculated, array &$childPerms)
	{
		$final = $this->builder->finalizePermissionValues($calculated);

		if (empty($final['xa_ams']['view']))
		{
			$childPerms['xa_ams']['view'] = 'deny';
		}

		return $final;
	}
}