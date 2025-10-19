<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class ArticleReview extends AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);

		return $this->redirectToReview($review);
	}
	
	/**
	 * @param \XenAddons\AMS\Entity\ArticleRating $rating
	 *
	 * @return \XenAddons\AMS\Service\Review\Edit
	 */
	protected function setupReviewEdit(\XenAddons\AMS\Entity\ArticleRating $rating)
	{
		/** @var \XenAddons\AMS\Service\Review\Edit $editor */
		$editor = $this->service('XenAddons\AMS:Review\Edit', $rating);
	
		if ($rating->canEditSilently())
		{
			$silentEdit = $this->filter('silent', 'bool');
			if ($silentEdit)
			{
				$editor->logEdit(false);
				if ($this->filter('clear_edit', 'bool'))
				{
					$rating->last_edit_date = 0;
				}
			}
		}
		
		$editor->setMessage($this->plugin('XF:Editor')->fromInput('message'));
	
		$input = $this->filter([
			'rating' => 'uint',
			'pros' => 'str',
			'cons' => 'str',
		]);
	
		$editor->setRating($input['rating'], $input['pros'], $input['cons']);
	
		$customFields = $this->filter('custom_fields', 'array');
		$editor->setCustomFields($customFields);
		
		if ($this->filter('author_alert', 'bool') && $rating->canSendModeratorActionAlert())
		{
			$editor->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
		}
		
		if ($rating->Article->Category->canUploadAndManageReviewImages())
		{
			$editor->setAttachmentHash($this->filter('attachment_hash', 'str'));
		}
		
		return $editor;
	}	
	
	public function actionEdit(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
		if (!$review->canEdit($error))
		{
			return $this->noPermission($error);
		}	
		
		$category = $review->Article->Category;

		if ($this->isPost())
		{
			$editor = $this->setupReviewEdit($review);
			
			if (!$editor->validate($errors))
			{
				return $this->error($errors);
			}
			
			$review = $editor->save();
			
			if ($this->filter('_xfWithData', 'bool') && $this->filter('_xfInlineEdit', 'bool'))
			{
				$viewParams = [
					'review' => $review,
					'article' => $review->Article,
					'category' => $review->Article->Category,
				];
				$reply = $this->view('XenAddons\AMS:ArticleReview\EditNewReview', 'xa_ams_review_edit_new_review', $viewParams);
				$reply->setJsonParams([
					'message' => \XF::phrase('your_changes_have_been_saved')
				]);
				return $reply;
			}
			else
			{
				return $this->redirectToReview($review);
			}		
		}
		else
		{
			if ($category && $category->canUploadAndManageReviewImages())
			{
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = $this->repository('XF:Attachment');
				$attachmentData = $attachmentRepo->getEditorData('ams_rating', $review);
			}
			else
			{
				$attachmentData = null;
			}
			
			$viewParams = [
				'review' => $review,
				'article' => $review->Article,
				'category' => $review->Article->Category,
				'attachmentData' => $attachmentData,
				'quickEdit' => $this->filter('_xfWithData', 'bool')
			];
			return $this->view('XenAddons\AMS:Review\Edit', 'xa_ams_review_edit', $viewParams);
		}	
	}
	
	public function actionPreview(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		$review = $this->assertViewableReview($params->rating_id);
	
		$reviewEditor = $this->setupReviewEdit($review);
		if (!$reviewEditor->validate($errors))
		{
			return $this->error($errors);
		}
	
		$review = $reviewEditor->getRating();
	
		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');
	
		if ($review->Article->Category && $review->Article->Category->canUploadAndManageReviewImages())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_rating', $review, $tempHash);
			$attachments = $attachmentData['attachments'];
		}
	
		return $this->plugin('XF:BbCodePreview')->actionPreview(
			$review->message, 'ams_rating', $review->User, $attachments, $review->Article->canViewReviewImages()
		);
	}

	public function actionChangeDate(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
		if (!$review->canChangeDate($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			// TODO probably move this process into a service in a future version!
	
			$reviewDateInput = $this->filter([
				'review_date' => 'str',
				'review_hour' => 'int',
				'review_minute' => 'int',
				'review_timezone' => 'str'
			]);
	
			$tz = new \DateTimeZone($reviewDateInput['review_timezone']);
	
			$reviewDate = $reviewDateInput['review_date'];
			$reviewHour = $reviewDateInput['review_hour'];
			$reviewMinute = $reviewDateInput['review_minute'];
			$reviewDate = new \DateTime("$reviewDate $reviewHour:$reviewMinute", $tz);
			$reviewDate = $reviewDate->format('U');
	
			if ($reviewDate < $review->Article->publish_date)
			{
				return $this->error(\XF::phraseDeferred('xa_ams_can_not_set_review_date_older_than_article_publish_date'));
			}
				
			if ($reviewDate > \XF::$time)
			{
				return $this->error(\XF::phraseDeferred('xa_ams_can_not_change_date_into_the_future'));
			}
	
			$review->rating_date = $reviewDate;
			$review->save();
	
			return $this->redirect($this->buildLink('ams/review', $review));
		}
		else
		{
			$hours = [];
			for ($i = 0; $i < 24; $i++)
			{
				$hh = str_pad($i, 2, '0', STR_PAD_LEFT);
				$hours[$hh] = $hh;
			}
	
			$minutes = [];
			for ($i = 0; $i < 60; $i += 5)
			{
				$mm = str_pad($i, 2, '0', STR_PAD_LEFT);
				$minutes[$mm] = $mm;
			}
	
			/** @var \XF\Data\TimeZone $tzData */
			$tzData = $this->app->data('XF:TimeZone');
			$timeZones = $tzData->getTimeZoneOptions();
	
			$visitor = \XF::visitor();
	
			$reviewDate = new \DateTime('@' . $review->rating_date);
			$reviewDate->setTimezone(new \DateTimeZone($visitor->timezone));
			$reviewHour = $reviewDate->format('H');
			$reviewMinute = $reviewDate->format('i');
	
			$viewParams = [
				'review' => $review,
				'article' => $review->Article,
	
				'reviewDate' => $reviewDate,
				'reviewHour' => $reviewHour,
				'reviewMinute' => $reviewMinute,
	
				'hours' => $hours,
				'minutes' => $minutes,
				'timeZones' => $timeZones,
			];
			return $this->view('XenAddons\AMS:Review\ChangeDate', 'xa_ams_review_change_date', $viewParams);
		}
	}
		
	public function actionReassign(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
		if (!$review->canReassign($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$user = $this->em()->findOne('XF:User', ['username' => $this->filter('username', 'str')]);
			if (!$user)
			{
				return $this->error(\XF::phrase('requested_user_not_found'));
			}
	
			$canTargetView = \XF::asVisitor($user, function() use ($review)
			{
				return $review->canView();
			});
			if (!$canTargetView)
			{
				return $this->error(\XF::phrase('xa_ams_new_owner_must_be_able_to_view_this_review'));
			}
	
			/** @var \XenAddons\AMS\Service\Review\Reassign $reassigner */
			$reassigner = $this->service('XenAddons\AMS:Review\Reassign', $review);
	
			if ($this->filter('alert', 'bool'))
			{
				$reassigner->setSendAlert(true, $this->filter('alert_reason', 'str'));
			}
	
			$reassigner->reassignTo($user);
	
			return $this->redirect($this->buildLink('ams/review', $review));
		}
		else
		{
			$viewParams = [
				'review' => $review,
				'article' => $review->Article,
				'category' => $review->Article->Category
			];
			return $this->view('XenAddons\AMS:Review\Reassign', 'xa_ams_review_reassign', $viewParams);
		}
	}
	
	public function actionDelete(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
		if (!$review->canDelete('soft', $error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			$type = $this->filter('hard_delete', 'bool') ? 'hard' : 'soft';
			$reason = $this->filter('reason', 'str');

			if (!$review->canDelete($type, $error))
			{
				return $this->noPermission($error);
			}

			/** @var \XenAddons\AMS\Service\Review\Deleter $deleter */
			$deleter = $this->service('XenAddons\AMS:Review\Deleter', $review);

			if ($this->filter('author_alert', 'bool') && $review->canSendModeratorActionAlert())
			{
				$deleter->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
			}

			$deleter->delete($type, $reason);

			return $this->redirect(
				$this->getDynamicRedirect($this->buildLink('ams', $review->Article), false)
			);
		}
		else
		{
			$viewParams = [
				'review' => $review,
				'article' => $review->Article
			];
			return $this->view('XenAddons\AMS:ArticleReview\Delete', 'xa_ams_review_delete', $viewParams);
		}
	}

	public function actionUndelete(ParameterBag $params)
	{
		$this->assertValidCsrfToken($this->filter('t', 'str'));

		$review = $this->assertViewableReview($params->rating_id);
		if (!$review->canUndelete($error))
		{
			return $this->noPermission($error);
		}

		if ($review->rating_state == 'deleted')
		{
			$review->rating_state = 'visible';
			$review->save();
		}

		return $this->redirect($this->buildLink('ams/review', $review));
	}
	
	public function actionIp(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
		
		$article = $review->Article;
		$breadcrumbs = $article->Category->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Ip $ipPlugin */
		$ipPlugin = $this->plugin('XF:Ip');
		return $ipPlugin->actionIp($review, $breadcrumbs);
	}	

	public function actionReport(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
		if (!$review->canReport($error))
		{
			return $this->noPermission($error);
		}

		/** @var \XF\ControllerPlugin\Report $reportPlugin */
		$reportPlugin = $this->plugin('XF:Report');
		return $reportPlugin->actionReport(
			'ams_rating', $review,
			$this->buildLink('ams/review/report', $review),
			$this->buildLink('ams/review', $review)
		);
	}
	
	public function actionHistory(ParameterBag $params)
	{
		return $this->rerouteController('XF:EditHistory', 'index', [
			'content_type' => 'ams_rating',
			'content_id' => $params->rating_id
		]);
	}
	
	public function actionReact(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactSimple($review, 'ams/review');
	}
	
	public function actionReactions(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
	
		$breadcrumbs = $review->Content->getBreadcrumbs();
		$title = \XF::phrase('xa_ams_members_who_have_reacted_to_review_by_x', ['user' => $review->username]);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactions(
			$review,
			'ams/review/reactions',
			$title, $breadcrumbs
		);
	}	
	
	public function actionVote(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
	
		/** @var \XF\ControllerPlugin\ContentVote $votePlugin */
		$votePlugin = $this->plugin('XF:ContentVote');
	
		return $votePlugin->actionVote(
			$review,
			$this->buildLink('ams/review', $review),
			$this->buildLink('ams/review/vote', $review)
		);
	}

	public function actionWarn(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);

		if (!$review->canWarn($error))
		{
			return $this->noPermission($error);
		}

		$article = $review->Article;
		$breadcrumbs = $article->Category->getBreadcrumbs();

		/** @var \XF\ControllerPlugin\Warn $warnPlugin */
		$warnPlugin = $this->plugin('XF:Warn');
		return $warnPlugin->actionWarn(
			'ams_rating', $review,
			$this->buildLink('ams/review/warn', $review),
			$breadcrumbs
		);
	}

	public function actionReply(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);

		if (!$review->canReply($error))
		{
			return $this->noPermission($error);
		}

		/** @var \XenAddons\AMS\Service\Review\AuthorReply $authorReplier */
		$authorReplier = $this->service('XenAddons\AMS:Review\AuthorReply', $review);

		$message = $this->filter('message', 'str');
		if (!$authorReplier->reply($message, $error))
		{
			return $this->error($error);
		}

		if ($this->filter('_xfWithData', 'bool'))
		{
			$viewParams = [
				'review' => $review,
				'article' => $review->Article
			];
			return $this->view('XenAddons\AMS:Review\ReplyAdded', 'xa_ams_review_reply_added', $viewParams);
		}
		else
		{
			return $this->redirect($this->buildLink('ams/review', $review));
		}
	}

	public function actionReplyDelete(ParameterBag $params)
	{
		$review = $this->assertViewableReview($params->rating_id);
		if (!$review->canDeleteAuthorResponse($error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			/** @var \XenAddons\AMS\Service\Review\AuthorReplyDelete $deleter */
			$deleter = $this->service('XenAddons\AMS:Review\AuthorReplyDelete', $review);
			$deleter->delete();

			return $this->redirect(
				$this->getDynamicRedirect($this->buildLink('ams/review', $review), false)
			);
		}
		else
		{
			$viewParams = [
				'review' => $review,
				'article' => $review->Article
			];
			return $this->view('XenAddons\AMS:Review\ReplyDelete', 'xa_ams_review_reply_delete', $viewParams);
		}
	}
	
	public function actionDeleteRating(ParameterBag $params)
	{
		$rating = $this->assertViewableRating($params->rating_id);
		if (!$rating->canDelete('hard', $error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$type = 'hard';
			$reason = $this->filter('reason', 'str');
	
			if (!$rating->canDelete($type, $error))
			{
				return $this->noPermission($error);
			}
	
			/** @var \XenAddons\AMS\Service\Review\Deleter $deleter */
			$deleter = $this->service('XenAddons\AMS:Review\Deleter', $rating);
	
			if ($this->filter('author_alert', 'bool') && $rating->canSendModeratorActionAlert())
			{
				$deleter->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
			}
	
			$deleter->delete($type, $reason);
	
			return $this->redirect(
				$this->getDynamicRedirect($this->buildLink('ams', $rating->Article), false)
			);
		}
		else
		{
			$viewParams = [
				'rating' => $rating,
				'article' => $rating->Article
			];
			return $this->view('XenAddons\AMS:ArticleRating\Delete', 'xa_ams_rating_delete', $viewParams);
		}
	}	

	protected function redirectToReview(\XenAddons\AMS\Entity\ArticleRating $review)
	{
		$article = $review->Article;

		$newerFinder = $this->getRatingRepo()->findReviewsInArticle($article);
		$newerFinder->where('rating_date', '>', $review->rating_date);
		$totalNewer = $newerFinder->total();

		$perPage = $this->options()->xaAmsReviewsPerPage;
		$page = ceil(($totalNewer + 1) / $perPage);

		if ($page > 1)
		{
			$params = ['page' => $page];
		}
		else
		{
			$params = [];
		}

		return $this->redirect(
			$this->buildLink('ams/reviews', $article, $params)
			. '#review-' . $review->rating_id
		);
	}

	public static function getActivityDetails(array $activities)
	{
		return \XF::phrase('xa_ams_viewing_articles');
	}
}