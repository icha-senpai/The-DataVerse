<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\ReactionTrait;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null page_id
 * @property int article_id
 * @property int user_id
 * @property string username
 * @property string message
 * @property string meta_description
 * @property string page_state
 * @property int display_order
 * @property string title
 * @property string nav_title
 * @property int display_byline
 * @property int create_date
 * @property int edit_date
 * @property int depth
 * @property int last_edit_date
 * @property int last_edit_user_id
 * @property int edit_count
 * @property int attach_count
 * @property int cover_image_id
 * @property int cover_image_above_page
 * @property int has_poll
 * @property int reaction_score
 * @property array reactions_
 * @property array reaction_users_
 * @property int warning_id
 * @property string warning_message
 * @property int ip_id
 * @property array|null embed_metadata

 * GETTERS
 * @property ArticleItem Content
 * @property string article_title
 * @property mixed reactions
 * @property mixed reaction_users
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ArticleItem Article
 * @property \XF\Entity\Attachment CoverImage
 * @property \XF\Entity\Attachment[] Attachments
 * @property \XF\Entity\User User
 * @property \XF\Entity\DeletionLog DeletionLog
 * @property \XF\Entity\ReactionContent[] Reactions
 */
class ArticlePage extends Entity implements \XF\BbCode\RenderableContentInterface
{
	use ReactionTrait;
	
	public function getBreadcrumbs()
	{
		$article = $this->Article;
		
		return $article->getBreadcrumbs(true);
	}
	
	public function canView(&$error = null)
	{
		$article = $this->Article;

		return ($article ? $article->canView($error) : false);
	}
	
	public function canViewPageAttachments()
	{
		$article = $this->Article;
		
		return $article->canViewPageAttachments();
	}

	public function canEdit(&$error = null)
	{
		$article = $this->Article;
		
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		if ($article->hasPermission('editAny'))
		{
			return true;
		}
		
		if ($this->user_id == $visitor->user_id && $article->hasPermission('editOwn'))
		{
			$editLimit = $article->hasPermission('editOwnArticleTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->create_date < \XF::$time - 60 * $editLimit) && $article->article_state != 'draft')
			{
				$error = \XF::phrase('xa_ams_time_limit_to_edit_this_article_page_x_minutes_has_expired', ['editLimit' => $editLimit]);
				return false;
			}
		
			return true;
		}
		
		return $article->canEdit();
	}

	public function canEditSilently(&$error = null)
	{
		$article = $this->Article;
		
		$visitor = \XF::visitor();
		if (!$visitor->user_id || !$article)
		{
			return false;
		}
	
		if ($article->hasPermission('editAny'))  // TODO create page permissions instead of piggybacking off article permissions!
		{
			return true;
		}
	
		return false;
	}
	
	public function canViewHistory(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		$article = $this->Article;
	
		if (!$this->app()->options()->editHistory['enabled'])
		{
			return false;
		}
	
		if ($article->hasPermission('editAny'))
		{
			return true;
		}
	
		return false;
	}
	
	public function canReassign(&$error = null)
	{
		$visitor = \XF::visitor();
	
		$article = $this->Article;
	
		return (
			$visitor->user_id
			&& $article->hasPermission('reassign')  // TODO create page permissions instead of piggybacking off article permissions?  reassignPage
		);
	}

	public function canDelete($type = 'soft', &$error = null)
	{
		$article = $this->Article;
		
		$visitor = \XF::visitor();
		
		if ($type != 'soft')
		{
			return $article->hasPermission('hardDeleteAny');
		}
		
		if ($article->hasPermission('deleteAny'))
		{
			return true;
		}
		
		if ($this->user_id == $visitor->user_id && $article->hasPermission('deleteOwn'))
		{
			$editLimit = $article->hasPermission('editOwnArticleTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->create_date < \XF::$time - 60 * $editLimit) && $article->article_state != 'draft')
			{
				$error = \XF::phrase('xa_ams_time_limit_to_delete_this_article_page_x_minutes_has_expired', ['editLimit' => $editLimit]);
				return false;
			}
		
			return true;
		}
		
		return $article->canDelete($type);
	}
	
	public function canUndelete(&$error = null)
	{
		$article = $this->Article;
		
		$visitor = \XF::visitor();
		return $visitor->user_id && $article->hasPermission('undelete');
	}
	
	public function canSetCoverImage(&$error = null)
	{
		if (!$this->hasImageAttachments())
		{
			return false;
		}
	
		return $this->canEdit($error);
	}

	public function canReport(&$error = null, \XF\Entity\User $asUser = null)
	{
		$asUser = $asUser ?: \XF::visitor();
		return $asUser->canReport($error);
	}
	
	public function canWarn(&$error = null)
	{
		$visitor = \XF::visitor();
		$article = $this->Article;
	
		if ($this->warning_id
			|| !$article
			|| !$visitor->user_id
			|| $this->user_id == $visitor->user_id
			|| !$article->hasPermission('warn')
		)
		{
			return false;
		}
	
		$user = $this->User;
		return ($user && $user->isWarnable());
	}
	
	public function canReact(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if ($this->page_state != 'visible')
		{
			return false;
		}
	
		if ($this->user_id == $visitor->user_id)
		{
			$error = \XF::phraseDeferred('reacting_to_your_own_content_is_considered_cheating');
			return false;
		}
	
		return $this->Article->hasPermission('react');  // TODO maybe add page permissions instead of piggybacking off of Article permissions? 
	}
	
	public function canCleanSpam()
	{
		return (\XF::visitor()->canCleanSpam() && $this->User && $this->User->isPossibleSpammer());
	}
	
	public function canCreatePoll(&$error = null)
	{
		if ($this->has_poll)
		{
			return false;
		}
	
		if (!$this->Article->Category->canCreatePoll())
		{
			return false;
		}
		
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		$categoryId = $this->Article->category_id;
	
		if ($visitor->hasAmsArticleCategoryPermission($categoryId, 'editAny'))
		{
			return true;
		}
		
		if ($this->user_id == $visitor->user_id && $visitor->hasAmsArticleCategoryPermission($categoryId, 'editOwn'))
		{
			$editLimit = $visitor->hasAmsArticleCategoryPermission($categoryId, 'editOwnArticleTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->Article->publish_date < \XF::$time - 60 * $editLimit) && $this->Article->article_state != 'draft')
			{
				$error = \XF::phraseDeferred('message_edit_time_limit_expired', ['minutes' => $editLimit]);
				return false;
			}
	
			return true;
		}
		
		return false;
	}	
	
	public function canUseInlineModeration(&$error = null)
	{
		// TODO implement inline moderation for Pages? 
		
		return false;
		
		//$visitor = \XF::visitor();
		//return ($visitor->user_id && $this->Article->hasPermission('inlineModPage'));
	}
	
	public function canSendModeratorActionAlert()
	{
		$visitor = \XF::visitor();
		
		return (
			$visitor->user_id
				&& $visitor->user_id != $this->user_id
				&& $this->page_state == 'visible'
		);
	}
	
	public function isVisible()
	{
		if ($this->page_state != 'visible')
		{
			return false;
		}
	
		$article = $this->Article;
		if (!$article)
		{
			return false;
		}
		else if ($article instanceof ArticleItem)
		{
			return ($article->article_state == 'visible');
		}
		else
		{
			return true;
		}
	}
	
	public function isIgnored()
	{
		return \XF::visitor()->isIgnoring($this->user_id);
	}	

	public function isAttachmentEmbedded($attachmentId)
	{
		if (!$this->embed_metadata)
		{
			return false;
		}
	
		if ($attachmentId instanceof \XF\Entity\Attachment)
		{
			$attachmentId = $attachmentId->attachment_id;
		}
	
		return isset($this->embed_metadata['attachments'][$attachmentId]);
	}
	
	public function hasImageAttachments($page = false)
	{
		if (!$this->attach_count)
		{
			return false;
		}
	
		if ($page && $page['Attachments'])
		{
			$attachments = $page['Attachments'];
		}
		else
		{
			$attachments = $this->Attachments;
		}
	
		foreach ($attachments AS $attachment)
		{
			if ($attachment['thumbnail_url'])
			{
				return true;
			}
		}
	
		return false;
	}
	
	/**
	 * @return string
	 */
	public function getArticleTitle()
	{
		return $this->Article ? $this->Article->title : '';
	}
	
	/**
	 * @return ArticleItem
	 */
	public function getContent()
	{
		return $this->Article;
	}
	
	public function updateCoverImageIfNeeded()
	{
		$attachments = $this->Attachments;
	
		$imageAttachments = [];
		$fileAttachments = [];
	
		foreach ($attachments AS $key => $attachment)
		{
			if ($attachment['thumbnail_url'])
			{
				$imageAttachments[$key] = $attachment;
			}
			else
			{
				$fileAttachments[$key] = $attachment;
			}
		}
	
		if (!$this->cover_image_id)
		{
			if ($imageAttachments)
			{
				foreach ($imageAttachments AS $imageAttachment)
				{
					$coverImageId = $imageAttachment['attachment_id'];
					break;
				}
	
				if ($coverImageId)
				{
					$this->db()->query("
						UPDATE xf_xa_ams_article_page
						SET cover_image_id = ?
						WHERE page_id = ?
					", [$coverImageId, $this->page_id]);
				}
			}
		}
		elseif ($this->cover_image_id)
		{
			if (!$imageAttachments || !$imageAttachments[$this->cover_image_id])
			{
				$this->db()->query("
					UPDATE xf_xa_ams_article_page
					SET cover_image_id = 0
					WHERE page_id = ?
				", $this->page_id);
			}
		}
	}	
	
	public function getBbCodeRenderOptions($context, $type)
	{
		return [
			'entity' => $this,
			'user' => $this->Article->User,
			'attachments' => $this->attach_count ? $this->Attachments : [],
			'viewAttachments' => $this->canViewPageAttachments()
		];
	}

	protected function _preSave()
	{
		if (!$this->article_id)
		{
			throw new \LogicException("Need article ID");
		}
	}

	protected function _postSave()
	{
		$this->updateArticleRecord();
		
		if ($this->isUpdate() && $this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorChanges('ams_page', $this);
		}
	}
	
	protected function updateArticleRecord()
	{
		if (!$this->Article)
		{
			return;
		}
		
		$article = $this->Article;
		
		if ($this->isInsert())
		{
			$article->pageAdded($this);
			$article->save();
		}
	}	

	protected function _postDelete()
	{
		$db = $this->db();
		
		$this->Article->pageRemoved($this);
		$this->Article->save();
		
		if ($this->has_poll && $this->Poll)
		{
			$this->Poll->delete();
		}
		
		if ($this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorAction('ams_page', $this, 'delete_hard');
		}

		//$db->delete('xf_approval_queue', 'content_id = ? AND content_type = ?', [$this->page_id, 'ams_page']);
		$db->delete('xf_deletion_log', 'content_id = ? AND content_type = ?', [$this->page_id, 'ams_page']);
		$db->delete('xf_edit_history', 'content_id = ? AND content_type = ?', [$this->page_id, 'ams_page']);
		
		/** @var \XF\Repository\Attachment $attachRepo */
		$attachRepo = $this->repository('XF:Attachment');
		$attachRepo->fastDeleteContentAttachments('ams_page', $this->page_id);
		
		/** @var \XF\Repository\Reaction $reactionRepo */
		$reactionRepo = $this->repository('XF:Reaction');
		$reactionRepo->fastDeleteReactions('ams_page', $this->page_id);
	}
	
	public function softDelete($reason = '', \XF\Entity\User $byUser = null)
	{
		$byUser = $byUser ?: \XF::visitor();
	
		if ($this->page_state == 'deleted')
		{
			return false;
		}
	
		$this->page_state = 'deleted';
	
		/** @var \XF\Entity\DeletionLog $deletionLog */
		$deletionLog = $this->getRelationOrDefault('DeletionLog');
		$deletionLog->setFromUser($byUser);
		$deletionLog->delete_reason = $reason;
	
		$this->save();
	
		return true;
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article_page';
		$structure->shortName = 'XenAddons\AMS:ArticlePage';
		$structure->primaryKey = 'page_id';
		$structure->contentType = 'ams_page';
		$structure->columns = [
			'page_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'article_id' => ['type' => self::UINT, 'required' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'username' => ['type' => self::STR, 'maxLength' => 50,
				'required' => 'please_enter_valid_name'
			],
			'title' => ['type' => self::STR, 'maxLength' => 150,
				'required' => 'please_enter_valid_title',
				'censor' => true
			],
			'message' => ['type' => self::STR,
				'required' => 'please_enter_valid_message'
			],
			'meta_description' => ['type' => self::STR, 'default' => '', 'maxLength' => 320, 'censor' => true],
			'page_state' => ['type' => self::STR, 'default' => 'visible',
				'allowedValues' => ['visible', 'deleted', 'draft']
			],
			'display_order' => ['type' => self::UINT, 'default' => 1],
			'nav_title' => ['type' => self::STR, 'maxLength' => 150, 'default' => '', 'censor' => true],
			'display_byline' => ['type' => self::BOOL, 'default' => false],
			'create_date' => ['type' => self::UINT, 'default' => \XF::$time],
			'edit_date' => ['type' => self::UINT, 'default' => \XF::$time],	
			'depth' => ['type' => self::UINT, 'default' => 0],
			'last_edit_date' => ['type' => self::UINT, 'default' => 0],
			'last_edit_user_id' => ['type' => self::UINT, 'default' => 0],
			'edit_count' => ['type' => self::UINT, 'default' => 0],
			'attach_count' => ['type' => self::UINT, 'default' => 0],
			'cover_image_id' => ['type' => self::UINT, 'default' => 0],
			'cover_image_above_page' => ['type' => self::BOOL, 'default' => false],
			'has_poll' => ['type' => self::BOOL, 'default' => false],
			'warning_id' => ['type' => self::UINT, 'default' => 0],
			'warning_message' => ['type' => self::STR, 'default' => '', 'maxLength' => 255],
			'ip_id' => ['type' => self::UINT, 'default' => 0],
			'embed_metadata' => ['type' => self::JSON_ARRAY, 'nullable' => true, 'default' => null]
		];
		$structure->getters = [
			'Content' => true,
			'article_title' => true
		];
		$structure->behaviors = [
			'XF:Reactable' => ['stateField' => 'page_state'],
			'XF:NewsFeedPublishable' => [
				'usernameField' => 'username',
				'dateField' => 'create_date'
			],
			'XF:Indexable' => [
				'checkForUpdates' => ['message', 'user_id', 'article_id', 'create_date', 'page_state']
			]
		];
		$structure->relations = [
			'Article' => [
				'entity' => 'XenAddons\AMS:ArticleItem',
				'type' => self::TO_ONE,
				'conditions' => 'article_id',
				'primary' => true
			],
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
			'Poll' => [
				'entity' => 'XF:Poll',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_page'],
					['content_id', '=', '$page_id']
				]
			],
			'CoverImage' => [
				'entity' => 'XF:Attachment',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_page'],
					['content_id', '=', '$page_id'],
					['attachment_id', '=', '$cover_image_id']
				],
				'with' => 'Data',
				'order' => 'attach_date'
			],
			'Attachments' => [
				'entity' => 'XF:Attachment',
				'type' => self::TO_MANY,
				'conditions' => [
					['content_type', '=', 'ams_page'],
					['content_id', '=', '$page_id']
				],
				'with' => 'Data',
				'order' => 'attach_date'
			],
			'DeletionLog' => [
				'entity' => 'XF:DeletionLog',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_page'],
					['content_id', '=', '$page_id']
				],
				'primary' => true
			]
		];
		$structure->options = [
			'log_moderator' => true
		];
		$structure->defaultWith = [
			'Article', 'User'
		];
		$structure->withAliases = [
			'full' => [
				'User',
				'CoverImage',
				function()
				{
					$userId = \XF::visitor()->user_id;
					if ($userId)
					{
						return ['Reactions|' . $userId];
					}
				}
			]
		];
		
		static::addReactableStructureElements($structure);
		
		return $structure;
	}
}