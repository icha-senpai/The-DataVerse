<?php

namespace XenAddons\AMS\ModeratorLog;

use XF\Entity\ModeratorLog;
use XF\ModeratorLog\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Series extends AbstractHandler
{
	public function isLoggable(Entity $content, $action, \XF\Entity\User $actor)
	{
		switch ($action)
		{
			case 'title':
		}

		return parent::isLoggable($content, $action, $actor);
	}

	protected function getLogActionForChange(Entity $content, $field, $newValue, $oldValue)
	{
		switch ($field)
		{
			case 'description':
				return 'edit';
				
			case 'message':
				return 'edit';
					
			case 'title':
				return ['title', ['old' => $oldValue]];
				
			case 'series_state':
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
				
			case 'user_id':
				$oldUser = \XF::em()->find('XF:User', $oldValue);
				$from = $oldUser ? $oldUser->username : '';
				return ['reassign', ['from' => $from]];
		}

		return false;
	}

	protected function setupLogEntityContent(ModeratorLog $log, Entity $content)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $content */
		$series = $content;
		
		/** @var \XF\Entity\Thread $content */
		$log->content_user_id = $series->user_id;
		$log->content_username = $series->User ? $series->User->username : '';
		$log->content_title = $series->title;
		$log->content_url = \XF::app()->router('public')->buildLink('nopath:ams/series', $series);
		$log->discussion_content_type = 'ams_series';
		$log->discussion_content_id = $series->series_id;
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