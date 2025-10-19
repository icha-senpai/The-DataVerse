<?php

namespace XenAddons\AMS\Service\Comment;

use XenAddons\AMS\Entity\Comment;

class Editor extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var Comment
	 */
	protected $comment;

	/**
	 * @var \XenAddons\AMS\Service\Comment\Preparer
	 */
	protected $commentPreparer;

	protected $oldMessage;

	protected $logDelay;
	protected $logEdit = true;
	protected $logHistory = true;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, Comment $comment)
	{
		parent::__construct($app);
		$this->setComment($comment);
	}

	public function setComment(Comment $comment)
	{
		$this->comment = $comment;
		$this->commentPreparer = $this->service('XenAddons\AMS:Comment\Preparer', $this->comment);
	}

	public function getComment()
	{
		return $this->comment;
	}

	public function logDelay($logDelay)
	{
		$this->logDelay = $logDelay;
	}

	public function logEdit($logEdit)
	{
		$this->logEdit = $logEdit;
	}

	public function logHistory($logHistory)
	{
		$this->logHistory = $logHistory;
	}

	public function getCommentPreparer()
	{
		return $this->commentPreparer;
	}

	protected function setupEditHistory()
	{
		$comment = $this->comment;

		$comment->edit_count++;

		$options = $this->app->options();
		if ($options->editLogDisplay['enabled'] && $this->logEdit)
		{
			$delay = is_null($this->logDelay) ? $options->editLogDisplay['delay'] * 60 : $this->logDelay;
			if ($comment->comment_date + $delay <= \XF::$time)
			{
				$comment->last_edit_date = \XF::$time;
				$comment->last_edit_user_id = \XF::visitor()->user_id;
			}
		}

		if ($options->editHistory['enabled'] && $this->logHistory)
		{
			$this->oldMessage = $comment->message;
		}
	}

	public function setMessage($message, $format = true)
	{
		if (!$this->comment->isChanged('message'))
		{
			$this->setupEditHistory();
		}
		return $this->commentPreparer->setMessage($message, $format);
	}
	
	public function setAttachmentHash($hash)
	{
		$this->commentPreparer->setAttachmentHash($hash);
	}	

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function checkForSpam()
	{
		if ($this->comment->comment_state == 'visible' && \XF::visitor()->isSpamCheckRequired())
		{
			$this->commentPreparer->checkForSpam();
		}
	}

	protected function finalSetup() {}

	protected function _validate()
	{
		$this->finalSetup();

		$this->comment->preSave();
		return $this->comment->getErrors();
	}

	protected function _save()
	{
		$comment = $this->comment;
		$visitor = \XF::visitor();

		$db = $this->db();
		$db->beginTransaction();

		$comment->save(true, false);

		$this->commentPreparer->afterUpdate();

		if ($this->oldMessage)
		{
			/** @var \XF\Repository\EditHistory $repo */
			$repo = $this->repository('XF:EditHistory');
			$repo->insertEditHistory('ams_comment', $comment, $visitor, $this->oldMessage, $this->app->request()->getIp());
		}

		if ($comment->comment_state == 'visible' && $this->alert && $comment->user_id != $visitor->user_id)
		{
			/** @var \XenAddons\AMS\Repository\Comment $commentRepo */
			$commentRepo = $this->repository('XenAddons\AMS:Comment');
			$commentRepo->sendModeratorActionAlert($comment, 'edit', $this->alertReason);
		}

		$db->commit();

		return $comment;
	}
}