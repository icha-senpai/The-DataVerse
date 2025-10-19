<?php

namespace XenAddons\AMS\NewsFeed;

use XF\Mvc\Entity\Entity;
use XF\NewsFeed\AbstractHandler;

class Page extends AbstractHandler
{
	public function isPublishable(Entity $entity, $action)
	{
		/** @var \XenAddons\AMS\Entity\ArticlePage $entity */

		if ($action == 'insert')
		{
		
		}
		
		return true;
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return ['Article', 'Article.User', 'Article.Category', 'Article.Category.Permissions|' . $visitor->permission_combination_id];
	}
	
	protected function addAttachmentsToContent($content)
	{
		/** @var \XF\Repository\Attachment $attachmentRepo */
		$attachmentRepo = \XF::repository('XF:Attachment');
		$attachmentRepo->addAttachmentsToContent($content, 'ams_page');
	
		return $content;
	}
}