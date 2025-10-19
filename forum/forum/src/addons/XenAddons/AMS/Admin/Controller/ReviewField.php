<?php

namespace XenAddons\AMS\Admin\Controller;

use XF\Admin\Controller\AbstractField;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class ReviewField extends AbstractField
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertAdminPermission('articleManagementSystem');
	}

	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ReviewField';
	}

	protected function getLinkPrefix()
	{
		return 'xa-ams/review-fields';
	}

	protected function getTemplatePrefix()
	{
		return 'xa_ams_review_field';
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
			$fieldAssociations = $field->getRelationOrDefault('CategoryReviewFields', false);

			$reply->setParams([
				'categoryTree' => $categoryTree,
				'categoryIds' => $fieldAssociations->pluckNamed('category_id')
			]);
		}

		return $reply;
	}

	protected function saveAdditionalData(FormAction $form, \XF\Entity\AbstractField $field)
	{
		$categoryIds = $this->filter('category_ids', 'array-uint');

		/** @var \XenAddons\AMS\Entity\ReviewField $field */
		$form->complete(function() use ($field, $categoryIds)
		{
			/** @var \XenAddons\AMS\Repository\CategoryReviewField $repo */
			$repo = $this->repository('XenAddons\AMS:CategoryReviewField');
			$repo->updateFieldAssociations($field, $categoryIds);
		});

		return $form;
	}
}