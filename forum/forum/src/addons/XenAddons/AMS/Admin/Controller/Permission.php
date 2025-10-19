<?php

namespace XenAddons\AMS\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class Permission extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertAdminPermission('articleManagementSystem');
	}

	/**
	 * @return \XenAddons\AMS\ControllerPlugin\CategoryPermission
	 */
	protected function getCategoryPermissionPlugin()
	{
		/** @var \XenAddons\AMS\ControllerPlugin\CategoryPermission $plugin */
		$plugin = $this->plugin('XenAddons\AMS:CategoryPermission');
		$plugin->setFormatters('XenAddons\AMS\Permission\Category%s', 'xa_ams_permission_category_%s');
		$plugin->setRoutePrefix('permissions/xa-ams-categories');

		return $plugin;
	}

	public function actionCategory(ParameterBag $params)
	{
		if ($params->category_id)
		{
			return $this->getCategoryPermissionPlugin()->actionList($params);
		}
		else
		{
			$categoryRepo = $this->repository('XenAddons\AMS:Category');
			$categories = $categoryRepo->findCategoryList()->fetch();
			$categoryTree = $categoryRepo->createCategoryTree($categories);

			$customPermissions = $this->repository('XF:PermissionEntry')->getContentWithCustomPermissions('ams_category');

			$viewParams = [
				'categoryTree' => $categoryTree,
				'customPermissions' => $customPermissions
			];
			return $this->view('XenAddons\AMS:Permission\CategoryOverview', 'xa_ams_permission_category_overview', $viewParams);
		}
	}

	public function actionCategoryEdit(ParameterBag $params)
	{
		return $this->getCategoryPermissionPlugin()->actionEdit($params);
	}

	public function actionCategorySave(ParameterBag $params)
	{
		return $this->getCategoryPermissionPlugin()->actionSave($params);
	}
}