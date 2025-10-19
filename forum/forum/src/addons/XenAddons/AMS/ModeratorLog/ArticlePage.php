<?php

namespace XenAddons\AMS\ModeratorLog;

use XF\Entity\ModeratorLog;
use XF\ModeratorLog\AbstractHandler;
use XF\Mvc\Entity\Entity;

class ArticlePage extends AbstractHandler
{
	public function isLoggable(Entity $content, $action, \XF\Entity\User $actor)
	{
		switch ($action)
		{
			case 'title':
				/** @var \XenAddons\AMS\Entity\ArticleItem $content */
				if ($content->isContributor($actor->user_id))
				{
					return false;
				}
		}

		return parent::isLoggable($content, $action, $actor);
	}

	protected function getLogActionForChange(Entity $content, $field, $newValue, $oldValue)
	{
		switch ($field)
		{
			case 'message':
				return 'edit';
				
			case 'page_state':
				if ($newValue == 'visible' && $oldValue == 'moderated')
				{
					return 'approve';
				}
				else if ($newValue == 'visible' && $oldValue == 'deleted')
				{
					return 'undelete';
				}
				else if ($newValue == 'deleted')
				{
					$reason = $content->DeletionLog ? $content->DeletionLog->delete_reason : '';
					return ['delete_soft', ['reason' => $reason]];
				}
				else if ($newValue == 'moderated')
				{
					return 'unapprove';
				}
				break;

			case 'title':
				return ['title', ['old' => $oldValue]];
		}

		return false;
	}

	protected function setupLogEntityContent(ModeratorLog $log, Entity $content)
	{
		/** @var \XenAddons\AMS\Entity\ArticlePage $content */
		$article = $content->Article;
		
		/** @var \XF\Entity\Thread $content */
		$log->content_user_id = $article->user_id;
		$log->content_username = $article->User->username;
		$log->content_title = $content->title;
		$log->content_url = \XF::app()->router('public')->buildLink('nopath:ams/page', $content);
		$log->discussion_content_type = 'ams_article';
		$log->discussion_content_id = $content->article_id;
	}

	protected function getActionPhraseParams(ModeratorLog $log)
	{
		if ($log->action == 'edit')
		{
			return ['elements' => implode(', ', array_keys($log->action_params))];
		}
		else
		{
			return parent::getActionPhraseParams($log);
		}
	}
}