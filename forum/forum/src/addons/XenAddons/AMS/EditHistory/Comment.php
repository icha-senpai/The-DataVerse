<?php

namespace XenAddons\AMS\EditHistory;

use XF\EditHistory\AbstractHandler;
use XF\Mvc\Entity\Entity;


class Comment extends AbstractHandler
{
	/**
	 * @param \XenAddons\AMS\Entity\Comment $content
	 */
	public function canViewHistory(Entity $content)
	{
		return ($content->canView() && $content->canViewHistory());
	}

	/**
	 * @param \XenAddons\AMS\Entity\Comment $content
	 */
	public function canRevertContent(Entity $content)
	{
		return $content->canEdit();
	}

	/**
	 * @param \XenAddons\AMS\Entity\Comment $content
	 */
	public function getContentTitle(Entity $content)
	{
		return \XF::phrase('xa_ams_comment_by_x_in_article_y', [
			'user' => $content->username,
			'title' => $content->Content->title
		]);
	}

	/**
	 * @param \XenAddons\AMS\Entity\Comment $content
	 */
	public function getContentText(Entity $content)
	{
		return $content->message;
	}

	public function getContentLink(Entity $content)
	{
		return \XF::app()->router()->buildLink('ams/comments', $content);
	}

	/**
	 * @param \XenAddons\AMS\Entity\Comment $content
	 */
	public function getBreadcrumbs(Entity $content)
	{
		return $content->Content->getBreadcrumbs();
	}

	/**
	 * @param \XenAddons\AMS\Entity\Comment $content
	 */
	public function revertToVersion(Entity $content, \XF\Entity\EditHistory $history, \XF\Entity\EditHistory $previous = null)
	{
		/** @var \XenAddons\AMS\Service\Comment\Editor $editor */
		$editor = \XF::app()->service('XenAddons\AMS:Comment\Editor', $content);

		$editor->logEdit(false);
		$editor->setMessage($history->old_text);

		if (!$previous || $previous->edit_user_id != $content->user_id)
		{
			$content->last_edit_date = 0;
		}
		else if ($previous && $previous->edit_user_id == $content->user_id)
		{
			$content->last_edit_date = $previous->edit_date;
			$content->last_edit_user_id = $previous->edit_user_id;
		}

		return $editor->save();
	}

	public function getHtmlFormattedContent($text, Entity $content = null)
	{
		return \XF::app()->templater()->func('bb_code', [$text, 'ams_comment', $content]);
	}

	public function getSectionContext()
	{
		return 'xa_ams';
	}
}