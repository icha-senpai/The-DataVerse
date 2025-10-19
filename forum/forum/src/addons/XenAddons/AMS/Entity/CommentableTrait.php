<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Structure;

/**
 * @property \XF\Draft $draft_comment
 */
trait CommentableTrait
{
	abstract public function hasPermission($permission);

	public function canViewComments(&$error = null)
	{
		if (!$this->isAllowComments())
		{
			return false;
		}
		
		return $this->hasPermission('viewComments');
	}

	public function canAddComment(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if (!$this->isVisible())
		{
			return false;
		}
		
		if (!$this->isAllowComments())
		{
			return false;			
		}
		
		if (!$this->isCommentsOpen())
		{
			return false;
		}
		
		if ($visitor->user_id)
		{
			$replyBans = $this->ReplyBans;
			if ($replyBans)
			{
				if (isset($replyBans[$visitor->user_id]))
				{
					$replyBan = $replyBans[$visitor->user_id];
					$isBanned = ($replyBan && (!$replyBan->expiry_date || $replyBan->expiry_date > time()));
					if ($isBanned)
					{
						return false;
					}
				}
			}
		}

		return $this->hasPermission('addComment');
	}
	
	public function canAddCommentPreReg()
	{
		if (\XF::visitor()->user_id || $this->canAddComment())
		{
			// quick bypass with the user ID check, then ensure that this can only return true if the visitor
			// can't take the "normal" action
			return false;
		}
	
		return \XF::canPerformPreRegAction(
			function () { return $this->canAddComment(); }
		);
	}

	public function canReplyToComment(&$error = null)
	{
		return $this->canAddComment($error);
	}
	
	public function canReplyToCommentPreReg()
	{
		return $this->canAddCommentPreReg();
	}

	public function canViewDeletedComments()
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		return $this->hasPermission('viewDeletedComments');
	}

	public function canViewModeratedComments()
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		return $this->hasPermission('viewModeratedComments');
	}

	public function canViewCommentModeratorLogs(&$error = null)
	{
		$visitor = \XF::visitor();
		return $visitor->user_id && ($this->hasPermission('editAnyComment') || $this->hasPermission('deleteAnyComment'));
	}

	/**
	 * @return \XF\Draft
	 */
	public function getDraftComment()
	{
		return \XF\Draft::createFromEntity($this, 'DraftComments');
	}

	public function getNewCommentState()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		if ($visitor->user_id && $this->hasPermission('approveUnapproveComment'))
		{
			return 'visible';
		}

			if (!$this->hasPermission('addCommentWithoutApproval'))
		{
			return 'moderated';
		}

		return 'visible';
	}

	/**
	 * @return \XenAddons\AMS\Entity\Comment
	 */
	public function getNewComment()
	{
		$comment = $this->em()->create('\XenAddons\AMS:Comment');
		$comment->article_id = $this->getEntityId();

		return $comment;
	}

	public function getVisitorReadDate()
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return null;
		}

		$commentRead = $this->CommentRead[$visitor->user_id];
		$contentRead = isset($this->Read) ? $this->Read[$visitor->user_id] : null;

		$dates = [\XF::$time - $this->app()->options()->readMarkingDataLifetime * 86400];
		if ($commentRead)
		{
			$dates[] = $commentRead->comment_read_date;
		}
		if ($contentRead)
		{
			$dates[] = $contentRead->article_read_date;
		}

		return max($dates);
	}

	public function getCommentIds()
	{
		return $this->db()->fetchAllColumn("
			SELECT comment_id
			FROM xf_xa_ams_comment
			WHERE article_id = ?
			ORDER BY comment_date
		", [$this->getEntityId()]);
	}

	public function rebuildCommentCount()
	{
		$this->comment_count = $this->db()->fetchOne("
			SELECT COUNT(*)
			FROM xf_xa_ams_comment
			WHERE article_id = ?
				AND comment_state = 'visible'
		", [$this->getEntityId()]);

		return $this->comment_count;
	}

	public function rebuildLastCommentInfo()
	{
		$lastComment = $this->db()->fetchRow("
			SELECT comment_id, comment_date, user_id, username
			FROM xf_xa_ams_comment
			WHERE article_id = ?
				AND comment_state = 'visible'
			ORDER BY comment_date DESC
			LIMIT 1
		", [$this->getEntityId()]);

		if (!$lastComment)
		{
			$lastComment = [
				'comment_id' => 0,
				'comment_date' => 0,
				'user_id' => 0,
				'username' => ''
			];
		}

		$this->last_comment_id = $lastComment['comment_id'];
		$this->last_comment_date = $lastComment['comment_date'];
		$this->last_comment_user_id = $lastComment['user_id'];
		$this->last_comment_username = $lastComment['username'] ?: '-';

		return true;
	}

	public function commentAdded(Comment $comment)
	{
		$this->comment_count++;

		if ($comment->comment_date >= $this->last_comment_date)
		{
			$this->last_comment_date = $comment->comment_date;
			$this->last_comment_id = $comment->comment_id;
			$this->last_comment_user_id = $comment->user_id;
			$this->last_comment_username = $comment->username;
		}
	}

	public function commentRemoved(Comment $comment)
	{
		$this->comment_count--;

		if ($comment->comment_id == $this->last_comment_id)
		{
			$this->rebuildLastCommentInfo();
		}
	}

	protected function _postDeleteComments()
	{
		$commentIds = $this->comment_ids;
		if ($commentIds)
		{
			$db = $this->db();

			/** @var \XF\Repository\Attachment $attachRepo */
			$attachRepo = $this->repository('XF:Attachment');
			$attachRepo->fastDeleteContentAttachments('ams_comment', $commentIds);
			
			/** @var \XF\Repository\Reaction $reactionRepo */
			$reactionRepo = $this->repository('XF:Reaction');
			$reactionRepo->fastDeleteReactions('ams_comment', $commentIds);

			$db->delete('xf_xa_ams_comment', 'comment_id IN (' . $db->quote($commentIds) . ')');

			$db->delete('xf_approval_queue', 'content_id IN (' . $db->quote($commentIds) . ') AND content_type = ?', 'ams_comment');
			$db->delete('xf_deletion_log', 'content_id IN (' . $db->quote($commentIds) . ') AND content_type = ?', 'ams_comment');
			$db->delete('xf_edit_history', 'content_id IN (' . $db->quote($commentIds) . ') AND content_type = ?', 'ams_comment');
		}
	}

	protected static function addCommentableStructureElements(Structure $structure)
	{
		$structure->columns['last_comment_date'] = ['type' => self::UINT, 'default' => 0];
		$structure->columns['last_comment_id'] = ['type' => self::UINT, 'default' => 0];
		$structure->columns['last_comment_user_id'] = ['type' => self::UINT, 'default' => 0];
		$structure->columns['last_comment_username'] = ['type' => self::STR, 'maxLength' => 50, 'default' => ''];
		$structure->columns['comment_count'] = ['type' => self::UINT, 'forced' => true];

		$structure->getters['draft_comment'] = true;
		$structure->getters['comment_ids'] = true;

		$structure->relations['DraftComments'] = [
			'entity' => 'XF:Draft',
			'type' => self::TO_MANY,
			'conditions' => [
				['draft_key', '=', str_replace('_', '-', 'ams_article') . '-comment-', '$' . $structure->primaryKey]
			],
			'key' => 'user_id'
		];

		$structure->relations['LastComment'] = [
			'entity' => 'XenAddons\AMS:Comment',
			'type' => self::TO_ONE,
			'conditions' => [['comment_id', '=', '$last_comment_id']],
			'primary' => true
		];

		$structure->relations['LastCommenter'] = [
			'entity' => 'XF:User',
			'type' => self::TO_ONE,
			'conditions' => [['user_id', '=', '$last_comment_user_id']],
			'primary' => true
		];

		$structure->relations['CommentRead'] = [
			'entity' => (
				$structure->contentType == 'ams_article'
					? 'XenAddons\AMS:CommentRead'
					: 'XenAddons\AMS:CommentRead'
			),
			'type' => self::TO_MANY,
			'conditions' => $structure->primaryKey,
			'key' => 'user_id'
		];
	}
}