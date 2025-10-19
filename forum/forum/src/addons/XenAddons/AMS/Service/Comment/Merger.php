<?php

namespace XenAddons\AMS\Service\Comment;

use XenAddons\AMS\Entity\Comment;
use XF\Entity\User;

class Merger extends \XF\Service\AbstractService
{
	/**
	 * @var Comment
	 */
	protected $target;

	protected $originalTargetMessage;

	/**
	 * @var \XenAddons\AMS\Service\Comment\Preparer
	 */
	protected $commentPreparer;

	protected $alert = false;
	protected $alertReason = '';

	protected $log = true;

	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem[]
	 */
	protected $sourceArticles = [];

	/**
	 * @var \XenAddons\AMS\Entity\Comment[]
	 */
	protected $sourceComments = [];

	public function __construct(\XF\App $app, Comment $target)
	{
		parent::__construct($app);

		$this->target = $target;
		$this->originalTargetMessage = $target->message;
		$this->commentPreparer = $this->service('XenAddons\AMS:Comment\Preparer', $this->target);
	}

	public function getTarget()
	{
		return $this->target;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function setLog($log)
	{
		$this->log = (bool)$log;
	}

	public function setMessage($message, $format = true)
	{
		return $this->commentPreparer->setMessage($message, $format);
	}

	public function merge($sourceCommentsRaw)
	{
		if ($sourceCommentsRaw instanceof \XF\Mvc\Entity\AbstractCollection)
		{
			$sourceCommentsRaw = $sourceCommentsRaw->toArray();
		}
		else if ($sourceCommentsRaw instanceof Comment)
		{
			$sourceCommentsRaw = [$sourceCommentsRaw];
		}
		else if (!is_array($sourceCommentsRaw))
		{
			throw new \InvalidArgumentException('Comments must be provided as collection, array or entity');
		}

		if (!$sourceCommentsRaw)
		{
			return false;
		}

		$db = $this->db();

		/** @var Comment[] $sourceComments */
		$sourceComments = [];

		/** @var \XenAddons\AMS\Entity\ArticleItem[] $sourceArticles */
		$sourceArticles = [];

		foreach ($sourceCommentsRaw AS $sourceComment)
		{
			$sourceComment->setOption('log_moderator', false);
			$sourceComments[$sourceComment->comment_id] = $sourceComment;

			/** @var \XenAddons\AMS\Entity\ArticleItem $sourceArticle */
			$sourceArticle = $sourceComment->Article;
			if (!isset($sourceArticles[$sourceArticle->article_id]))
			{
				$sourceArticle->setOption('log_moderator', false);
				$sourceArticles[$sourceArticle->article_id] = $sourceArticle;
			}
		}

		$this->sourceArticles = $sourceArticles;
		$this->sourceComments = $sourceComments;

		$target = $this->target;
		$target->setOption('log_moderator', false);

		$db->beginTransaction();

		$this->moveDataToTarget();
		$this->updateTargetData();
		$this->updateSourceData();
		$this->updateUserCounters();

		if ($this->alert)
		{
			$this->sendAlert();
		}

		$this->finalActions();

		$target->save();

		$db->commit();

		return true;
	}

	protected function moveDataToTarget()
	{
		$db = $this->db();
		$target = $this->target;

		$sourceComments = $this->sourceComments;
		$sourceCommentIds = array_keys($sourceComments);
		$sourceIdsQuoted = $db->quote($sourceCommentIds);

		$rows = $db->update('xf_attachment',
			['content_id' => $target->comment_id],
			"content_id IN ($sourceIdsQuoted) AND content_type = 'ams_comment'"
		);

		$target->attach_count += $rows;

		$db->update(
			'xf_bookmark_item',
			[
				'content_type' => 'ams_comment',
				'content_id' => $target->comment_id
			],
			"content_id IN ($sourceIdsQuoted) AND content_type = 'ams_comment'",
			[], 'IGNORE'
		);

		foreach ($sourceComments AS $sourceComment)
		{
			$sourceComment->delete();
		}
	}

	protected function updateTargetData()
	{
		/** @var \XenAddons\Entity\ArticleItem $targetArticle */
		$targetArticle = $this->target->Article;

		$targetArticle->rebuildCounters();
		$targetArticle->save();
	}

	protected function updateSourceData()
	{
		/** @var \XenAddons\AMS\Repository\Article $articleRepo */
		$articleRepo = $this->repository('XenAddons\AMS:Article');

		foreach ($this->sourceArticles AS $sourceArticle)
		{
			$sourceArticle->rebuildCounters();

			$sourceArticle->save(); // has to be saved for the delete to work (if needed).

			$sourceArticle->Category->rebuildCounters();
			$sourceArticle->Category->save();
		}
	}

	protected function updateUserCounters()
	{
		// currently not needed, but may expand if we start counting comments as message_count
	}

	protected function sendAlert()
	{
		/** @var \XenAddons\Repository\Comment $commentRepo */
		$commentRepo = $this->repository('XenAddons\AMS:Comment');

		$alerted = [];
		foreach ($this->sourceComments AS $sourceComment)
		{
			if (isset($alerted[$sourceComment->user_id]))
			{
				continue;
			}

			if ($sourceComment->comment_state == 'visible' && $sourceComment->user_id != \XF::visitor()->user_id)
			{
				$commentRepo->sendModeratorActionAlert($sourceComment, 'merge', $this->alertReason);
				$alerted[$sourceComment->user_id] = true;
			}
		}
	}

	protected function finalActions()
	{
		$target = $this->target;
		$commentIds = array_keys($this->sourceComments);

		if ($commentIds)
		{
			$this->app->jobManager()->enqueue('XF:SearchIndex', [
				'content_type' => 'ams_comment',
				'content_ids' => $commentIds
			]);
		}

		if ($this->log)
		{
			$this->app->logger()->logModeratorAction('ams_comment', $target, 'merge_target',
				['ids' => implode(', ', $commentIds)]
			);
		}

		$preEditMergeMessage = $this->originalTargetMessage;
		foreach ($this->sourceComments AS $s)
		{
			$preEditMergeMessage .= "\n\n" . $s->message;
		}
		$preEditMergeMessage = trim($preEditMergeMessage);

		$options = $this->app->options();
		if ($options->editLogDisplay['enabled'] && $this->log && $target->message != $preEditMergeMessage)
		{
			$target->last_edit_date = \XF::$time;
			$target->last_edit_user_id = \XF::visitor()->user_id;
		}

		if ($options->editHistory['enabled'])
		{
			$visitor = \XF::visitor();
			$ip = $this->app->request()->getIp();

			/** @var \XF\Repository\EditHistory $editHistoryRepo */
			$editHistoryRepo = $this->app->repository('XF:EditHistory');

			// Log an edit history record for the target post's original message then log a further record
			// for the pre-merge result of all the source and target messages. These two entries should ensure
			// there is no context loss as a result of merging a series of posts.
			$editHistoryRepo->insertEditHistory('ams_comment', $target, $visitor, $this->originalTargetMessage, $ip);
			$target->edit_count++;

			if ($target->message != $preEditMergeMessage)
			{
				$editHistoryRepo->insertEditHistory('ams_comment', $target, $visitor, $preEditMergeMessage, $ip);
				$target->edit_count++;
			}
		}
	}
}