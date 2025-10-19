<?php

namespace XenAddons\AMS\Poll;

use XF\Entity\Poll;
use XF\Mvc\Entity\Entity;
use XF\Poll\AbstractHandler;

class Series extends AbstractHandler
{
	public function canCreate(Entity $content, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $content */

		return $content->canCreatePoll($error);
	}

	public function canEdit(Entity $content, Poll $poll, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $content */

		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}

		if ($visitor->hasAmsSeriesPermission('editAnySeries'))
		{
			return true;
		}

		if ($content->user_id == $visitor->user_id && $visitor->hasAmsSeriesPermission('editOwnSeries'))
		{
			$editLimit = $visitor->hasAmsSeriesPermission('editOwnSeriesTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $content->create_date < \XF::$time - 60 * $editLimit))
			{
				$error = \XF::phraseDeferred('message_edit_time_limit_expired', ['minutes' => $editLimit]);
				return false;
			}

			return true;
		}

		return false;
	}

	public function canAlwaysEditDetails(Entity $content, Poll $poll, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $content */

		$visitor = \XF::visitor();
		return ($visitor->user_id && $visitor->hasAmsSeriesPermission('editAnySeries'));
	}

	public function canDelete(Entity $content, Poll $poll, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $content */

		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}

		if ($visitor->hasAmsSeriesPermission('editAnySeries'))
		{
			return true;
		}

		if ($visitor->user_id != $content->user_id)
		{
			return false;
		}

		return ($poll->voter_count == 0);
	}

	public function canVote(Entity $content, Poll $poll, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $content */

		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		return $visitor->hasAmsSeriesPermission('votePollSeries');
	}

	public function getPollLink($action, Entity $content, array $extraParams = [])
	{
		if ($action == 'content')
		{
			return \XF::app()->router('public')->buildLink('ams/series', $content, $extraParams);
		}
		else
		{
			return \XF::app()->router('public')->buildLink('ams/series/poll/' . $action, $content, $extraParams);
		}
	}

	public function finalizeCreation(Entity $content, Poll $poll)
	{
		$content->has_poll = true;
		$content->save();		
	}

	public function finalizeDeletion(Entity $content, Poll $poll)
	{
		$content->has_poll = false;
		$content->save();
	}

	public function getEntityWith()
	{
		return ['Series', 'User'];
	}
}