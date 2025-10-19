<?php

namespace XenAddons\AMS\Widget;

use XF\Widget\AbstractWidget;

class CategoryNav extends AbstractWidget
{
	public function render()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		if (!method_exists($visitor, 'canViewAmsArticles') || !$visitor->canViewAmsArticles())
		{
			return '';
		}

		$categoryRepo = $this->getCategoryRepo();
		$categories = $categoryRepo->getViewableCategories();
		$categoryTree = $categoryRepo->createCategoryTree($categories);
		$categoryExtras = $categoryRepo->getCategoryListExtras($categoryTree);

		$viewParams = [
			'categories' => $categories,
			'categoryTree' => $categoryTree,
			'categoryExtras' => $categoryExtras
		];
		
		return $this->renderer('xa_ams_widget_category_nav', $viewParams);
	}

	public function getOptionsTemplate()
	{
		return;
	}
	
	/**
	 * @return \XenAddons\AMS\Repository\Category
	 */
	protected function getCategoryRepo()
	{
		return $this->repository('XenAddons\AMS:Category');
	}
}