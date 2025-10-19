<?php

namespace XenAddons\AMS\Report;

use XF\Entity\Report;
use XF\Mvc\Entity\Entity;
use XF\Report\AbstractHandler;

class Comment extends AbstractHandler
{
	protected function canViewContent(Report $report)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		$contentInfo = $report->content_info;

		if (!empty($contentInfo['category_id']))
		{
			if (!method_exists($visitor, 'hasAmsArticleCategoryPermission'))
			{
				return false;
			}

			return $visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'viewComments');
		}
		else
		{
			return $visitor->hasPermission('xa_ams', 'viewComments');
		}
	}

	protected function canActionContent(Report $report)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		$contentInfo = $report->content_info;

		if (!empty($contentInfo['category_id']))
		{
			if (!method_exists($visitor, 'hasAmsArticleCategoryPermission'))
			{
				return false;
			}

			return (
				$visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'editAnyComment')
				|| $visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'deleteAnyComment')
			);
		}
		else
		{
			return (
				$visitor->hasPermission('xa_ams', 'editAnyComment')
				|| $visitor->hasPermission('xa_ams', 'deleteAnyComment')
			);
		}
	}

	public function setupReportEntityContent(Report $report, Entity $content)
	{
		/** @var \XenAddons\AMS\Entity\Comment $content */

		$contentInfo = [
			// these are only populated in the context of being containers of the comment content
			'category_id' => 0,
			'category_title' => '',

			// actually related to the comment content
			'content_type' => 'ams_article',
			'content_id' => $content->article_id,
			'content_title' => $content->Content->title,
			'content_message' => $content->Content->message,
			'user_id' => $content->user_id,
			'username' => $content->username,
			'comment' => [
				'comment_id' => $content->comment_id,
				'username' => $content->username,
				'message' => $content->message
			]
		];

		if ($content->Content->Category)
		{
			$contentInfo['category_id'] = $content->Content->Category->category_id;
			$contentInfo['category_title'] = $content->Content->Category->title;
		}

		$report->content_user_id = $content->user_id;
		$report->content_info = $contentInfo;
	}

	public function getContentTitle(Report $report)
	{
		$contentInfo = $report->content_info;

		if (!empty($contentInfo['article_id']))
		{
			return \XF::phrase('xa_ams_comment_by_x_in_article_y', [
				'user' => $contentInfo['username'],
				'title' => \XF::app()->stringFormatter()->censorText($contentInfo['content_title'])
			]);
		}
		else
		{
			return \XF::phrase('xa_ams_comment_by_x', [
				'user' => $contentInfo['username']
			]);
		}
	}

	public function getContentMessage(Report $report)
	{
		if (isset($report->content_info['comment']['message']))
		{
			return $report->content_info['comment']['message'];
		}
		else
		{
			if (isset($report->content_info['message']))
			{
				return $report->content_info['message'];
			}
			else
			{
				return 'N/A';
			}
		}
	}

	public function getContentLink(Report $report)
	{
		return \XF::app()->router()->buildLink('canonical:ams/comments', $report->content_info['comment']);
	}
}