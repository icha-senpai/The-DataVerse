<?php

namespace XenAddons\AMS\Warning;

use XF\Entity\Warning;
use XF\Mvc\Entity\Entity;
use XF\Warning\AbstractHandler;

class Page extends AbstractHandler
{
	public function getStoredTitle(Entity $entity)
	{
		return $entity->title;
		
		return $entity->Article ? $entity->Article->title : '';
	}

	public function getDisplayTitle($title)
	{
		return \XF::phrase('xa_ams_article_page_x', ['title' => $title]);
	}

	public function getContentForConversation(Entity $entity)
	{
		return $entity->message;
	}

	public function getContentUrl(Entity $entity, $canonical = false)
	{
		return \XF::app()->router('public')->buildLink(($canonical ? 'canonical:' : '') . 'ams/page', $entity);
	}

	public function getContentUser(Entity $entity)
	{
		return $entity->User;
	}

	public function canViewContent(Entity $entity, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
		return $entity->canView();
	}

	public function onWarning(Entity $entity, Warning $warning)
	{
		$entity->warning_id = $warning->warning_id;
		$entity->save();
	}

	public function onWarningRemoval(Entity $entity, Warning $warning)
	{
		$entity->warning_id = 0;
		$entity->warning_message = '';
		$entity->save();
	}

	public function takeContentAction(Entity $entity, $action, array $options)
	{
		if ($action == 'public')
		{
			$message = isset($options['message']) ? $options['message'] : '';
			if (is_string($message) && strlen($message))
			{
				$entity->warning_message = $message;
				$entity->save();
			}
		}
		else if ($action == 'delete')
		{
			$reason = isset($options['reason']) ? $options['reason'] : '';
			if (!is_string($reason))
			{
				$reason = '';
			}

			/** @var \XenAddons\AMS\Service\ArticlePage\Deleter $deleter */
			$deleter = \XF::app()->service('XenAddons\AMS:ArticlePage\Deleter', $entity);
			$deleter->delete('soft', $reason);
		}
	}

	protected function canWarnPublicly(Entity $entity)
	{
		return true;
	}

	protected function canDeleteContent(Entity $entity)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
		return $entity->canDelete('soft');
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();
		return ['User', 'Article', 'Article.Category', 'Article.Category.Permissions|' . $visitor->permission_combination_id];
	}
}