<?php

namespace XenAddons\AMS\ControllerPlugin;

use XF\ControllerPlugin\AbstractPermission;

class CategoryPermission extends AbstractPermission
{
	protected $viewFormatter = 'XenAddons\AMS:Permission\Category%s';
	protected $templateFormatter = 'xa_ams_permission_category_%s';
	protected $routePrefix = 'permissions/xa-ams-categories';
	protected $contentType = 'ams_category';
	protected $entityIdentifier = 'XenAddons\AMS:Category';
	protected $primaryKey = 'category_id';
	protected $privatePermissionGroupId = 'xa_ams';
	protected $privatePermissionId = 'view';
}