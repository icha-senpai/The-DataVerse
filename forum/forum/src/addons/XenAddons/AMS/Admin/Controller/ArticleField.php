<?php

namespace XenAddons\AMS\Admin\Controller;

use XF\Admin\Controller\AbstractField;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class ArticleField extends AbstractField
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertAdminPermission('articleManagementSystem');
	}

	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ArticleField';
	}

	protected function getLinkPrefix()
	{
		return 'xa-ams/article-fields';
	}

	protected function getTemplatePrefix()
	{
		return 'xa_ams_article_field';
	}

	protected function fieldAddEditResponse(\XF\Entity\AbstractField $field)
	{
		$reply = parent::fieldAddEditResponse($field);

		if ($reply instanceof \XF\Mvc\Reply\View)
		{
			/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
			$categoryRepo = $this->repository('XenAddons\AMS:Category');

			$categories = $categoryRepo->findCategoryList()->fetch();
			$categoryTree = $categoryRepo->createCategoryTree($categories);

			/** @var \XF\Mvc\Entity\ArrayCollection $fieldAssociations */
			$fieldAssociations = $field->getRelationOrDefault('CategoryFields', false);

			$reply->setParams([
				'categoryTree' => $categoryTree,
				'categoryIds' => $fieldAssociations->pluckNamed('category_id')
			]);
		}

		return $reply;
	}

	protected function saveAdditionalData(FormAction $form, \XF\Entity\AbstractField $field)
	{
		$additionalOptions = $this->filter([
			'hide_title' => 'bool',
			'display_on_list' => 'bool',
			'display_on_tab' => 'bool',
			'display_on_tab_field_id' => 'int'
		]);
		
		$form->setup(function() use ($field, $additionalOptions)
		{
			$field->bulkSet($additionalOptions);
		});
		
		$categoryIds = $this->filter('category_ids', 'array-uint');

		/** @var \XenAddons\AMS\Entity\ArticleField $field */
		$form->complete(function() use ($field, $categoryIds)
		{
			/** @var \XenAddons\AMS\Repository\CategoryField $repo */
			$repo = $this->repository('XenAddons\AMS:CategoryField');
			$repo->updateFieldAssociations($field, $categoryIds);
		});

		return $form;
	}
}