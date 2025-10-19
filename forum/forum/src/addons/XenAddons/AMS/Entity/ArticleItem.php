<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\BookmarkTrait;
use XF\Entity\ReactionTrait;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null article_id
 * @property int category_id
 * @property int user_id
 * @property string username
 * @property array contributor_user_ids
 * @property string title
 * @property string description
 * @property string meta_description
 * @property string article_state
 * @property string message
 * @property int publish_date
 * @property string publish_datetimezone
 * @property int last_update
 * @property int last_feature_date
 * @property int edit_date
 * @property int reaction_score
 * @property array reactions_
 * @property array reaction_users_
 * @property int attach_count
 * @property int view_count
 * @property int page_count
 * @property string overview_page_title
 * @property string overview_page_nav_title
 * @property int rating_count
 * @property int rating_sum
 * @property string rating_avg
 * @property string rating_weighted
 * @property int review_count
 * @property int comment_count
 * @property int series_part_id
 * @property int cover_image_id
 * @property int cover_image_above_article
 * @property int discussion_thread_id
 * @property array custom_fields_
 * @property int prefix_id
 * @property array tags
 * @property int comments_open
 * @property int ratings_open
 * @property array original_source
 * @property string location
 * @property int has_poll
 * @property int warning_id
 * @property string warning_message
 * @property int ip_id
 * @property array|null embed_metadata

 * 
 * GETTERS
 * @property \XF\CustomField\Set custom_fields
 * @property int real_review_count
 * @property int real_comment_count
 * @property int image_attach_count
 * @property array article_rating_ids
 * @property array article_page_ids
 * @property \XF\Draft draft_article
 * @property \XF\Draft draft_comment
 * @property mixed comment_ids
 * @property mixed reactions
 * @property mixed reaction_users
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\Category Category
 * @property \XF\Entity\Attachment CoverImage
 * @property \XF\Entity\Attachment[] Attachments
 * @property \XF\Entity\User User
 * @property \XF\Entity\User LastCommenter
 * @property \XF\Mvc\Entity\AbstractCollection|\XenAddons\AMS\Entity\ArticleContributor[] Contributors
 * @property \XF\Entity\Thread Discussion
 * @property \XF\Entity\Poll Poll
 * @property \XenAddons\AMS\Entity\ArticleFeature Featured
 * @property \XenAddons\AMS\Entity\SeriesPart SeriesPart
 * @property \XenAddons\AMS\Entity\ArticlePrefix Prefix
 * @property \XenAddons\AMS\Entity\ArticleReplyBan[] ReplyBans
 * @property \XenAddons\AMS\Entity\ArticleWatch[] Watch
 * @property \XF\Entity\DeletionLog DeletionLog
 * @property \XF\Entity\ApprovalQueue ApprovalQueue
 * @property \XF\Entity\Draft[] DraftArticles
 * @property \XF\Entity\Draft[] DraftComments
 * @property \XF\Entity\ReactionContent[] Reactions
 * @property \XF\Entity\BookmarkItem[] Bookmarks
 * @property \XenAddons\AMS\Entity\Comment LastComment
 * @property \XenAddons\AMS\Entity\ArticleRead[] Read
 * @property \XenAddons\AMS\Entity\CommentRead[] CommentRead
 * @property \XenAddons\AMS\Entity\ArticleFieldValue[] CustomFields
 */
class ArticleItem extends Entity implements \XF\BbCode\RenderableContentInterface, \XF\Entity\LinkableInterface
{
	use CommentableTrait, ReactionTrait, BookmarkTrait;
	
	const RATING_WEIGHTED_THRESHOLD = 10;
	const RATING_WEIGHTED_AVERAGE = 3;

	public function getBreadcrumbs($includeSelf = true)
	{
		$breadcrumbs = $this->Category ? $this->Category->getBreadcrumbs() : [];
		
		if ($includeSelf)
		{
			$breadcrumbs[] = [
				'href' => $this->app()->router()->buildLink('ams', $this),
				'value' => $this->title
			];
		}

		return $breadcrumbs;
	}

	public function canView(&$error = null)
	{
		if (!$this->Category || !$this->Category->canView())
		{
			return false;
		}

		$visitor = \XF::visitor();

		if (!$this->hasPermission('view'))
		{
			return false;
		}

		if ($this->article_state == 'moderated')
		{
			if (
				!$this->hasPermission('viewModerated')
				&& (!$visitor->user_id || !$this->isContributor())
			)
			{
				return false;
			}
		}
		else if ($this->article_state == 'deleted')
		{
			if (!$this->hasPermission('viewDeleted'))
			{
				return false;
			}
		}
		else if ($this->article_state == 'draft')
		{
			if (
				!$this->hasPermission('viewDraft')
				&& (!$visitor->user_id || !$this->isContributor())
			)
			{
				return false;
			}
		}
		else if ($this->article_state == 'awaiting')
		{
			if (
				!$this->hasPermission('viewAwaiting') 
				&& (!$visitor->user_id || !$this->isContributor())
			)
			{
				return false;
			}
		}
		
		return true;
	}
	
	public function canViewDeletedReviews()
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		return $this->hasPermission('viewDeletedReviews');
	}

	public function canViewModeratedReviews()
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		return $this->hasPermission('viewModeratedReviews');
	}
	
	public function canViewFullArticle()
	{
		$visitor = \XF::visitor();

		return (
			$this->hasPermission('viewFull')
			|| ($visitor->user_id && $this->isContributor())
		);
	}

	public function canViewArticleMap()
	{
		return $this->hasPermission('viewArticleMap');
	}

	public function canViewArticleAttachments()
	{
		return $this->hasPermission('viewArticleAttach');
	}
	
	public function canViewPageAttachments()
	{
		return $this->hasPermission('viewArticleAttach');  
	}
	
	public function canViewCommentImages()
	{
		return $this->hasPermission('viewReviewAttach');
	}
	
	public function canViewReviewImages()
	{
		return $this->hasPermission('viewReviewAttach');
	}
	
	public function canViewReviews()
	{
		return $this->hasPermission('viewReviews');
	}

	public function canAddPage(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		// only pages can be added to Visible or Draft articles!
		if ($this->isVisible() || $this->isDraft())
		{
			if ($this->hasPermission('editAny'))
			{
				return true;
			}
		
			return (
				$this->isContributor()
				&&	$this->hasPermission('addPageOwnArticle')
			);
		}
		else 
		{
			return false; 
		}
	}
	
	public function canCreatePoll(&$error = null)
	{
		if ($this->has_poll)
		{
			return false;
		}
		
		if (!$this->Category->canCreatePoll())
		{
			return false;
		}
		
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		$categoryId = $this->category_id;
	
		if ($visitor->hasAmsArticleCategoryPermission($categoryId, 'editAny'))
		{
			return true;
		}
	
		if ($this->isContributor() && $visitor->hasAmsArticleCategoryPermission($categoryId, 'editOwn'))
		{
			$editLimit = $visitor->hasAmsArticleCategoryPermission($categoryId, 'editOwnArticleTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->publish_date < \XF::$time - 60 * $editLimit) && $this->article_state != 'draft')
			{
				$error = \XF::phraseDeferred('message_edit_time_limit_expired', ['minutes' => $editLimit]);
				return false;
			}
	
			return true;
		}
	
		return false;
	}	
	
	public function canRate(&$error = null)
	{
		if (!$this->isVisible())
		{
			return false;
		}

		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
		
		if (!$this->isAllowRatings())
		{
			return false;
		}
		
		if (!$this->isRatingsOpen())
		{
			return false;
		}
		
		if ($this->isContributor() && !$this->hasPermission('rateOwn'))
		{
			return false;
		}
		
		if(!$this->hasPermission('reviewMultiple') && $this->hasPermission('rate'))
		{
			$existingRating = $this->Ratings[$visitor->user_id];
			if ($existingRating)
			{
				return false;
			}
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

		return $this->hasPermission('rate');
	}
	
	public function canRatePreReg()
	{
		if (\XF::visitor()->user_id || $this->canRate())
		{
			// quick bypass with the user ID check, then ensure that this can only return true if the visitor
			// can't take the "normal" action
			return false;
		}
	
		return \XF::canPerformPreRegAction(
			function() { return $this->canRate(); }
		);
	}	
	
	public function canReviewAnon(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		return $this->hasPermission('reviewAnon');
	}
	
	public function canReviewAnonPreReg()
	{
		if (\XF::visitor()->user_id || $this->canReviewAnon())
		{
			// quick bypass with the user ID check, then ensure that this can only return true if the visitor
			// can't take the "normal" action
			return false;
		}
		
		return \XF::canPerformPreRegAction(
			function() { return $this->canReviewAnon(); }
		);
	}

	public function canSaveAsDraft(&$error = null)
	{
		$visitor = \XF::visitor();

		return (
			$this->isContributor()
			&& $this->hasPermission('saveAsDraft')
		);
	}
	
	public function canPublishDraft(&$error = null)
	{
		$visitor = \XF::visitor();

		if (!in_array($this->article_state, ['draft','awaiting']))
		{
			return false;
		}
			
		return $this->canEdit($error);
	}
	
	// TODO probably should create a permission to controlled who can use scheduled publishing!
	public function canPublishDraftScheduled(&$error = null)
	{
		$visitor = \XF::visitor();
	
		if ($this->article_state != 'draft')
		{
			return false;
		}
			
		return $this->canEdit($error);
	}	
	
	// TODO probably should create a permission to controlled who can use scheduled publishing!
	public function canChangeScheduledPublishDate(&$error = null)
	{
		$visitor = \XF::visitor();
	
		if ($this->article_state != 'awaiting')
		{
			return false;
		}
			
		return $this->canEdit($error);
	}
	
	public function canEdit(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}

		if ($this->hasPermission('editAny'))
		{
			return true;
		}
		
		if ($this->isContributor() && $this->hasPermission('editOwn'))
		{
			$editLimit = $this->hasPermission('editOwnArticleTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->publish_date < \XF::$time - 60 * $editLimit) && $this->article_state != 'draft')
			{
				$error = \XF::phrase('xa_ams_time_limit_to_edit_this_article_x_minutes_has_expired', ['editLimit' => $editLimit]);
				return false;
			}
		
			return true;
		}
		
		return false;		
	}
	
	public function canLeaveContributorsTeam(&$error = null): bool
	{
		if (!$this->Category->allow_contributors)
		{
			return false;
		}
	
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if ($this->isOwner())
		{
			return false;
		}
	
		return $this->isContributor();
	}
	
	// TODO redesign this for AMS!
	public function canViewContributors(): bool
	{
		if (!$this->Category->allow_contributors)
		{
			return false;
		}
	
		$visitor = \XF::visitor();
	
		if ($this->hasPermission('reassign'))
		{
			return true;
		}
		
		if ($this->isContributor())
		{
			return true;
		}
	
		return true; // FOR NOW, EVERYONE CAN VIEW CONTRIBUTORS!
	}
	
	public function canManageContributors(&$error = null): bool
	{
		if (!$this->Category->allow_contributors)
		{
			return false;
		}
	
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if ($this->getMaxContributorCount() == 0)
		{
			return false;
		}
	
		return $this->canAddContributors() || $this->canRemoveContributors();
	}
	
	/**
	 * Note: check that canManageContributors is true before relying on this value.
	 *
	 * @return bool
	 */
	public function canAddCoAuthors(): bool
	{
		if ($this->hasPermission('manageAnyContributors'))
		{
			return true;
		}
		
		return (
			\XF::visitor()->user_id == $this->user_id
			&& $this->hasPermission('manageOwnContributors')
		);
	}
	
	/**
	 * Note: check that canManageContributors is true before relying on this value.
	 *
	 * @return bool
	 */
	public function canAddContributors(): bool
	{
		if ($this->hasPermission('manageAnyContributors'))
		{
			return true;
		}
		
		return (
			\XF::visitor()->user_id == $this->user_id
			&& $this->hasPermission('manageOwnContributors')
		);
	}
	
	/**
	 * Note: check that canManageContributors is true before relying on this value.
	 *
	 * @return bool
	 */
	public function canRemoveContributors(): bool
	{
		if (!$this->contributor_user_ids)
		{
			return false;
		}
	
		if ($this->hasPermission('manageAnyContributors'))
		{
			return true;
		}
		
		if ($this->hasPermission('reassign')) // Might not need this one due to the above check!
		{
			return true;
		}
	
		return (
			\XF::visitor()->user_id == $this->user_id
			&& $this->hasPermission('manageOwnContributors')
		);
	}
	
	public function canContributorBeAdded(User $user): bool
	{
		if (!$user->user_id)
		{
			return false;
		}
	
		if ($user->user_id == $this->user_id)
		{
			// can't add the author - normally skipped before this
			return false;
		}
	
		if ($user->isIgnoring($this->user_id))
		{
			// if the target is ignoring the article author, then don't let them be added
			return false;
		}
	
		/** @var \XenAddons\AMS\XF\Entity\User $user */
		return $user->hasAmsArticleCategoryPermission(
			$this->category_id,
			'add'
		);
	}
	
	public function canViewHistory(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if (!$this->app()->options()->editHistory['enabled'])
		{
			return false;
		}
	
		if ($this->hasPermission('editAny'))
		{
			return true;
		}
	
		return false;
	}
	
	public function canUploadAndManageArticleAttachments(&$error = null)
	{
		return ($this->canEdit($error) && $this->hasPermission('uploadArticleAttach'));
	}
	
	public function canSetAuthorRating(&$error = null)
	{
		if ($this->hasPermission('editAny'))
		{
			return true;
		}
	
		return (
			$this->hasPermission('setAuthorRatingOwn')
			&& $this->canEdit($error)
		);
	}	
	
	public function canSetCoverImage(&$error = null)
	{
		if (!$this->hasImageAttachments())
		{
			return false;
		}
		
		return $this->canEdit($error);
	}

	public function canMove(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if (!$visitor->user_id)
		{
			return false;
		}
		
		if ($this->hasPermission('move'))
		{
			return true;
		}

		return (
			$this->user_id == $visitor->user_id
			&& $this->hasPermission('moveOwn')
		);
	}
	
	public function canMerge(&$error = null)
	{
		$visitor = \XF::visitor();
	
		if (!$visitor->user_id)
		{
			return false;
		}
	
		return $this->hasPermission('editAny');
	}

	public function canReassign(&$error = null)
	{
		$visitor = \XF::visitor();

		return (
			$visitor->user_id
			&& $this->hasPermission('reassign')
		);
	}

	public function canFeatureUnfeature(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if ($this->hasPermission('featureUnfeature'))
		{
			return true;
		}
		
		return (
			$visitor->user_id
			&& $this->isContributor()
			&& $this->hasPermission('featureUnfeatureOwn')
		);
	}

	public function canBookmarkContent(&$error = null)
	{
		return $this->isVisible();
	}
	
	public function canChangeDates(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if (in_array($this->article_state, ['draft','awaiting']))
		{
			return false;
		}
		
		if ($this->hasPermission('changeDates'))
		{
			return true;
		}
		
		return (
			$visitor->user_id
			&& $this->isContributor()
			&& $this->hasPermission('changeDatesOwn')
			&& $this->canEdit($error)	
		);
	}
	
	public function canDelete($type = 'soft', &$error = null)
	{
		$visitor = \XF::visitor();

		if ($type != 'soft')
		{
			return $this->hasPermission('hardDeleteAny');
		}

		if ($this->hasPermission('deleteAny'))
		{
			return true;
		}
		
		if ($this->user_id == $visitor->user_id && $this->hasPermission('deleteOwn'))
		{
			$editLimit = $this->hasPermission('editOwnArticleTimeLimit');
			if ($editLimit != -1 && (!$editLimit || $this->publish_date < \XF::$time - 60 * $editLimit) && $this->article_state != 'draft')
			{
				$error = \XF::phrase('xa_ams_time_limit_to_delete_this_article_x_minutes_has_expired', ['editLimit' => $editLimit]);
				return false;
			}
		
			return true;
		}
		
		return false;		
	}

	public function canUndelete(&$error = null)
	{
		$visitor = \XF::visitor();
		
		return (
			$visitor->user_id 
			&& $this->hasPermission('undelete')
		);
	}
	
	public function canManagePages(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if ($this->hasPermission('editAny'))
		{
			return true;
		}
	
		return (
			$visitor->user_id
			&& $this->isContributor()
			&& $this->hasPermission('addPageOwnArticle')
			&& $this->canEdit($error)
		);
	}
	
	public function canManageRatings(&$error = null)
	{
		$visitor = \XF::visitor();
	
		return (
			$visitor->user_id
			&& $this->hasPermission('manageRatings')
		);
	}
	
	public function canReplyBan(&$error = null)
	{
		$visitor = \XF::visitor();
		
		return (
			$visitor->user_id 
			&& $this->hasPermission('articleReplyBan')
		);
	}
	
	public function canChangeDiscussionThread(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if (in_array($this->article_state, ['draft','awaiting']))
		{
			return false;
		}
		
		return (
			$visitor->user_id 
			&& $this->hasPermission('changeDiscussionThread')
		);
	}

	public function canSendModeratorActionAlert()
	{
		$visitor = \XF::visitor();

		return (
			$visitor->user_id
			&& !$this->isContributor()
			&& $this->article_state == 'visible'
		);
	}

	public function canApproveUnapprove(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if (in_array($this->article_state, ['draft','awaiting']))
		{
			return false;
		}
		
		return (
			$visitor->user_id
			&& $this->hasPermission('approveUnapprove')
		);
	}
	
	public function canWarn(&$error = null)
	{
		$visitor = \XF::visitor();
	
		if (in_array($this->article_state, ['draft','awaiting']))
		{
			return false;
		}
		
		if ($this->warning_id
			|| !$this->user_id
			|| !$visitor->user_id
			|| $this->isContributor()
			|| !$this->hasPermission('warn')
		)
		{
			return false;
		}
	
		return ($this->User && $this->User->isWarnable());
	}
	
	public function canReact(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}
	
		if ($this->article_state != 'visible')
		{
			return false;
		}
	
		if ($this->isContributor())
		{
			$error = \XF::phraseDeferred('reacting_to_your_own_content_is_considered_cheating');
			return false;
		}
	
		return $this->hasPermission('react');
	}
	
	public function canReport(&$error = null, \XF\Entity\User $asUser = null)
	{
		$asUser = $asUser ?: \XF::visitor();
		return $asUser->canReport($error);
	}

	public function canWatch(&$error = null)
	{
		$visitor = \XF::visitor();

		return ($visitor->user_id);
	}

	public function canEditTags(&$error = null)
	{
		$category = $this->Category;
		return $category ? $category->canEditTags($this, $error) : false;
	}

	public function canUseInlineModeration(&$error = null)
	{
		$visitor = \XF::visitor();
		
		if (in_array($this->article_state, ['draft','awaiting']))
		{
			return false;
		}
		
		return ($visitor->user_id && $this->hasPermission('inlineMod'));
	}
	
	public function canViewModeratorLogs(&$error = null)
	{
		$visitor = \XF::visitor();
		return $visitor->user_id && ($this->hasPermission('editAny') || $this->hasPermission('deleteAny'));
	}
	
	public function canLockUnlockComments(&$error = null)
	{
		$visitor = \XF::visitor();
	
		if ($this->hasPermission('lockUnlockCommentsAny'))
		{
			return true;
		}
	
		return (
			$visitor->user_id
			&& $this->isContributor()
			&& $this->hasPermission('lockUnlockCommentsOwn')
		);
	}

	public function canLockUnlockRatings(&$error = null)
	{
		$visitor = \XF::visitor();
	
		if ($this->hasPermission('lockUnlockRatingsAny'))
		{
			return true;
		}
	
		return (
			$visitor->user_id
			&& $this->isContributor()
			&& $this->hasPermission('lockUnlockRatingsOwn')
		);
	}

	public function hasPermission($permission)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		return $visitor->hasAmsArticleCategoryPermission($this->category_id, $permission);
	}

	public function isUnread()
	{
		if ($this->article_state == 'deleted')
		{
			return false;
		}
	
		$readDate = $this->getVisitorArticleReadDate();
		if ($readDate === null)
		{
			return false;
		}
	
		return $readDate < $this->last_update;
	}
	
	public function getVisitorArticleReadDate()
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return null;
		}
	
		$articleRead = $this->Read[$visitor->user_id];
	
		$dates = [\XF::$time - $this->app()->options()->readMarkingDataLifetime * 86400];
		if ($articleRead)
		{
			$dates[] = $articleRead->article_read_date;
		}
	
		return max($dates);
	}
	
	public function isIgnored()
	{
		return \XF::visitor()->isIgnoring($this->user_id);
	}

	public function isVisible()
	{
		return ($this->article_state == 'visible');
	}
	
	public function isOwner(): bool
	{
		$visitor = \XF::visitor();
		return $visitor->user_id && $visitor->user_id == $this->user_id;
	}
	
	public function isContributor(int $userId = null): bool
	{
		$userId = $userId === null ? \XF::visitor()->user_id : $userId;
		if (!$userId)
		{
			return false;
		}
	
		if ($userId == $this->user_id)
		{
			return true;
		}
	
		if (!$this->Category->allow_contributors)
		{
			return false;
		}
	
		return in_array($userId, $this->contributor_user_ids);
	}
	
	public function isNonOwnerContributor(int $userId = null): bool
	{
		$userId = $userId === null ? \XF::visitor()->user_id : $userId;
		if (!$userId)
		{
			return false;
		}
	
		if ($userId == $this->user_id)
		{
			return false;
		}
	
		return $this->isContributor($userId);
	}
	
	public function isDraft()
	{
		return ($this->article_state == 'draft');
	}
	
	// Future use for scheduled publishing... 
	public function isAwaitingPublishment()
	{
		return ($this->article_state == 'awaiting');
	}

	public function isInSeries()
	{
		return ($this->series_part_id ? true : false);
	}
	
	public function isAllowComments()
	{
		return ($this->Category->allow_comments);
	}
	
	public function isCommentsOpen()
	{
		return ($this->comments_open);
	}

	public function isAllowRatings()
	{
		return ($this->Category->allow_ratings);
	}
	
	public function isRatingsOpen()
	{
		return ($this->ratings_open);
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
	
	public function hasViewableDiscussion()
	{
		if (!$this->discussion_thread_id)
		{
			return false;
		}

		$thread = $this->Discussion;
		if (!$thread)
		{
			return false;
		}

		return $thread->canView();
	}
	
	public function getMaxContributorCount(): int
	{
		return $this->app()->options()->xaAmsMaxAllowedContributors;   // TODO probably make this a per category setting in the future!
	}

	public function getExtraFieldTabs()
	{
		if (!$this->getValue('custom_fields'))
		{
			return [];
		}

		/** @var \XF\CustomField\Set $fieldSet */
		$fieldSet = $this->custom_fields;
		$definitionSet = $fieldSet->getDefinitionSet()
			->filterOnly($this->Category->field_cache)
			->filterGroup('new_tab')
			->filterWithValue($fieldSet);

		$output = [];
		foreach ($definitionSet AS $fieldId => $definition)
		{
			$output[$fieldId] = $definition->title;
		}

		return $output;
	}

	public function getExpectedThreadTitle($currentValues = true)
	{
		$title = $currentValues ? $this->getValue('title') : $this->getExistingValue('title');
		$state = $currentValues ? $this->getValue('article_state') : $this->getExistingValue('article_state');

		$template = '';
		$options = $this->app()->options();

		if($state == 'draft' || $state == 'awaiting')
		{
			$template = '{title} [' . \XF::phraseDeferred('xa_ams_article_awaiting_publishing') . ']';
		}
		elseif ($state != 'visible' && $options->xaAmsArticleDeleteThreadAction['update_title'])
		{
			$template = $options->xaAmsArticleDeleteThreadAction['title_template'];
		}
		
		if (!$template)
		{
			$template = '{title}';
		}

		$threadTitle = str_replace('{title}', $title, $template);
		return $this->app()->stringFormatter()->wholeWordTrim($threadTitle, 100);
	}

	/**
	 * @return \XF\Draft
	 */
	public function getDraftArticle()
	{
		return \XF\Draft::createFromEntity($this, 'DraftArticles');
	}
	
	/**
	 * @return string
	 */
	public function getTrimmedArticle()
	{
		return '';
	}

	public function getFieldEditMode()
	{
		$visitor = \XF::visitor();
	
		$isContributor = ($this->isContributor() || !$this->article_id);
		$isMod = ($visitor->user_id && $this->hasPermission('editAny'));
	
		if ($isMod || !$isContributor)
		{
			return $isContributor ? 'moderator_user' : 'moderator';
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
		$class = 'XF\CustomField\Set';
		$class = $this->app()->extendClass($class);
		
		$fieldDefinitions = $this->app()->container('customFields.ams_articles');
		
		return new $class($fieldDefinitions, $this);
	}
	
	public function hasImageAttachments($article = false)
	{
		if (!$this->attach_count)
		{
			return false;
		}
		
		if ($article && $article['Attachments'])
		{
			$attachments = $article['Attachments'];
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
	
	public function getNewPage()
	{
		$page = $this->_em->create('XenAddons\AMS:ArticlePage');
		$page->article_id = $this->article_id;
	
		return $page;
	}
	
	public function getNewRating()
	{
		$rating = $this->_em->create('XenAddons\AMS:ArticleRating');
		$rating->article_id = $this->article_id;
	
		return $rating;
	}
	
	public function getNewRatingState()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
	
		if ($visitor->user_id && $this->hasPermission('approveUnapproveReview'))
		{
			return 'visible';
		}
	
		if (!$this->hasPermission('postReviewWithoutApproval'))
		{
			return 'moderated';
		}
	
		return 'visible';
	}	
	
	public function getNextPage($articlePages, $articlePage = null)
	{
		if (!$articlePage)
		{
			foreach ($articlePages as $pageID => $page)
			{
				return $page;   // returns the first page since you are viewing the overview
			}
		}
		else
		{
			$thisisit = false;
	
			foreach ($articlePages as $pageID => $page)
			{
				if ($thisisit)
				{
					return $page;
				}
	
				if ($page['page_id'] == $articlePage->page_id)
				{
					$thisisit = true;
				}
			}
				
			return false;  // already viewing the last page, so there won't be a next page
		}
	}	

	public function getPreviousPage($articlePages, $articlePage = null)
	{
		if (!$articlePage)
		{
			return false;  // already viewing the overview, so there is no previous page!
		}
		else
		{
			$thisIsCurrentPage = false;
			$previousPage = false;
				
			foreach ($articlePages as $pageID => $page)
			{
				// check to see if this is the page being viewed
				if ($page['page_id'] == $articlePage->page_id)
				{
					$thisIsCurrentPage = true;
					
					if ($previousPage)
					{
						return $previousPage;
					}
				}

				$previousPage = $page;
			}
				
			return false; // previous page is the article overview!
		}
	}
	
	public function getNextSeriesPart($seriesToc)
	{
		$thisisit = false;

		foreach ($seriesToc as $seriesPartId => $seriesPart)
		{
			if ($thisisit)
			{
				return $seriesPart;
			}

			if ($seriesPart['article_id'] == $this->article_id)
			{
				$thisisit = true;
			}
		}

		return false;  // already viewing the last series part, so there won't be a next series part!
	}
	
	public function getPreviousSeriesPart($seriesToc)
	{
		$thisIsCurrentSeriesPart = false;
		$previousSeriesPart = false;
	
		foreach ($seriesToc as $seriesPartId => $seriesPart)
		{
			// check to see if this is the series part being viewed
			if ($seriesPart['article_id'] == $this->article_id)
			{
				$thisIsCurrentSeriesPart = true;
					
				if ($previousSeriesPart)
				{
					return $previousSeriesPart;
				}
			}

			$previousSeriesPart = $seriesPart;
		}

		return false; // previous series part is the article being viewed!
	}
	
	public function pageAdded(ArticlePage $page)
	{
		$this->page_count++;
	}
	
	public function pageRemoved(ArticlePage $page)
	{
		$this->page_count--;
	}
	
	public function addedToSeries(SeriesPart $seriesPart)
	{
		$this->series_part_id = $seriesPart->part_id;
	}
	
	public function removedFromSeries(SeriesPart $seriesPart)
	{
		$this->series_part_id = 0;
	}
	
	/**
	 * @return int
	 */
	public function getImageAttachCount()
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
	
		return count($imageAttachments);
	}
	
	/**
	 * @return int
	 */
	public function getRealReviewCount()
	{
		if (!$this->canViewDeletedReviews())
		{
			return $this->review_count;
		}
		else
		{
			/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
			$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
			return $ratingRepo->findReviewsInArticle($this)->total();
		}
	}	

	/**
	 * @return array
	 */
	public function getArticlePageIds()
	{
		return $this->db()->fetchAllColumn("
			SELECT page_id
			FROM xf_xa_ams_article_page
			WHERE article_id = ?
			ORDER BY create_date
		", $this->article_id);
	}

	/**
	 * @return array
	 */
	public function getArticleRatingIds()
	{
		return $this->db()->fetchAllColumn("
			SELECT rating_id
			FROM xf_xa_ams_article_rating
			WHERE article_id = ?
			ORDER BY rating_date
		", $this->article_id);
	}

	public function rebuildCounters()
	{
		$this->rebuildCommentCount();
		$this->rebuildLastCommentInfo();
		$this->rebuildPageCount();
		$this->rebuildReviewCount();
		$this->rebuildRating();

		return true;
	}
	
	public function rebuildPageCount()
	{
		$this->page_count = $this->db()->fetchOne("
			SELECT COUNT(*)
				FROM xf_xa_ams_article_page
				WHERE article_id = ?
					AND page_state = 'visible'
		", $this->article_id);
	
		return $this->page_count;
	}

	public function rebuildReviewCount()
	{
		$this->review_count = $this->db()->fetchOne("
			SELECT COUNT(*)
				FROM xf_xa_ams_article_rating
				WHERE article_id = ?
					AND is_review = 1
					AND rating_state = 'visible'
		", $this->article_id);

		return $this->review_count;
	}

	public function rebuildRating()
	{
		$rating = $this->db()->fetchRow("
			SELECT COUNT(*) AS total,
				SUM(rating) AS sum
			FROM xf_xa_ams_article_rating
			WHERE article_id = ?
				AND rating_state = 'visible'
		", $this->article_id);

		$this->rating_sum = $rating['sum'] ?: 0;
		$this->rating_count = $rating['total'] ?: 0;
	}

	protected function updateRatingAverage()
	{
		$threshold = self::RATING_WEIGHTED_THRESHOLD;
		$average = self::RATING_WEIGHTED_AVERAGE;

		$this->rating_weighted = ($threshold * $average + $this->rating_sum) / ($threshold + $this->rating_count);

		if ($this->rating_count)
		{
			$this->rating_avg = $this->rating_sum / $this->rating_count;
		}
		else
		{
			$this->rating_avg = 0;
		}
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
						UPDATE xf_xa_ams_article
						SET cover_image_id = ?
						WHERE article_id = ?
					", [$coverImageId, $this->article_id]);
				}	
			}	
		}
		elseif ($this->cover_image_id)
		{
			if (!$imageAttachments || !$imageAttachments[$this->cover_image_id])
			{
				$this->db()->query("
					UPDATE xf_xa_ams_article
					SET cover_image_id = 0
					WHERE article_id = ?
				", $this->article_id);
			}
		}		
	}
	
	public function getBbCodeRenderOptions($context, $type)
	{
		return [
			'entity' => $this,
			'user' => $this->User,
			'attachments' => $this->attach_count ? $this->Attachments : [],
			'viewAttachments' => $this->canViewArticleAttachments()
		];
	}	

	protected function _preSave()
	{
		if ($this->prefix_id && $this->isChanged(['prefix_id', 'category_id']))
		{
			if (!$this->Category->isPrefixValid($this->prefix_id))
			{
				$this->prefix_id = 0;
			}
		}

		if ($this->isInsert() || $this->isChanged(['rating_sum', 'rating_count']))
		{
			$this->updateRatingAverage();
		}
	}

	protected function _postSave()
	{
		$visibilityChange = $this->isStateChanged('article_state', 'visible');
		$approvalChange = $this->isStateChanged('article_state', 'moderated');
		$deletionChange = $this->isStateChanged('article_state', 'deleted');
		$draftChange = $this->isStateChanged('article_state', 'draft');
		$awaitingPublishmentChange = $this->isStateChanged('article_state', 'awaiting');
		
		if ($this->isUpdate())
		{
			if ($visibilityChange == 'enter')
			{
				$this->articleMadeVisible();

				if ($approvalChange)
				{
					$this->submitHamData();
				}
			}
			else if ($visibilityChange == 'leave')
			{
				$this->articleHidden();
			}

			if ($this->isChanged('category_id'))
			{
				$oldCategory = $this->getExistingRelation('Category');
				if ($oldCategory && $this->Category)
				{
					$this->articleMoved($oldCategory, $this->Category);
				}
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
			if ($this->article_state == 'visible')
			{
				$this->articleInsertedVisible();
			}
			
			if ($this->article_state == 'draft')
			{
				$this->articleInsertedDraft();
			}
			
			if ($this->article_state == 'awaiting')
			{
				$this->articleInsertedAwaiting();
			}
		}

		if ($this->isUpdate())
		{
			if ($this->isChanged('user_id'))
			{
				$this->articleReassigned();
			}

			if ($this->isChanged('discussion_thread_id'))
			{
				if ($this->getExistingValue('discussion_thread_id'))
				{
					/** @var \XF\Entity\Thread $oldDiscussion */
					$oldDiscussion = $this->getExistingRelation('Discussion');
					if ($oldDiscussion && $oldDiscussion->discussion_type == 'ams_article')
					{
						// this will set it back to the forum default type
						$oldDiscussion->discussion_type = '';
						$oldDiscussion->save(false, false);
					}
				}
			
				if (
					$this->discussion_thread_id 
					&& $this->Discussion 
					&& $this->Discussion->discussion_type === \XF\ThreadType\AbstractHandler::BASIC_THREAD_TYPE
				)
				{
					$this->Discussion->discussion_type = 'ams_article';
					$this->Discussion->save(false, false);
				}
			}
			
			if ($this->discussion_thread_id)
			{
				$newThreadTitle = $this->getExpectedThreadTitle(true);
				if (
					$newThreadTitle != $this->getExpectedThreadTitle(false)
					&& $this->Discussion
					&& $this->Discussion->discussion_type == 'ams_article')
				{
					$this->Discussion->title = $newThreadTitle;
					$this->Discussion->saveIfChanged($saved, false, false);
				}
			}
		}

		if ($approvalChange == 'enter')
		{
			$approvalQueue = $this->getRelationOrDefault('ApprovalQueue', false);
			$approvalQueue->content_date = $this->publish_date;
			$approvalQueue->save();
		}
		else if ($deletionChange == 'enter' && !$this->DeletionLog)
		{
			$delLog = $this->getRelationOrDefault('DeletionLog', false);
			$delLog->setFromVisitor();
			$delLog->save();
		}

		$this->updateCategoryRecord();

		if ($this->isUpdate() && $this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorChanges('ams_article', $this);
		}
		
		$this->_postSaveBookmarks();
	}

	protected function articleMadeVisible()
	{
		$this->adjustUserArticleCountIfNeeded(1);

		if ($this->discussion_thread_id && $this->Discussion && $this->Discussion->discussion_type == 'ams_article')
		{
			$thread = $this->Discussion;

			switch ($this->app()->options()->xaAmsArticleDeleteThreadAction['action'])
			{
				case 'delete':
					$thread->discussion_state = 'visible';
					break;

				case 'close':
					$thread->discussion_open = true;
					break;
			}

			$thread->title = $this->getExpectedThreadTitle();
			$thread->saveIfChanged($saved, false, false);
		}
		
		/** @var \XF\Repository\Reaction $reactionRepo */
		$reactionRepo = $this->repository('XF:Reaction');
		$reactionRepo->recalculateReactionIsCounted('ams_article', $this->article_id);
		
		// TODO alert author that their article was approved? 
		
	}

	protected function articleHidden($hardDelete = false)
	{
		$this->adjustUserArticleCountIfNeeded(-1);

		if ($this->discussion_thread_id && $this->Discussion && $this->Discussion->discussion_type == 'ams_article')
		{
			$thread = $this->Discussion;

			switch ($this->app()->options()->xaAmsArticleDeleteThreadAction['action'])
			{
				case 'delete':
					$thread->discussion_state = 'deleted';
					break;

				case 'close':
					$thread->discussion_open = false;
					break;
			}
			
			if ($hardDelete)
			{
				$thread->discussion_type == 'discussion'; // unassociate from AMS and set as a discussion thread type!
			}

			$thread->title = $this->getExpectedThreadTitle();
			$thread->saveIfChanged($saved, false, false);
		}

		if (!$hardDelete)
		{
			// on hard delete the reactions will be removed which will do this
			/** @var \XF\Repository\Reaction $reactionRepo */
			$reactionRepo = $this->repository('XF:Reaction');
			$reactionRepo->recalculateReactionIsCounted('ams_article', $this->article_id, false);
		}

		// TODO testing this to see if this helps resolve the issue of cached Read Marking of deleted articles (at least for the viewing user that performs the delete action)
		/** @var \XenAddons\AMS\Repository\Article $articleRepo */
		$articleRepo = $this->repository('XenAddons\AMS:Article');
		$articleRepo->markArticleItemReadByVisitor($this);
		
		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = $this->repository('XF:UserAlert');
		$alertRepo->fastDeleteAlertsForContent('ams_article', $this->article_id);
		$alertRepo->fastDeleteAlertsForContent('ams_rating', $this->article_rating_ids);
	}

	protected function articleInsertedVisible()
	{
		$this->adjustUserArticleCountIfNeeded(1);
	}
	
	protected function articleInsertedDraft()
	{
		// TODO any actions needed to be performed when initially saving a AMS Draft Article!
	}
	
	protected function articleInsertedAwaiting()
	{
		// TODO any actions needed to be performed when initially saving a AMS Awating Publishing (delayed publishing) Article!
	}
	
	protected function submitHamData()
	{
		/** @var \XF\Spam\ContentChecker $submitter */
		$submitter = $this->app()->container('spam.contentHamSubmitter');
		$submitter->submitHam('ams_article', $this->article_id);
	}

	protected function articleMoved(Category $from, Category $to)
	{
	}

	protected function articleReassigned()
	{
		if ($this->article_state == 'visible')
		{
			$this->adjustUserArticleCountIfNeeded(-1, $this->getExistingValue('user_id'));
			$this->adjustUserArticleCountIfNeeded(1);
		}
	}

	protected function adjustUserArticleCountIfNeeded($amount, $userId = null)
	{
		if ($userId === null)
		{
			$userId = $this->user_id;
		}

		if ($userId)
		{
			$this->db()->query("
				UPDATE xf_user
				SET xa_ams_article_count = GREATEST(0, xa_ams_article_count + ?)
				WHERE user_id = ?
			", [$amount, $userId]);
		}
	}

	protected function updateCategoryRecord()
	{
		if (!$this->Category)
		{
			return;
		}

		$category = $this->Category;

		if ($this->isUpdate() && $this->isChanged('category_id'))
		{
			// moved, trumps the rest
			if ($this->article_state == 'visible')
			{
				$category->articleAdded($this);
				$category->save();
			}

			if ($this->getExistingValue('article_state') == 'visible')
			{
				/** @var Category $oldCategory */
				$oldCategory = $this->getExistingRelation('Category');
				if ($oldCategory)
				{
					$oldCategory->articleRemoved($this);
					$oldCategory->save();
				}
			}

			if ($this->discussion_thread_id && $this->Discussion && $this->Discussion->discussion_type == 'ams_article')
			{
				$thread = $this->Discussion;
				if ($category->thread_node_id)
				{
					$thread->node_id = $category->thread_node_id;
					$thread->prefix_id = $category->thread_prefix_id;
					if ($this->article_state == 'visible' && $thread->discussion_state == 'deleted')
					{
						// presumably the thread was soft deleted by being moved to a category without a thread
						$thread->discussion_state = 'visible';
					}
				}
				else
				{
					// this category doesn't have a thread
					$thread->discussion_state = 'deleted';
				}
				
				$thread->saveIfChanged($saved, false, false);
			}

			return;
		}

		// check for entering/leaving visible
		$visibilityChange = $this->isStateChanged('article_state', 'visible');
		if ($visibilityChange == 'enter' && $category)
		{
			$category->articleAdded($this);
			$category->save();
		}
		else if ($visibilityChange == 'leave' && $category)
		{
			$category->articleRemoved($this);
			$category->save();
		}
		else if ($this->isUpdate() && $this->article_state == 'visible')
		{
			$category->articleDataChanged($this);
			$category->save();
		}
	}

	protected function _postDelete()
	{
		if ($this->article_state == 'visible')
		{
			$this->articleHidden(true);
		}

		if ($this->Category && $this->article_state == 'visible')
		{
			$this->Category->articleRemoved($this);
			$this->Category->save();
		}

		if ($this->article_state == 'deleted' && $this->DeletionLog)
		{
			$this->DeletionLog->delete();
		}

		if ($this->article_state == 'moderated' && $this->ApprovalQueue)
		{
			$this->ApprovalQueue->delete();
		}
		
		if ($this->has_poll && $this->Poll)
		{
			$this->Poll->delete();
		}

		if ($this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorAction('ams_article', $this, 'delete_hard');
		}

		$db = $this->db();

		$db->delete('xf_xa_ams_article_feature', 'article_id = ?', $this->article_id);
		$db->delete('xf_xa_ams_article_field_value', 'article_id = ?', $this->article_id);
		$db->delete('xf_xa_ams_article_read', 'article_id = ?', $this->article_id);
		$db->delete('xf_xa_ams_article_reply_ban', 'article_id = ?', $this->article_id);
		$db->delete('xf_xa_ams_article_watch', 'article_id = ?', $this->article_id);
		
		$db->delete('xf_xa_ams_comment_read', 'article_id = ?', $this->article_id);
		
		$db->delete('xf_approval_queue', 'content_id = ? AND content_type = ?', [$this->article_id, 'ams_article']);
		$db->delete('xf_deletion_log', 'content_id = ? AND content_type = ?', [$this->article_id, 'ams_article']);
		$db->delete('xf_edit_history', 'content_id = ? AND content_type = ?', [$this->article_id, 'ams_article']);
		
		/** @var \XF\Repository\Attachment $attachRepo */
		$attachRepo = $this->repository('XF:Attachment');
		$attachRepo->fastDeleteContentAttachments('ams_article', $this->article_id);
		
		$this->_postDeleteComments();
		
		$ratingIds = $this->article_rating_ids;
		if ($ratingIds)
		{
			$this->_postDeleteRatings($ratingIds);
		}
		
		$pageIds = $this->article_page_ids;
		if ($pageIds)
		{
			$this->_postDeletePages($pageIds);
		}
		
		if ($this->series_part_id)
		{
			/** @var \XenAddons\AMS\Entity\SeriesPart $seriesPart */
			$seriesPart = $this->em()->find('XenAddons\AMS:SeriesPart', $this->series_part_id);
			if($seriesPart)
			{
				if($seriesPart->Series)
				{
					$seriesPart->Series->partRemoved($seriesPart);
					$seriesPart->Series->save();
				}
				
				$db->delete('xf_xa_ams_series_part', 'part_id = ?', $this->series_part_id);
			}
		}
		
		$this->_postDeleteBookmarks();
	}
	
	protected function _postDeleteRatings(array $ratingIds)
	{
		$db = $this->db();
		
		/** @var \XF\Repository\Attachment $attachRepo */
		$attachRepo = $this->repository('XF:Attachment');
		$attachRepo->fastDeleteContentAttachments('ams_rating', $ratingIds);

		/** @var \XF\Repository\Reaction $reactionRepo */
		$reactionRepo = $this->repository('XF:Reaction');
		$reactionRepo->fastDeleteReactions('ams_rating', $ratingIds);
		
		$db->delete('xf_xa_ams_article_rating', 'rating_id IN (' . $db->quote($ratingIds) . ')');
		$db->delete('xf_xa_ams_review_field_value', 'rating_id IN (' . $db->quote($ratingIds) . ')');
		
		$db->delete('xf_approval_queue', 'content_id IN (' . $db->quote($ratingIds) . ') AND content_type = ?', 'ams_rating');
		$db->delete('xf_deletion_log', 'content_id IN (' . $db->quote($ratingIds) . ') AND content_type = ?', 'ams_rating');
		$db->delete('xf_edit_history', 'content_id IN (' . $db->quote($ratingIds) . ') AND content_type = ?', 'ams_rating');
	}
	
	protected function _postDeletePages(array $pageIds)
	{
		$db = $this->db();
		
		/** @var \XF\Repository\Attachment $attachRepo */
		$attachRepo = $this->repository('XF:Attachment');
		$attachRepo->fastDeleteContentAttachments('ams_page', $pageIds);
		
		$db->delete('xf_xa_ams_article_page', 'page_id IN (' . $db->quote($pageIds) . ')');

		$db->delete('xf_deletion_log', 'content_id IN (' . $db->quote($pageIds) . ') AND content_type = ?', 'ams_page');
		$db->delete('xf_edit_history', 'content_id IN (' . $db->quote($pageIds) . ') AND content_type = ?', 'ams_page');
	}

	public function softDelete($reason = '', \XF\Entity\User $byUser = null)
	{
		$byUser = $byUser ?: \XF::visitor();

		if ($this->article_state == 'deleted')
		{
			return false;
		}

		$this->article_state = 'deleted';

		/** @var \XF\Entity\DeletionLog $deletionLog */
		$deletionLog = $this->getRelationOrDefault('DeletionLog');
		$deletionLog->setFromUser($byUser);
		$deletionLog->delete_reason = $reason;

		$this->save();

		return true;
	}
	
	public function getContentUrl(bool $canonical = false, array $extraParams = [], $hash = null)
	{
		$route = ($canonical ? 'canonical:' : '') . 'ams';
		return $this->app()->router('public')->buildLink($route, $this, $extraParams, $hash);
	}
	
	public function getContentPublicRoute()
	{
		return 'ams';
	}
	
	public function getContentTitle(string $context = '')
	{
		return \XF::phrase('xa_ams_article_x', [
			'title' => $this->title
		]);
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article';
		$structure->shortName = 'XenAddons\AMS:ArticleItem';
		$structure->primaryKey = 'article_id';
		$structure->contentType = 'ams_article';
		
		$structure->columns = [
			'article_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],  
			'category_id' => ['type' => self::UINT, 'required' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'username' => ['type' => self::STR, 'maxLength' => 50,
				'required' => 'please_enter_valid_name'
			],	
			'contributor_user_ids' => ['type' => self::LIST_COMMA, 'default' => [],
				'list' => ['type' => 'posint', 'unique' => true]
			],
			'title' => ['type' => self::STR, 'maxLength' => 150,
				'required' => 'please_enter_valid_title',
				'censor' => true
			],
			'description' => ['type' => self::STR, 'default' => '', 'maxLength' => 256, 'censor' => true],
			'meta_description' => ['type' => self::STR, 'default' => '', 'maxLength' => 320, 'censor' => true],
			'article_state' => ['type' => self::STR, 'default' => 'visible',
				'allowedValues' => ['visible', 'moderated', 'deleted', 'awaiting', 'draft']
			],
			'message' => ['type' => self::STR,
				'required' => 'please_enter_valid_message'
			],
			'publish_date' => ['type' => self::UINT, 'default' => \XF::$time],
			'publish_date_timezone' => ['type' => self::STR, 'maxLength' => 50, 'default' => 'Europe/London'],
			'last_update' => ['type' => self::UINT, 'default' => \XF::$time],
			'last_feature_date' => ['type' => self::UINT, 'default' => 0],
			'edit_date' => ['type' => self::UINT, 'default' => \XF::$time],
			'attach_count' => ['type' => self::UINT, 'default' => 0],
			'view_count' => ['type' => self::UINT, 'default' => 0],
			'page_count' => ['type' => self::UINT, 'default' => 0],
			'overview_page_title' => ['type' => self::STR, 'maxLength' => 150,
				'default' => '',
				'censor' => true
			],
			'overview_page_nav_title' => ['type' => self::STR, 'maxLength' => 150,
				'default' => '',
				'censor' => true
			],
			'rating_count' => ['type' => self::UINT, 'default' => 0, 'forced' => true],
			'rating_sum' => ['type' => self::UINT, 'default' => 0, 'forced' => true],
			'rating_avg' => ['type' => self::FLOAT, 'default' => 0],
			'rating_weighted' => ['type' => self::FLOAT, 'default' => 0],
			'review_count' => ['type' => self::UINT, 'default' => 0, 'forced' => true],
			'comment_count' => ['type' => self::UINT, 'default' => 0, 'forced' => true],
			'series_part_id' => ['type' => self::UINT, 'default' => 0],
			'cover_image_id' => ['type' => self::UINT, 'default' => 0],			
			'cover_image_above_article' => ['type' => self::BOOL, 'default' => false],
			'discussion_thread_id' => ['type' => self::UINT, 'default' => 0],	
			'custom_fields' => ['type' => self::JSON_ARRAY, 'default' => []],
			'prefix_id' => ['type' => self::UINT, 'default' => 0],
			'last_review_date' => ['type' => self::UINT, 'default' => 0],
			'about_author' => ['type' => self::BOOL, 'default' => false],
			'author_rating' => ['type' => self::FLOAT, 'default' => 0],
			'author_suggested_ids' => ['type' => self::LIST_COMMA, 'default' => []],
			'tags' => ['type' => self::JSON_ARRAY, 'default' => []],
			'comments_open' => ['type' => self::BOOL, 'default' => true],
			'ratings_open' => ['type' => self::BOOL, 'default' => true],
			'last_edit_date' => ['type' => self::UINT, 'default' => 0],
			'last_edit_user_id' => ['type' => self::UINT, 'default' => 0],
			'edit_count' => ['type' => self::UINT, 'default' => 0],
			'original_source' => ['type' => self::JSON_ARRAY, 'nullable' => true, 'default' => null],
			'location' => ['type' => self::STR, 'default' => '', 'maxLength' => 255],
			'has_poll' => ['type' => self::BOOL, 'default' => false],
			'warning_id' => ['type' => self::UINT, 'default' => 0],
			'warning_message' => ['type' => self::STR, 'default' => '', 'maxLength' => 255],
			'ip_id' => ['type' => self::UINT, 'default' => 0],
			'embed_metadata' => ['type' => self::JSON_ARRAY, 'nullable' => true, 'default' => null]
		];
		$structure->getters = [
			'custom_fields' => true,
			'real_comment_count' => true,
			'real_review_count' => true,
			'image_attach_count' => true,
			'article_rating_ids' => true,
			'article_page_ids' => true,
			'draft_article' => true,
		];
		$structure->behaviors = [
			'XF:Reactable' => ['stateField' => 'article_state'],
			'XF:ReactableContainer' => [
				'childContentType' => 'ams_comment',
				'childIds' => function($articleItem) { return $articleItem->comment_ids; },
				'stateField' => 'article_state'
			],
			'XF:Taggable' => ['stateField' => 'article_state'],
			'XF:Indexable' => [
				'checkForUpdates' => ['title', 'message', 'category_id', 'user_id', 'prefix_id', 'tags', 'article_state']
			],
			'XF:NewsFeedPublishable' => [
				'usernameField' => 'username',
				'dateField' => 'publish_date'
			],
			'XF:CustomFieldsHolder' => [
				'valueTable' => 'xf_xa_ams_article_field_value',
				'checkForUpdates' => ['category_id'],
				'getAllowedFields' => function($article) { return $article->Category ? $article->Category->field_cache : []; }
			],
			'XF:ContentVotableContainer' => [
				'childContentType' => 'ams_rating',
				'childIds' => function($article) { return $article->article_rating_ids; },
				'stateField' => 'article_state'
			],
		];
		$structure->relations = [
			'Category' => [
				'entity' => 'XenAddons\AMS:Category',
				'type' => self::TO_ONE,
				'conditions' => 'category_id',
				'primary' => true
			],
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
			'Contributors' => [
				'entity' => 'XenAddons\AMS:ArticleContributor',
				'type' => self::TO_MANY,
				'conditions' => 'article_id',
				'key' => 'user_id',
				'with' => ['User','User.Profile'],
				'order' => 'User.username'
			],
			'Ratings' => [
				'entity' => 'XenAddons\AMS:ArticleRating',
				'type' => self::TO_MANY,
				'conditions' => 'article_id',
				'key' => 'user_id'
			],
			'CoverImage' => [
				'entity' => 'XF:Attachment',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_article'],
					['content_id', '=', '$article_id'],
					['attachment_id', '=', '$cover_image_id']
				],
				'with' => 'Data',
				'order' => 'attach_date'
			],			
			'Attachments' => [
				'entity' => 'XF:Attachment',
				'type' => self::TO_MANY,
				'conditions' => [
					['content_type', '=', 'ams_article'],
					['content_id', '=', '$article_id']
				],
				'with' => 'Data',
				'order' => 'attach_date'
			],
			'Read' => [
				'entity' => 'XenAddons\AMS:ArticleRead',
				'type' => self::TO_MANY,
				'conditions' => 'article_id',
				'key' => 'user_id'
			],
			'Discussion' => [
				'entity' => 'XF:Thread',
				'type' => self::TO_ONE,
				'conditions' => [['thread_id', '=', '$discussion_thread_id']],
				'primary' => true
			],
			'Featured' => [
				'entity' => 'XenAddons\AMS:ArticleFeature',
				'type' => self::TO_ONE,
				'conditions' => 'article_id',
				'primary' => true
			],
			'SeriesPart' => [
				'entity' => 'XenAddons\AMS:SeriesPart',
				'type' => self::TO_ONE,
				'conditions' => [['part_id', '=', '$series_part_id']],
				'primary' => true
			],			
			'Prefix' => [
				'entity' => 'XenAddons\AMS:ArticlePrefix',
				'type' => self::TO_ONE,
				'conditions' => 'prefix_id',
				'primary' => true
			],
			'Watch' => [
				'entity' => 'XenAddons\AMS:ArticleWatch',
				'type' => self::TO_MANY,
				'conditions' => 'article_id',
				'key' => 'user_id'
			],
			'ReplyBans' => [
				'entity' => 'XenAddons\AMS:ArticleReplyBan',
				'type' => self::TO_MANY,
				'conditions' => 'article_id',
				'key' => 'user_id'
			],
			'Poll' => [
				'entity' => 'XF:Poll',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_article'],
					['content_id', '=', '$article_id']
				]
			],
			'DeletionLog' => [
				'entity' => 'XF:DeletionLog',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_article'],
					['content_id', '=', '$article_id']
				],
				'primary' => true
			],
			'ApprovalQueue' => [
				'entity' => 'XF:ApprovalQueue',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_article'],
					['content_id', '=', '$article_id']
				],
				'primary' => true
			],
			'DraftArticles' => [
				'entity'     => 'XF:Draft',
				'type'       => self::TO_MANY,
				'conditions' => [
					['draft_key', '=', 'xa-ams-article-', '$article_id']
				],
				'key' => 'user_id'
			],
			'CustomFields' => [
				'entity' => 'XenAddons\AMS:ArticleFieldValue',
				'type' => self::TO_MANY,
				'conditions' => 'article_id',
				'key' => 'field_id'
			]
		];
		$structure->defaultWith = [
			'User'
		];
		$structure->options = [
			'log_moderator' => true
		];
		$structure->withAliases = [
			'full' => [
				'User',
				'Featured',
				'CoverImage',
				function()
				{
					$userId = \XF::visitor()->user_id;
					if ($userId)
					{
						return [
							'Read|' . $userId, 
							'Watch|' . $userId,
							'Reactions|' . $userId,
							'Bookmarks|' . $userId
						];
					}
				
					return null;
				}
			],
			'fullCategory' => [
				'full',
				function()
				{
					$with = ['Category'];
				
					$userId = \XF::visitor()->user_id;
					if ($userId)
					{
						$with[] = 'Category.Watch|' . $userId;
					}
				
					return $with;
				}
			]
		];		
		
		static::addCommentableStructureElements($structure);
		static::addReactableStructureElements($structure);
		static::addBookmarkableStructureElements($structure);
		
		return $structure;
	}
}