<?php

namespace XenAddons\AMS\Tag;

use XF\Mvc\Entity\Entity;
use XF\Tag\AbstractHandler;

class ArticleItem extends AbstractHandler
{
	public function getPermissionsFromContext(Entity $entity)
	{
		if ($entity instanceof \XenAddons\AMS\Entity\ArticleItem)
		{
			$article = $entity;
			$category = $article->Category;
		}
		else if ($entity instanceof \XenAddons\AMS\Entity\Category)
		{
			$article = null;
			$category = $entity;
		}
		else
		{
			throw new \InvalidArgumentException("Entity must be an article or category");
		}

		$visitor = \XF::visitor();

		if ($article)
		{
			if (
				$article->isContributor()
				&& $article->hasPermission('manageOthersTagsOwnArt'))
			{
				$removeOthers = true;
			}
			else
			{
				$removeOthers = $article->hasPermission('manageAnyTag');
			}

			$edit = $article->canEditTags();
		}
		else
		{
			$removeOthers = false;
			$edit = $category->canEditTags();
		}

		return [
			'edit' => $edit,
			'removeOthers' => $removeOthers,
			'minTotal' => $category->min_tags
		];
	}

	public function getContentDate(Entity $entity)
	{
		return $entity->publish_date;
	}

	public function getContentVisibility(Entity $entity)
	{
		return $entity->article_state == 'visible';
	}

	public function getTemplateData(Entity $entity, array $options = [])
	{
		return [
			'article' => $entity,
			'options' => $options
		];
	}

	public function getEntityWith($forView = false)
	{
		$get = ['Category'];
		if ($forView)
		{
			$get[] = 'User';

			$visitor = \XF::visitor();
			$get[] = 'Category.Permissions|' . $visitor->permission_combination_id;
		}

		return $get;
	}

	public function canUseInlineModeration(Entity $entity, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
		return $entity->canUseInlineModeration($error);
	}
}