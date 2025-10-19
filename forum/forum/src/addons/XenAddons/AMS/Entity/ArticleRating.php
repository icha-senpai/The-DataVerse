<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\ReactionTrait;
use XF\Entity\ContentVoteTrait;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null rating_id
 * @property int article_id
 * @property int user_id
 * @property string username
 * @property int rating
 * @property int rating_date
 * @property string rating_state
 * @property bool is_review
 * @property string pros
 * @property string cons
 * @property string message
 * @property int author_response_contributor_user_id
 * @property string author_response_contributor_username
 * @property string author_response
 * @property array custom_fields_
 * @property int warning_id
 * @property string warning_message
 * @property bool is_anonymous
 * @property int attach_count
 * @property int ip_id
 * @property array|null embed_metadata
 * @property int reaction_score
 * @property array reactions_
 * @property array reaction_users_
 * @property int vote_score
 * @property int vote_count
 * 
 * GETTERS
 * @property ArticleItem Content
 * @property string article_title
 * @property \XF\CustomField\Set custom_fields
 * @property mixed reactions
 * @property mixed reaction_users
 * @property mixed vote_score_short  
 * 
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ArticleItem Article
 * @property \XF\Entity\Attachment[] Attachments
 * @property \XF\Entity\ApprovalQueue ApprovalQueue
 * @property \XF\Entity\User User
 * @property \XF\Entity\User AuthorResponseContributorUser
 * @property \XF\Entity\DeletionLog DeletionLog
 * @property \XF\Entity\ReactionContent[] Reactions
 * @property \XF\Mvc\Entity\AbstractCollection|\XF\Entity\ContentVote[] ContentVotes
 */
class ArticleRating extends Entity implements \XF\BbCode\RenderableContentInterface, \XF\Entity\LinkableInterface
{
	use ReactionTrait, ContentVoteTrait;
	
	public function canView(&$error = null)
	{
		$visitor = \XF::visitor();
		
		$article = $this->Article;

		if ($this->rating_state == 'deleted')
		{
			if (!$article->hasPermission('viewDeletedReviews'))
			{
				return false;
			}
		}
		
		if ($this->rating_state == 'moderated')
		{
			if (
				!$article->hasPermission('viewModeratedReviews') 
				&& $this->user_id != $visitor->user_id
			)
			{
				return false;
			}
		}

		return ($article ? $article->canView($error) && $article->canViewReviews($error) : false);
	}
	
	public function canViewReviewImages()
	{
		$article = $this->Article;
		
		return $article->hasPermission('viewReviewAttach');
	}
	
	public function canEdit(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}

		$article = $this->Article;

		if ($article->hasPermission('editAnyReview'))
		{
			return true;
		}

		if ($this->user_id == $visitor->user_id && $article->hasPermission('editReview'))
		{
			$editLimit = $article->hasPermission('editOwnReviewTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->rating_date < \XF::$time - 60 * $editLimit))
			{
				$error = \XF::phrase('xa_ams_time_limit_to_edit_this_review_x_minutes_has_expired', ['editLimit' => $editLimit]);
				return false;
			}

			return true;
		}

		return false;
	}	
	
	public function canEditSilently(&$error = null)
	{
		$article = $this->Article;
		$visitor = \XF::visitor();
		if (!$visitor->user_id || !$article)
		{
			return false;
		}
	
		if ($article->hasPermission('editAnyReview'))
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
	
		if ($article->hasPermission('editAnyReview'))
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
			&& $article->hasPermission('reassignReview')
		);
	}
	
	public function canChangeDate(&$error = null)
	{
		$visitor = \XF::visitor();
	
		if (!$visitor->user_id)
		{
			return false;
		}
	
		$article = $this->Article;
	
		// TODO maybe expend this to have its own permission?
		return $article->hasPermission('editAnyReview');
	}
	
	public function canDelete($type = 'soft', &$error = null)
	{
		$visitor = \XF::visitor();

		if (!$visitor->user_id)
		{
			return false;
		}

		$article = $this->Article;
		
		if ($type != 'soft' && !$article->hasPermission('hardDeleteAnyReview'))
		{
			return false;
		}
		
		if ($article->hasPermission('deleteAnyReview'))
		{
			return true;
		}
		
		if ($this->user_id == $visitor->user_id && $article->hasPermission('deleteReview'))
		{
			$editLimit = $article->hasPermission('editOwnReviewTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->rating_date < \XF::$time - 60 * $editLimit))
			{
				$error = \XF::phrase('xa_ams_time_limit_to_delete_this_review_x_minutes_has_expired', ['editLimit' => $editLimit]);
				return false;
			}
		
			return true;
		}
		
		return false;
	}

	public function canUpdate(&$error = null)
	{
		$visitor = \XF::visitor();
		$article = $this->Article;

		if (!$visitor->user_id
			|| ($visitor->user_id != $this->user_id && !$article->hasPermission('rateOwn'))
			|| !$article
			|| !$article->hasPermission('rate')
		)
		{
			return false;
		}

		if ($this->rating_state != 'visible' || !$this->is_review)
		{
			return true;
		}

		if ($this->author_response)
		{
			$error = \XF::phraseDeferred('xa_ams_cannot_update_rating_once_author_response');
			return false;
		}

		return true;
	}

	public function canUndelete(&$error = null)
	{
		$visitor = \XF::visitor();
		$article = $this->Article;

		if (!$visitor->user_id || !$article)
		{
			return false;
		}

		return $article->hasPermission('undelete');
	}
	
	public function canApproveUnapprove(&$error = null)
	{
		if (!$this->Article)
		{
			return false;
		}
	
		return $this->Article->hasPermission('approveUnapproveReview');
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

	public function canReply(&$error = null)
	{
		$visitor = \XF::visitor();
		$article = $this->Article;

		return (
			$visitor->user_id
			&& $article
			&& $article->isContributor()
			&& $this->is_review
			&& !$this->author_response
			&& $this->rating_state == 'visible'
			&& $article->hasPermission('reviewReply')
		);
	}
	
	public function canReact(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if ($this->rating_state != 'visible')
		{
			return false;
		}
	
		if ($this->user_id == $visitor->user_id)
		{
			$error = \XF::phraseDeferred('reacting_to_your_own_content_is_considered_cheating');
			return false;
		}
	
		return $this->Article->hasPermission('reactReview');
	}
	
	public function canCleanSpam()
	{
		return (\XF::visitor()->canCleanSpam() && $this->User && $this->User->isPossibleSpammer());
	}	

	public function canDeleteAuthorResponse(&$error = null)
	{
		$visitor = \XF::visitor();
		$article = $this->Article;

		if (!$visitor->user_id || !$this->is_review || !$this->author_response || !$article)
		{
			return false;
		}
		
		if (
			$article->isContributor() 
			&& $visitor->user_id == $this->author_response_contributor_user_id
		)
		{
			return true;
		}

		return (
			$visitor->user_id == $this->Article->user_id
			|| $article->hasPermission('deleteAnyReview')
		);
	}

	public function canViewAnonymousAuthor()
	{
		$visitor = \XF::visitor();

		return (
			$visitor->user_id
			&& (
				$visitor->user_id == $this->user_id
				|| $visitor->canBypassUserPrivacy()
			)
		);
	}
	
	public function isContentVotingSupported(): bool
	{
		$article = $this->Article;
		
		return $article->Category->review_voting !== '';
	}
	
	public function isContentDownvoteSupported(): bool
	{
		$article = $this->Article;
		
		return $article->Category->review_voting === 'yes';
	}
	
	protected function canVoteOnContentInternal(&$error = null): bool
	{
		if (!$this->isVisible())
		{
			return false;
		}
	
		$article = $this->Article;
	
		if ($article->isContributor())
		{
			return $article->hasPermission('contentVoteContributor');
		}
	
		return $article->hasPermission('contentVote');
	}

	public function canSendModeratorActionAlert()
	{
		$visitor = \XF::visitor();
		
		if (!$visitor->user_id || $visitor->user_id == $this->user_id)
		{
			return false;
		}
		
		if ($this->rating_state != 'visible')
		{
			return false;
		}
		
		return true;
	}
	
	public function canUseInlineModeration(&$error = null)
	{
		$visitor = \XF::visitor();
		return ($visitor->user_id && $this->Article->hasPermission('inlineModReview'));
	}	

	public function isVisible()
	{
		return (
			$this->rating_state == 'visible'
			&& $this->Article
			&& $this->Article->isVisible()
		);
	}

	public function isIgnored()
	{
		if ($this->is_anonymous)
		{
			return false;
		}

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
	
	public function getBbCodeRenderOptions($context, $type)
	{
		return [
			'entity' => $this,
			'user' => $this->User,
			'attachments' => $this->attach_count ? $this->Attachments : [],
			'viewAttachments' => $this->canViewReviewImages()
		];
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
	
	public function getFieldEditMode()
	{
		$visitor = \XF::visitor();
	
		$isSelf = ($visitor->user_id == $this->user_id || !$this->rating_id);
		$isMod = ($visitor->user_id && $this->Article->hasPermission('editAnyReview'));
	
		if ($isMod || !$isSelf)
		{
			return $isSelf ? 'moderator_user' : 'moderator';
		}
		else
		{
			return 'user';
		}
	}
	
	/**
	 * @return \XF\CustomField\Set
	 */
	public function getCustomFields()
	{
		/** @var \XF\CustomField\DefinitionSet $fieldDefinitions */
		$fieldDefinitions = $this->app()->container('customFields.ams_reviews');
	
		return new \XF\CustomField\Set($fieldDefinitions, $this);
	}
	
	/**
	 * @return \XF\CustomField\Set
	 */
	public function getReviewFields()
	{
		/** @var \XF\CustomField\DefinitionSet $fieldDefinitions */
		$fieldDefinitions = $this->app()->container('customFields.ams_reviews');
	
		return new \XF\CustomField\Set($fieldDefinitions, $this);
	}
	
	public function hasImageAttachments($review = false)
	{
		if ($review && $review['Attachments'])
		{
			$attachments = $review['Attachments'];
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

	protected function _preSave()
	{
		if ($this->isChanged('message'))
		{
			$this->is_review = strlen($this->message) ? true : false;
		}
	}

	protected function _postSave()
	{
		$visibilityChange = $this->isStateChanged('rating_state', 'visible');
		$approvalChange = $this->isStateChanged('rating_state', 'moderated');
		$deletionChange = $this->isStateChanged('rating_state', 'deleted');

		if ($this->isUpdate())
		{
			if ($visibilityChange == 'enter')
			{
				$this->ratingMadeVisible();
			}
			else if ($visibilityChange == 'leave')
			{
				$this->ratingHidden();
			}
			else if ($this->isChanged('rating'))
			{
				$this->ratingChanged();	
			}

			if ($deletionChange == 'leave' && $this->DeletionLog)
			{
				$this->DeletionLog->delete();
			}
			
			if ($approvalChange == 'leave' && $this->ApprovalQueue)
			{
				$this->ApprovalQueue->delete();
			}
		}
		else
		{
			// insert
			if ($this->rating_state == 'visible')
			{
				$this->ratingMadeVisible();
			}
		}

		if ($approvalChange == 'enter')
		{
			$approvalQueue = $this->getRelationOrDefault('ApprovalQueue', false);
			$approvalQueue->content_date = $this->rating_date;
			$approvalQueue->save();
		}
		else if ($deletionChange == 'enter' && !$this->DeletionLog)
		{
			$delLog = $this->getRelationOrDefault('DeletionLog', false);
			$delLog->setFromVisitor();
			$delLog->save();
		}

		if ($this->isUpdate() && $this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorChanges('ams_rating', $this);
		}
	}

	protected function ratingChanged()
	{
		$article = $this->Article;
		
		if ($article)
		{
			$article->rebuildRating();
			$article->saveIfChanged();
		}		
	}
	
	protected function ratingMadeVisible()
	{
		$article = $this->Article;

		if ($this->is_review && $article)
		{
			$article->review_count++;
		}

		if ($article)
		{
			$article->rebuildRating();
			$article->saveIfChanged();
		}
	}

	protected function ratingHidden($hardDelete = false)
	{
		$article = $this->Article;

		if ($this->is_review && $article)
		{
			$article->review_count--;
		}

		if ($article)
		{
			$article->rebuildRating();
			$article->saveIfChanged();
		}

		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = $this->repository('XF:UserAlert');
		$alertRepo->fastDeleteAlertsForContent('ams_rating', $this->rating_id);
	}

	protected function _postDelete()
	{
		if ($this->rating_state == 'visible')
		{
			$this->ratingHidden(true);
		}

		if ($this->rating_state == 'deleted' && $this->DeletionLog)
		{
			$this->DeletionLog->delete();
		}
		
		if ($this->rating_state == 'moderated' && $this->ApprovalQueue)
		{
			$this->ApprovalQueue->delete();
		}

		if ($this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorAction('ams_rating', $this, 'delete_hard');
		}
		
		$db = $this->db();
		
		$db->delete('xf_xa_ams_review_field_value', 'rating_id = ?', $this->rating_id);
		
		$db->delete('xf_approval_queue', 'content_id = ? AND content_type = ?', [$this->rating_id, 'ams_rating']);
		$db->delete('xf_deletion_log', 'content_id = ? AND content_type = ?', [$this->rating_id, 'ams_rating']);
		$db->delete('xf_edit_history', 'content_id = ? AND content_type = ?', [$this->rating_id, 'ams_rating']);
		
		/** @var \XF\Repository\Attachment $attachRepo */
		$attachRepo = $this->repository('XF:Attachment');
		$attachRepo->fastDeleteContentAttachments('ams_rating', $this->rating_id);
		
		/** @var \XF\Repository\Reaction $reactionRepo */
		$reactionRepo = $this->repository('XF:Reaction');
		$reactionRepo->fastDeleteReactions('ams_rating', $this->rating_id);
	}

	public function softDelete($reason = '', \XF\Entity\User $byUser = null)
	{
		$byUser = $byUser ?: \XF::visitor();

		if ($this->rating_state == 'deleted')
		{
			return false;
		}

		$this->rating_state = 'deleted';

		/** @var \XF\Entity\DeletionLog $deletionLog */
		$deletionLog = $this->getRelationOrDefault('DeletionLog');
		$deletionLog->setFromUser($byUser);
		$deletionLog->delete_reason = $reason;

		$this->save();

		return true;
	}
	
	public function getContentUrl(bool $canonical = false, array $extraParams = [], $hash = null)
	{
		if ($this->is_review)
		{
			$route = ($canonical ? 'canonical:' : '') . 'ams/review';
			return $this->app()->router('public')->buildLink($route, $this, $extraParams, $hash);
		}
		else
		{
			return '';
		}
	}
	
	public function getContentPublicRoute()
	{
		return 'ams/review';
	}
	
	public function getContentTitle(string $context = '')
	{
		return \XF::phrase('xa_ams_review_in_x', [
			'title' => $this->Article->title
		]);
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article_rating';
		$structure->shortName = 'XenAddons\AMS:ArticleRating';
		$structure->primaryKey = 'rating_id';
		$structure->contentType = 'ams_rating';
		$structure->columns = [
			'rating_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'article_id' => ['type' => self::UINT, 'required' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'username' => ['type' => self::STR, 'maxLength' => 50,
				'required' => 'please_enter_valid_name'
			],
			'rating' => ['type' => self::UINT, 'required' => true, 'min' => 1, 'max' => 5],
			'rating_date' => ['type' => self::UINT, 'default' => \XF::$time],
			'rating_state' => ['type' => self::STR, 'default' => 'visible',
				'allowedValues' => ['visible', 'moderated', 'deleted']
			],
			'is_review' => ['type' => self::BOOL, 'default' => false],
			'pros' => ['type' => self::STR, 'default' => ''],
			'cons' => ['type' => self::STR, 'default' => ''],
			'message' => ['type' => self::STR, 'default' => ''],
			'author_response_contributor_user_id' => ['type' => self::UINT, 'default' => 0],
			'author_response_contributor_username' => ['type' => self::STR, 'maxLength' => 50, 'default' => ''],
			'author_response' => ['type' => self::STR, 'default' => ''],
			'custom_fields' => ['type' => self::JSON_ARRAY, 'default' => []],
			'warning_id' => ['type' => self::UINT, 'default' => 0],
			'warning_message' => ['type' => self::STR, 'default' => '', 'maxLength' => 255],
			'is_anonymous' => ['type' => self::BOOL, 'default' => false],
			'attach_count' => ['type' => self::UINT, 'default' => 0],
			'last_edit_date' => ['type' => self::UINT, 'default' => 0],
			'last_edit_user_id' => ['type' => self::UINT, 'default' => 0],
			'edit_count' => ['type' => self::UINT, 'default' => 0],
			'ip_id' => ['type' => self::UINT, 'default' => 0],
			'embed_metadata' => ['type' => self::JSON_ARRAY, 'nullable' => true, 'default' => null]
		];
		$structure->getters = [
			'custom_fields' => true,
			'Content' => true,
			'article_title' => true
		];
		$structure->behaviors = [
			'XF:ContentVotable' => ['stateField' => 'rating_state'],
			'XF:Reactable' => ['stateField' => 'rating_state'],
			'XF:NewsFeedPublishable' => [
				'userIdField' => function($rating) { return $rating->is_anonymous ? 0 : $rating->user_id; },
				'usernameField' => function($rating) { return $rating->is_anonymous ? '' : $rating->User->username; },
				'dateField' => 'rating_date'
			],
			'XF:CustomFieldsHolder' => [
				'valueTable' => 'xf_xa_ams_review_field_value',
				'checkForUpdates' => ['category_id'],
				'getAllowedFields' => function($rating) { return $rating->Article->Category ? $rating->Article->Category->review_field_cache : []; }
			],
			'XF:Indexable' => [
				'checkForUpdates' => ['message', 'user_id', 'article_id', 'rating_date', 'rating_state']
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
			'AuthorResponseContributorUser' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => [['user_id', '=', '$author_response_contributor_user_id']],
				'primary' => true
			],
			'Attachments' => [
				'entity' => 'XF:Attachment',
				'type' => self::TO_MANY,
				'conditions' => [
					['content_type', '=', 'ams_rating'],
					['content_id', '=', '$rating_id']
				],
				'with' => 'Data',
				'order' => 'attach_date'
			],
			'ApprovalQueue' => [
				'entity' => 'XF:ApprovalQueue',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_rating'],
					['content_id', '=', '$rating_id']
				],
				'primary' => true
			],			
			'DeletionLog' => [
				'entity' => 'XF:DeletionLog',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_rating'],
					['content_id', '=', '$rating_id']
				],
				'primary' => true
			]
		];
		$structure->options = [
			'log_moderator' => true
		];
		$structure->defaultWith = [
			'Article'
		];
		$structure->withAliases = [
			'full' => [
				'User', 'AuthorResponseContributorUser',
				function()
				{
					$userId = \XF::visitor()->user_id;
					if ($userId)
					{
						return [
							'Reactions|' . $userId,
							'ContentVotes|' . $userId
						];
					}
				}
			]
		];
		
		static::addReactableStructureElements($structure);
		static::addVotableStructureElements($structure);
		
		return $structure;
	}
}