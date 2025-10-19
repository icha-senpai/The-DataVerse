<?php

namespace XenAddons\AMS\Attachment;

use XF\Attachment\AbstractHandler;
use XF\Entity\Attachment;
use XF\Mvc\Entity\Entity;

class Comment extends AbstractHandler
{
	public function getContainerWith()
	{
		$visitor = \XF::visitor();
		
		return ['Article', 'Article.Category', 'Article.Category.Permissions|' . $visitor->permission_combination_id];
	}

	public function canView(Attachment $attachment, Entity $container, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\Comment $container */
		if (!$container->canView())
		{
			return false;
		}
		
		return $container->canViewCommentImages();
	}

	public function canManageAttachments(array $context, &$error = null)
	{
		$category = $this->getCategoryFromContext($context);

		return $category&& $category->canUploadAndManageCommentImages();
	}

	public function onAttachmentDelete(Attachment $attachment, Entity $container = null)
	{
		if (!$container)
		{
			return;
		}

		/** @var \XenAddons\AMS\Entity\Comment $container */
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
		if ($category && $category->canUploadCommentVideos())
		{
			$constraints = $attachRepo->applyVideoAttachmentConstraints($constraints);
		}
		
		return $constraints;
	}

	public function getContainerIdFromContext(array $context)
	{
		return isset($context['comment_id']) ? intval($context['comment_id']) : null;
	}

	public function getContainerLink(Entity $container, array $extraParams = [])
	{
		return \XF::app()->router('public')->buildLink('ams/comments', $container, $extraParams);
	}

	public function getContext(Entity $entity = null, array $extraContext = [])
	{
		if ($entity instanceof \XenAddons\AMS\Entity\Comment)
		{
			$extraContext['comment_id'] = $entity->comment_id;
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
			throw new \InvalidArgumentException("Entity must be comment, article or category");
		}

		return $extraContext;
	}
	
	protected function getCategoryFromContext(array $context)
	{
		$em = \XF::em();
		
		if (!empty($context['comment_id']))
		{
			/** @var \XenAddons\AMS\Entity\Comment $comment */
			$comment = $em->find('XenAddons\AMS:Comment', intval($context['comment_id']), ['Article']);
			if (!$comment || !$comment->canView() || !$comment->canEdit())
			{
				return null;
			}
		
			$category = $comment->Article->Category;
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