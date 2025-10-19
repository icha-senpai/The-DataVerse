<?php

namespace XenAddons\AMS\Admin\Controller;

use XF\Admin\Controller\AbstractPrefix;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\ParameterBag;
use XF\Mvc\FormAction;

class ArticlePrefix extends AbstractPrefix
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertAdminPermission('articleManagementSystem');
	}

	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ArticlePrefix';
	}

	protected function getLinkPrefix()
	{
		return 'xa-ams/prefixes';
	}

	protected function getTemplatePrefix()
	{
		return 'xa_ams_article_prefix';
	}

	protected function getCategoryParams(\XenAddons\AMS\Entity\ArticlePrefix $prefix)
	{
		/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
		$categoryRepo = \XF::repository('XenAddons\AMS:Category');
		$categoryTree = $categoryRepo->createCategoryTree($categoryRepo->findCategoryList()->fetch());

		return [
			'categoryTree' => $categoryTree,
		];
	}

	protected function prefixAddEditResponse(\XF\Entity\AbstractPrefix $prefix)
	{
		$reply = parent::prefixAddEditResponse($prefix);

		if ($reply instanceof \XF\Mvc\Reply\View)
		{
			$reply->setParams($this->getCategoryParams($prefix));
		}

		return $reply;
	}

	protected function quickSetAdditionalData(FormAction $form, ArrayCollection $prefixes)
	{
		$input = $this->filter([
			'apply_article_category_ids' => 'bool',
			'category_ids' => 'array-uint'
		]);

		if ($input['apply_article_category_ids'])
		{
			$form->complete(function() use($prefixes, $input)
			{
				$mapRepo = $this->getCategoryPrefixRepo();

				foreach ($prefixes AS $prefix)
				{
					$mapRepo->updatePrefixAssociations($prefix, $input['category_ids']);
				}
			});
		}

		return $form;
	}

	public function actionQuickSet()
	{
		$reply = parent::actionQuickSet();

		if ($reply instanceof \XF\Mvc\Reply\View)
		{
			if ($reply->getTemplateName() == $this->getTemplatePrefix() . '_quickset_editor')
			{
				$reply->setParams($this->getCategoryParams($reply->getParam('prefix')));
			}
		}

		return $reply;
	}

	protected function saveAdditionalData(FormAction $form, \XF\Entity\AbstractPrefix $prefix)
	{
		$categoryIds = $this->filter('category_ids', 'array-uint');

		$form->complete(function() use($prefix, $categoryIds)
		{
			$this->getCategoryPrefixRepo()->updatePrefixAssociations($prefix, $categoryIds);
		});

		return $form;
	}
	
	public function actionPrefixes(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		$categoryId = $this->filter('val', 'uint');
	
		/** @var \XenAddons\AMS\Entity\Category $category */
		$category = $this->em()->find('XenAddons\AMS:Category', $categoryId,
			'Permissions|' . \XF::visitor()->permission_combination_id
		);
		if (!$category)
		{
			return $this->notFound(\XF::phrase('requested_category_not_found'));
		}
	
		if (!$category->canView($error))
		{
			return $this->noPermission($error);
		}
	
		$viewParams = [
			'category' => $category,
			'prefixes' => $category->getUsablePrefixes()
		];
	
		return $this->view('XenAddons\AMS:Category\Prefixes', 'public:xa_ams_category_prefixes', $viewParams);
	}

	/**
	 * @return \XenAddons\AMS\Repository\CategoryPrefix
	 */
	protected function getCategoryPrefixRepo()
	{
		return $this->repository('XenAddons\AMS:CategoryPrefix');
	}
}