<?php

namespace XenAddons\AMS\EditHistory;

use XF\EditHistory\AbstractHandler;
use XF\Mvc\Entity\Entity;


class Rating extends AbstractHandler
{
	/**
	 * @param \XenAddons\AMS\Entity\ArticleRating $content
	 */
	public function canViewHistory(Entity $content)
	{
		return ($content->canView() && $content->canViewHistory());
	}

	/**
	 * @param \XenAddons\AMS\Entity\ArticleRating $content
	 */
	public function canRevertContent(Entity $content)
	{
		return $content->canEdit();
	}

	/**
	 * @param \XenAddons\AMS\Entity\ArticleRating $content
	 */
	public function getContentTitle(Entity $content)
	{
		return \XF::phrase('xa_ams_review_by_x_in_article_y', [
			'user' => $content->username,
			'title' => $content->Article->title
		]);
	}

	/**
	 * @param \XenAddons\AMS\Entity\ArticleRating $content
	 */
	public function getContentText(Entity $content)
	{
		return $content->message;
	}

	public function getContentLink(Entity $content)
	{
		return \XF::app()->router()->buildLink('ams/review', $content);
	}

	/**
	 * @param \XenAddons\AMS\Entity\ArticleRating $content
	 */
	public function getBreadcrumbs(Entity $content)
	{
		return $content->Article->getBreadcrumbs();
	}

	/**
	 * @param \XenAddons\AMS\Entity\ArticleRating $content
	 */
	public function revertToVersion(Entity $content, \XF\Entity\EditHistory $history, \XF\Entity\EditHistory $previous = null)
	{
		/** @var \XenAddons\AMS\Service\Review\Edit $editor */
		$editor = \XF::app()->service('XenAddons\AMS:Review\Edit', $content);

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
		return \XF::app()->templater()->func('bb_code', [$text, 'ams_rating', $content]);
	}

	public function getSectionContext()
	{
		return 'xa_ams';
	}
}