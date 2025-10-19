<?php

namespace XenAddons\AMS\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class Category extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertAdminPermission('articleManagementSystem');
	}

	/**
	 * @return \XenAddons\AMS\ControllerPlugin\CategoryTree
	 */
	protected function getCategoryTreePlugin()
	{
		return $this->plugin('XenAddons\AMS:CategoryTree');
	}

	public function actionIndex()
	{
		return $this->getCategoryTreePlugin()->actionList([
			'permissionContentType' => 'ams_category'
		]);
	}

	public function categoryAddEdit(\XenAddons\AMS\Entity\Category $category)
	{
		$categoryRepo = $this->getCategoryRepo();
		$categories = $categoryRepo->findCategoryList()->fetch();
		$categoryTree = $categoryRepo->createCategoryTree($categories);

		if ($category->thread_prefix_id && $category->ThreadForum)
		{
			$threadPrefixes = $category->ThreadForum->getPrefixesGrouped();
		}
		else
		{
			$threadPrefixes = [];
		}

		/** @var \XenAddons\AMS\Repository\ArticlePrefix $prefixRepo */
		$prefixRepo = $this->repository('XenAddons\AMS:ArticlePrefix');
		$availablePrefixes = $prefixRepo->findPrefixesForList()->fetch();
		$availablePrefixes = $availablePrefixes->pluckNamed('title', 'prefix_id');
		$prefixListData = $prefixRepo->getPrefixListData();
		
		/** @var \XenAddons\AMS\Repository\ArticleField $fieldRepo */
		$fieldRepo = $this->repository('XenAddons\AMS:ArticleField');
		$availableFields = $fieldRepo->findFieldsForList()->fetch();
		$availableFields = $availableFields->pluckNamed('title', 'field_id');
		
		/** @var \XenAddons\AMS\Repository\ReviewField $reviewfieldRepo */
		$reviewfieldRepo = $this->repository('XenAddons\AMS:ReviewField');
		$availableReviewFields = $reviewfieldRepo->findFieldsForList()->fetch();
		$availableReviewFields = $availableReviewFields->pluckNamed('title', 'field_id');
		
		/** @var \XF\Repository\Style $styleRepo */
		$styleRepo = $this->repository('XF:Style');
		$styleTree = $styleRepo->getStyleTree(false);
		
		$viewParams = [
			'forumOptions' => $this->repository('XF:Forum')->getForumOptionsData(false, 'discussion'),
			'threadPrefixes' => $threadPrefixes,
			'category' => $category,
			'categoryTree' => $categoryTree,

			'availableFields' => $availableFields,
			'availableReviewFields' => $availableReviewFields,
			'availablePrefixes' => $availablePrefixes,
			
			'prefixGroups' => $prefixListData['prefixGroups'],
			'prefixesGrouped' => $prefixListData['prefixesGrouped'],
			
			'styleTree' => $styleTree,
		];
		return $this->view('XenAddons\AMS:Category\Edit', 'xa_ams_category_edit', $viewParams);
	}

	public function actionEdit(ParameterBag $params)
	{
		$category = $this->assertCategoryExists($params->category_id);
		
		return $this->categoryAddEdit($category);
	}

	public function actionAdd()
	{
		$category = $this->em()->create('XenAddons\AMS:Category');
		$category->parent_category_id = $this->filter('parent_category_id', 'uint');

		if ($this->filter('clone_category_id', 'uint'))
		{
			if ($clonedCategory = $this->assertCategoryExists($this->filter('clone_category_id', 'uint')))
			{
				$category->display_order = $clonedCategory->display_order;
				$category->min_tags = $clonedCategory->min_tags;
				$category->default_tags = $clonedCategory->default_tags;
				$category->allow_articles = $clonedCategory->allow_articles;				
				$category->require_article_image = $clonedCategory->require_article_image;				
				$category->allow_contributors = $clonedCategory->allow_contributors;
				$category->allow_original_source = $clonedCategory->allow_original_source;
				$category->require_original_source = $clonedCategory->require_original_source;
				$category->allow_poll = $clonedCategory->allow_poll;
				$category->allow_location = $clonedCategory->allow_location;
				$category->allow_comments = $clonedCategory->allow_comments;				
				$category->allow_ratings = $clonedCategory->allow_ratings;
				$category->require_review = $clonedCategory->require_review;
				$category->allow_pros_cons = $clonedCategory->allow_pros_cons;
				$category->allow_anon_reviews = $clonedCategory->allow_anon_reviews;
				$category->review_voting = $clonedCategory->review_voting;
				$category->allow_author_rating = $clonedCategory->allow_author_rating;
				$category->thread_node_id = $clonedCategory->thread_node_id;
				$category->thread_prefix_id = $clonedCategory->thread_prefix_id;
				$category->thread_set_article_tags = $clonedCategory->thread_set_article_tags;
				$category->article_list_order = $clonedCategory->article_list_order;				
				$category->layout_type = $clonedCategory->layout_type;
				$category->style_id = $clonedCategory->style_id;
				$category->field_cache = $clonedCategory->field_cache;
				$category->review_field_cache = $clonedCategory->review_field_cache;
				$category->prefix_cache = $clonedCategory->prefix_cache;
				$category->default_prefix_id = $clonedCategory->default_prefix_id;
				$category->require_prefix = $clonedCategory->require_prefix;
				$category->expand_category_nav = $clonedCategory->expand_category_nav;
			}
		}
		
		return $this->categoryAddEdit($category);
	}

	protected function categorySaveProcess(\XenAddons\AMS\Entity\Category $category)
	{
		$entityInput = $this->filter([
			'title' => 'str',
			'description' => 'str',
			'parent_category_id' => 'uint',
			'display_order' => 'uint',
			'min_tags' => 'uint',
			'default_tags' => 'str',	
			'thread_node_id' => 'uint',
			'thread_prefix_id' => 'uint',
			'thread_set_article_tags' => 'bool',
			'require_prefix' => 'bool',
			'default_prefix_id' => 'uint',
			'style_id' => 'uint',
			'content_image' => 'str',
			'content_title' => 'str',
			'content_message' => 'str',
			'content_term' => 'str',
			'allow_articles' => 'bool',
			'allow_contributors' => 'bool',
			'require_article_image' => 'bool',
			'allow_comments' => 'bool',
			'allow_ratings' => 'bool',
			'review_voting' => 'str',
			'require_review' => 'bool',
			'allow_pros_cons' => 'bool',
			'allow_anon_reviews' => 'bool',
			'allow_author_rating' => 'bool',
			'allow_location' => 'bool',
			'allow_poll' => 'bool',
			'allow_original_source' => 'bool',
			'require_original_source' => 'bool',
			'layout_type' => 'str',
			'article_list_order' => 'str',
			'expand_category_nav' => 'bool',
		]);

		$form = $this->formAction();
		$form->basicEntitySave($category, $entityInput);

		$prefixIds = $this->filter('available_prefixes', 'array-uint');
		$form->complete(function() use ($category, $prefixIds)
		{
			/** @var \XenAddons\AMS\Repository\CategoryPrefix $repo */
			$repo = $this->repository('XenAddons\AMS:CategoryPrefix');
			$repo->updateContentAssociations($category->category_id, $prefixIds);
		});
		
		if (!in_array($category->default_prefix_id, $prefixIds))
		{
			$category->default_prefix_id = 0;
		}

		$fieldIds = $this->filter('available_fields', 'array-str');
		$form->complete(function() use ($category, $fieldIds)
		{
			/** @var \XenAddons\AMS\Repository\CategoryField $repo */
			$repo = $this->repository('XenAddons\AMS:CategoryField');
			$repo->updateContentAssociations($category->category_id, $fieldIds);
		});
		
		$reviewfieldIds = $this->filter('available_review_fields', 'array-str');
		$form->complete(function() use ($category, $reviewfieldIds)
		{
			/** @var \XenAddons\AMS\Repository\CategoryReviewField $repo */
			$repo = $this->repository('XenAddons\AMS:CategoryReviewField');
			$repo->updateContentAssociations($category->category_id, $reviewfieldIds);
		});

		return $form;
	}

	public function actionSave(ParameterBag $params)
	{
		$this->assertPostOnly();

		if ($params->category_id)
		{
			$category = $this->assertCategoryExists($params->category_id);
		}
		else
		{
			$category = $this->em()->create('XenAddons\AMS:Category');
		}

		$this->categorySaveProcess($category)->run();

		return $this->redirect(
			$this->buildLink('xa-ams/categories') . $this->buildLinkHash($category->getEntityId())
		);
	}

	public function actionDelete(ParameterBag $params)
	{
		return $this->getCategoryTreePlugin()->actionDelete($params);
	}

	public function actionSort()
	{
		return $this->getCategoryTreePlugin()->actionSort();
	}

	/**
	 * @return \XenAddons\AMS\ControllerPlugin\CategoryPermission
	 */
	protected function getCategoryPermissionPlugin()
	{
		/** @var \XenAddons\AMS\ControllerPlugin\CategoryPermission $plugin */
		$plugin = $this->plugin('XenAddons\AMS:CategoryPermission');
		$plugin->setFormatters('XenAddons\AMS:Category\Permission%s', 'xa_ams_category_permission_%s');
		$plugin->setRoutePrefix('xa-ams/categories/permissions');

		return $plugin;
	}

	public function actionPermissions(ParameterBag $params)
	{
		return $this->getCategoryPermissionPlugin()->actionList($params);
	}

	public function actionPermissionsEdit(ParameterBag $params)
	{
		return $this->getCategoryPermissionPlugin()->actionEdit($params);
	}

	public function actionPermissionsSave(ParameterBag $params)
	{
		return $this->getCategoryPermissionPlugin()->actionSave($params);
	}

	/**
	 * @param string $id
	 * @param array|string|null $with
	 * @param null|string $phraseKey
	 *
	 * @return \XenAddons\AMS\Entity\Category
	 */
	protected function assertCategoryExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists('XenAddons\AMS:Category', $id, $with, $phraseKey);
	}

	/**
	 * @return \XenAddons\AMS\Repository\Category
	 */
	protected function getCategoryRepo()
	{
		return $this->repository('XenAddons\AMS:Category');
	}
}