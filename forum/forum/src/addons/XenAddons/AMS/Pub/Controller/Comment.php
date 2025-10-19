<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class Comment extends AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		$content = $comment->Content;

		$commentRepo = $this->getCommentRepo();

		$commentList = $commentRepo->findCommentsForContent($content)
			->where('comment_id', '<', $comment->comment_id)
			->fetch();
		$commentList = $commentList->filterViewable();
		$commentsBefore = $commentList->count();

		$perPage = $this->options()->xaAmsCommentsPerPage;
		$page = floor($commentsBefore / $perPage) + 1;

		$link = 'ams';
		
		if ($page > 1)
		{
			$params = [
				'page' => $page  // comment_page
			];
		}
		else
		{
			$params = [];
		}

		return $this->redirectPermanently(
			$this->buildLink($link, $content, $params) . '#ams-comment-' . $comment->comment_id
		);
	}

	public function actionShow(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		$content = $comment->Content;

		$viewParams = [
			'comment' => $comment,
			'content' => $content,
			'canInlineMod' => $comment->canUseInlineModeration()
		];
		return $this->view('XenAddons\AMS:Comment\Show', 'xa_ams_comment_show', $viewParams);
	}

	/**
	 * @param \XenAddons\AMS\Entity\Comment $comment
	 *
	 * @return \XenAddons\AMS\Service\Comment\Editor
	 */
	protected function setupCommentEdit(\XenAddons\AMS\Entity\Comment $comment)
	{
		$message = $this->plugin('XF:Editor')->fromInput('message');

		/** @var \XenAddons\AMS\Service\Comment\Editor $editor */
		$editor = $this->service('XenAddons\AMS:Comment\Editor', $comment);
		if ($comment->canEditSilently())
		{
			$silentEdit = $this->filter('silent', 'bool');
			if ($silentEdit)
			{
				$editor->logEdit(false);
				if ($this->filter('clear_edit', 'bool'))
				{
					$comment->last_edit_date = 0;
				}
			}
		}
		$editor->setMessage($message);

		if ($this->filter('author_alert', 'bool') && $comment->canSendModeratorActionAlert())
		{
			$editor->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
		}
		

		if ($comment->Article->Category->canUploadAndManageCommentImages())
		{
			$editor->setAttachmentHash($this->filter('attachment_hash', 'str'));
		}

		return $editor;
	}

	protected function finalizeCommentEdit(\XenAddons\AMS\Service\Comment\Editor $editor) {}

	public function actionEdit(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		if (!$comment->canEdit($error))
		{
			return $this->noPermission($error);
		}
		
		$category = $comment->Content->Category;

		if ($this->isPost())
		{
			$editor = $this->setupCommentEdit($comment);
			$editor->checkForSpam();

			if (!$editor->validate($errors))
			{
				return $this->error($errors);
			}
			$editor->save();

			$this->finalizeCommentEdit($editor);

			if ($this->filter('_xfWithData', 'bool'))
			{
				$content = $comment->Content;

				$viewParams = [
					'comment' => $comment,
					'content' => $content
				];
				$reply = $this->view('XenAddons\AMS:Comment\EditNewComment', 'xa_ams_comment_edit_new_comment', $viewParams);
				$reply->setJsonParam('message', \XF::phrase('redirect_changes_saved_successfully'));
				return $reply;
			}
			else
			{
				return $this->redirect($this->buildLink('ams/comments', $comment));
			}
		}
		else
		{
			if ($category && $category->canUploadAndManageCommentImages())
			{
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = $this->repository('XF:Attachment');
				$attachmentData = $attachmentRepo->getEditorData('ams_comment', $comment);
			}
			else
			{
				$attachmentData = null;
			}
			
			$viewParams = [
				'comment' => $comment,
				'content' => $comment->Content,
				'category' => $comment->Content->Category,
				'attachmentData' => $attachmentData,
				'quickEdit' => $this->filter('_xfWithData', 'bool')
			];
			return $this->view('XenAddons\AMS:Comment\Edit', 'xa_ams_comment_edit', $viewParams);
		}
	}
	
	public function actionEditPreview(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		$comment = $this->assertViewableComment($params->comment_id);
	
		$commentEditor = $this->setupCommentEdit($comment);
		if (!$commentEditor->validate($errors))
		{
			return $this->error($errors);
		}
	
		$comment = $commentEditor->getComment();
	
		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');
	
		if ($comment->Article->Category && $comment->Article->Category->canUploadAndManageCommentImages())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_comment', $comment, $tempHash);
			$attachments = $attachmentData['attachments'];
		}
	
		return $this->plugin('XF:BbCodePreview')->actionPreview(
			$comment->message, 'ams_comment', $comment->User, $attachments, $comment->Article->canViewCommentImages()
		);
	}	

	public function actionChangeDate(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		if (!$comment->canChangeDate($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			// TODO probably move this process into a service in a future version!
	
			$commentDateInput = $this->filter([
				'comment_date' => 'str',
				'comment_hour' => 'int',
				'comment_minute' => 'int',
				'comment_timezone' => 'str'
			]);
	
			$tz = new \DateTimeZone($commentDateInput['comment_timezone']);
	
			$commentDate = $commentDateInput['comment_date'];
			$commentHour = $commentDateInput['comment_hour'];
			$commentMinute = $commentDateInput['comment_minute'];
			$commentDate = new \DateTime("$commentDate $commentHour:$commentMinute", $tz);
			$commentDate = $commentDate->format('U');
	
			if ($commentDate < $comment->Content->publish_date)
			{
				return $this->error(\XF::phraseDeferred('xa_ams_can_not_set_comemnt_date_older_than_article_publish_date'));
			}
	
			if ($commentDate > \XF::$time)
			{
				return $this->error(\XF::phraseDeferred('xa_ams_can_not_change_date_into_the_future'));
			}
	
			$comment->comment_date = $commentDate;
			$comment->save();
	
			return $this->redirect($this->buildLink('ams/comments', $comment));
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
	
			$commentDate = new \DateTime('@' . $comment->comment_date);
			$commentDate->setTimezone(new \DateTimeZone($visitor->timezone));
			$commentHour = $commentDate->format('H');
			$commentMinute = $commentDate->format('i');
	
			$viewParams = [
				'comment' => $comment,
				'content' => $comment->Content,

				'commentDate' => $commentDate,
				'commentHour' => $commentHour,
				'commentMinute' => $commentMinute,

				'hours' => $hours,
				'minutes' => $minutes,
				'timeZones' => $timeZones,
			];
			return $this->view('XenAddons\AMS:Comment\ChangeDate', 'xa_ams_comment_change_date', $viewParams);
		}
	}
		
	public function actionReassign(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		if (!$comment->canReassign($error))
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
	
			$canTargetView = \XF::asVisitor($user, function() use ($comment)
			{
				return $comment->canView();
			});
			if (!$canTargetView)
			{
				return $this->error(\XF::phrase('xa_ams_new_owner_must_be_able_to_view_this_comment'));
			}
	
			/** @var \XenAddons\AMS\Service\Comment\Reassign $reassigner */
			$reassigner = $this->service('XenAddons\AMS:Comment\Reassign', $comment);
	
			if ($this->filter('alert', 'bool'))
			{
				$reassigner->setSendAlert(true, $this->filter('alert_reason', 'str'));
			}
	
			$reassigner->reassignTo($user);
	
			return $this->redirect($this->buildLink('ams/comments', $comment));
		}
		else
		{
			$viewParams = [
				'comment' => $comment,
				'content' => $comment->Content,
				'category' => $comment->Content->Category
			];
			return $this->view('XenAddons\AMS:Comment\Reassign', 'xa_ams_comment_reassign', $viewParams);
		}
	}
	
	public function actionDelete(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		if (!$comment->canDelete('soft', $error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			$type = $this->filter('hard_delete', 'bool') ? 'hard' : 'soft';
			$reason = $this->filter('reason', 'str');

			if (!$comment->canDelete($type, $error))
			{
				return $this->noPermission($error);
			}

			/** @var \XenAddons\AMS\Service\Comment\Deleter $deleter */
			$deleter = $this->service('XenAddons\AMS:Comment\Deleter', $comment);

			if ($this->filter('author_alert', 'bool') && $comment->canSendModeratorActionAlert())
			{
				$deleter->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
			}

			$deleter->delete($type, $reason);

			$this->plugin('XF:InlineMod')->clearIdFromCookie('ams_comment', $comment->comment_id);

			return $this->redirect(
				$this->buildLink('ams', $comment->Content)
			);
		}
		else
		{
			$viewParams = [
				'comment' => $comment,
				'content' => $comment->Content
			];
			return $this->view('XenAddons\AMS:Comment\Delete', 'xa_ams_comment_delete', $viewParams);
		}
	}

	public function actionIp(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		$breadcrumbs = $comment->Content->getBreadcrumbs();

		/** @var \XF\ControllerPlugin\Ip $ipPlugin */
		$ipPlugin = $this->plugin('XF:Ip');
		return $ipPlugin->actionIp($comment, $breadcrumbs);
	}

	public function actionReport(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		if (!$comment->canReport($error))
		{
			return $this->noPermission($error);
		}

		/** @var \XF\ControllerPlugin\Report $reportPlugin */
		$reportPlugin = $this->plugin('XF:Report');
		return $reportPlugin->actionReport(
			'ams_comment', $comment,
			$this->buildLink('ams/comments/report', $comment),
			$this->buildLink('ams/comments', $comment)
		);
	}

	public function actionQuote(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
		return $this->plugin('XF:Quote')->actionQuote($comment, 'ams_comment');
	}

	public function actionHistory(ParameterBag $params)
	{
		return $this->rerouteController('XF:EditHistory', 'index', [
			'content_type' => 'ams_comment',
			'content_id' => $params->comment_id
		]);
	}

	public function actionReact(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactSimple($comment, 'ams/comments');
	}
	
	public function actionReactions(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);
	
		$breadcrumbs = $comment->Content->getBreadcrumbs();
		$title = \XF::phrase('xa_ams_members_who_have_reacted_to_comment_by_x', ['user' => $comment->username]);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactions(
			$comment,
			'ams/comments/reactions',
			$title, $breadcrumbs
		);
	}

	public function actionWarn(ParameterBag $params)
	{
		$comment = $this->assertViewableComment($params->comment_id);

		if (!$comment->canWarn($error))
		{
			return $this->noPermission($error);
		}

		$breadcrumbs = $comment->Content->getBreadcrumbs();

		/** @var \XF\ControllerPlugin\Warn $warnPlugin */
		$warnPlugin = $this->plugin('XF:Warn');
		return $warnPlugin->actionWarn(
			'ams_comment', $comment,
			$this->buildLink('ams/comments/warn', $comment),
			$breadcrumbs
		);
	}
}