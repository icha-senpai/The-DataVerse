<?php

namespace XenAddons\AMS\Attachment;

use XF\Attachment\AbstractHandler;
use XF\Entity\Attachment;
use XF\Mvc\Entity\Entity;

class Rating extends AbstractHandler
{
	public function getContainerWith()
	{
		$visitor = \XF::visitor();
		
		return ['Article', 'Article.Category', 'Article.Category.Permissions|' . $visitor->permission_combination_id];
	}

	public function canView(Attachment $attachment, Entity $container, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\ArticleRating $container */
		if (!$container->canView())
		{
			return false;
		}
		
		return $container->canViewReviewImages();
	}

	public function canManageAttachments(array $context, &$error = null)
	{
		$category = $this->getCategoryFromContext($context);
		
		return $category->canUploadAndManageReviewImages();
	}

	public function onAttachmentDelete(Attachment $attachment, Entity $container = null)
	{
		if (!$container)
		{
			return;
		}

		/** @var \XenAddons\AMS\Entity\ArticleRating $container */
		$container->attach_count--;
		$container->save();
	}

	public function getConstraints(array $context)
	{
		/** @var \XF\Repository\Attachment $attachRepo */
		$attachRepo = \XF::repository('XF:Attachment');
		
		$constraints = \XF::repository('XF:Attachment')->getDefaultAttachmentConstraints();
		$constraints['extensions'] = ['jpg', 'jpeg', 'jpe', 'png', 'gif'];

		$category = $this->getCategoryFromContext($context);
		if ($category && $category->canUploadReviewVideos())
		{
			$constraints = $attachRepo->applyVideoAttachmentConstraints($constraints);
		}
		
		return $constraints;
	}

	public function getContainerIdFromContext(array $context)
	{
		return isset($context['rating_id']) ? intval($context['rating_id']) : null;
	}

	public function getContainerLink(Entity $container, array $extraParams = [])
	{
		return \XF::app()->router('public')->buildLink('ams/review', $container, $extraParams);
	}

	public function getContext(Entity $entity = null, array $extraContext = [])
	{
		if ($entity instanceof \XenAddons\AMS\Entity\ArticleRating)
		{
			$extraContext['rating_id'] = $entity->rating_id;
		}
		else if ($entity instanceof \XenAddons\AMS\Entity\ArticleItem)
		{
			$extraContext['article_id'] = $entity->article_id;
		}
		else if ($entity instanceof \XenAddons\AMS\Entity\Category)
		{
			$extraContext['category_id'] = $entity->category_id;
		}
		else
		{
			throw new \InvalidArgumentException("Entity must be rating, article or category");
		}

		return $extraContext;
	}
	
	protected function getCategoryFromContext(array $context)
	{
		$em = \XF::em();
		
		if (!empty($context['rating_id']))
		{
			/** @var \XenAddons\AMS\Entity\ArticleRating $rating */
			$rating = $em->find('XenAddons\AMS:ArticleRating', intval($context['rating_id']), ['Article']);
			if (!$rating || !$rating->canView() || !$rating->canEdit())
			{
				return null;
			}
		
			$category = $rating->Article->Category;
		}
		else if (!empty($context['article_id']))
		{
			/** @var \XenAddons\AMS\Entity\ArticleItem $article */
			$article = $em->find('XenAddons\AMS:ArticleItem', intval($context['article_id']));
			if (!$article || !$article->canView())
			{
				return null;
			}
				
			$category = $article->Category;
		}
		else if (!empty($context['category_id']))
		{
			/** @var \XenAddons\AMS\Entity\Category $category */
			$category = $em->find('XenAddons\AMS:Category', intval($context['category_id']));
			if (!$category || !$category->canView())
			{
				return null;
			}
		}
		else
		{
			return null;
		}

		return $category;
	}
}