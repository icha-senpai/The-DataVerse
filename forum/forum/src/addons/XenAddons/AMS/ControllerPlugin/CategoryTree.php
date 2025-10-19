<?php

namespace XenAddons\AMS\ControllerPlugin;

use XF\ControllerPlugin\AbstractCategoryTree;

class CategoryTree extends AbstractCategoryTree
{
	protected $viewFormatter = 'XenAddons\AMS:Category\%s';
	protected $templateFormatter = 'xa_ams_category_%s';
	protected $routePrefix = 'xa-ams/categories';
	protected $entityIdentifier = 'XenAddons\AMS:Category';
	protected $primaryKey = 'category_id';
}