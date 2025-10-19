<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class Series extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
	
		if (!$visitor->canViewAmsArticles($error))
		{
			throw $this->exception($this->noPermission($error));
		}
		
		if (!$visitor->canViewAmsSeries($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		if ($this->options()->xaAmsOverrideStyle)
		{
			$this->setViewOption('style_id', $this->options()->xaAmsOverrideStyle);
		}
	}
	
	public function actionIndex(ParameterBag $params)
	{
		if ($params->series_id)
		{
			return $this->rerouteController(__CLASS__, 'view', $params);
		}

		/** @var \XenAddons\AMS\ControllerPlugin\SeriesList $seriesListPlugin */
		$seriesListPlugin = $this->plugin('XenAddons\AMS:SeriesList');

		$listParams = $seriesListPlugin->getSeriesListData();
		
		$this->assertValidPage($listParams['page'], $listParams['perPage'], $listParams['total'], 'ams/series');
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/series', null, $listParams['page']));

		$viewParams = $listParams;

		return $this->view('XenAddons\AMS\Series:Index', 'xa_ams_series_index', $viewParams);
	}
	
	public function actionFilters()
	{
		/** @var \XenAddons\AMS\ControllerPlugin\Series $seriesListPlugin */
		$seriesListPlugin = $this->plugin('XenAddons\AMS:SeriesList');
	
		return $seriesListPlugin->actionFilters();
	}
	
	public function actionFeatured()
	{
		/** @var \XenAddons\AMS\ControllerPlugin\Series $seriesListPlugin */
		$seriesListPlugin = $this->plugin('XenAddons\AMS:SeriesList');
	
		return $seriesListPlugin->actionFeatured();
	}	
	
	/**
	 * @param \XenAddons\AMS\Entity\SeriesItem $series
	 *
	 * @return \XenAddons\AMS\Service\Series\Create
	 */
	protected function setupSeriesCreate(\XenAddons\AMS\Entity\SeriesItem $series)
	{
		/** @var \XenAddons\AMS\Service\Series\Create $seriesCreator */
		$seriesCreator = $this->service('XenAddons\AMS:Series\Create', $series);
	
		$seriesCreator->setTitle($this->filter('title', 'str'));
		$seriesCreator->setMessage($this->plugin('XF:Editor')->fromInput('message'));
		$seriesCreator->setDescription($this->filter('description', 'str'));
		
		if ($series->canUploadAndManageSeriesAttachments())
		{
			$seriesCreator->setSeriesAttachmentHash($this->filter('attachment_hash', 'str'));
		}
		
		$basicFields = $this->filter([
			'community_series' => 'bool'
		]);
		$seriesCreator->getSeries()->bulkSet($basicFields);
		
		return $seriesCreator;
	}		
	
	protected function finalizeSeriesCreate(\XenAddons\AMS\Service\Series\Create $creator)
	{
		$creator->sendNotifications();
	
		$series = $creator->getSeries();
	
		if (\XF::visitor()->user_id)
		{
			if ($series->series_state == 'moderated')
			{
				$this->session()->setHasContentPendingApproval();
			}
		}
	}
	
	public function actionCreateSeries(ParameterBag $params)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		
		if (!$visitor->canCreateAmsSeries($error))
		{
			return $this->noPermission($error);
		}
		
		$series = $this->em()->create('XenAddons\AMS:SeriesItem');
		
		if ($this->isPost())
		{
			$seriesCreator = $this->setupSeriesCreate($series);
			$seriesCreator->checkForSpam();
			
			if (!$seriesCreator->validate($errors))
			{
				return $this->error($errors);
			}
			$this->assertNotFlooding('post');
	
			$series = $seriesCreator->save();
			$this->finalizeSeriesCreate($seriesCreator);
			
			if (!$series->canView())
			{
				return $this->redirect($this->buildLink('ams/series', null, ['pending_approval' => 1]));
			}
			else
			{
				return $this->redirect($this->buildLink('ams/series', $series));
			}
		}
		else
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			
			if ($series->canUploadAndManageSeriesAttachments())
			{
				$attachmentData = $attachmentRepo->getEditorData('ams_series', $series);
			}
			else
			{
				$attachmentData = null;
			}
			
			$viewParams = [
				'series' => $series,
				'attachmentData' => $attachmentData,
			];
			return $this->view('XenAddons\AMS:Series\Create', 'xa_ams_series_create', $viewParams);
		}	
	}
	
	public function actionView(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id, $this->getSeriesViewExtraWith());
	
		$page = $this->filterPage($params->page);
		$perPage = $this->options()->xaAmsSeriesPartsPerPage;
	
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/series', $series, $page));
	
		$seriesRepo = $this->getSeriesRepo();
		$seriesPartRepo = $this->getSeriesPartRepo();
						
		/** @var \XenAddons\AMS\Repository\SeriesPart $seriesPartRepo */
		$seriesPartFinder = $seriesPartRepo->findPartsInSeries($series)->with('full');

		$totalSeriesParts = $series->part_count;
		
		$seriesPartFinder->limitByPage($page, $perPage);
		$seriesParts = $seriesPartFinder->fetch();
		$seriesParts = $seriesParts->filterViewable();
	
		$this->assertValidPage($page, $perPage, $totalSeriesParts, 'ams/series', $series);
	
		$communityContributors = $this->em()->getEmptyCollection();
		if ($series->community_series)
		{
			$communityContributors = $seriesRepo->findCommunityContributors($series)->fetch();
		}
		$totalContributors = $communityContributors->count();
		
		$poll = ($series->has_poll ? $series->Poll : null);
		
		$viewParams = [
			'series' => $series,
			'seriesParts' => $seriesParts,
			
			'poll' => $poll,
				
			'page' => $page,
			'perPage' => $perPage,
			'total' => $totalSeriesParts,
			
			'communityContributors' => $communityContributors,
			'totalContributors' => $totalContributors			
		];
		return $this->view('XenAddons\AMS\Series:Series\View', 'xa_ams_series_view', $viewParams);
	}
	
	protected function getSeriesViewExtraWith()
	{
		$extraWith = ['Featured'];
	
		$userId = \XF::visitor()->user_id;
		if ($userId)
		{
			$extraWith[] = 'Watch|' . $userId;
			$extraWith[] = 'Reactions|' . $userId;
			$extraWith[] = 'Bookmarks|' . $userId;
		}
	
		return $extraWith;
	}	
	
	public function actionDetails(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id, $this->getSeriesViewExtraWith());
		
		$this->assertCanonicalUrl($this->buildLink('ams/series/details', $series));
		
		$seriesRepo = $this->getSeriesRepo();
		
		$communityContributors = $this->em()->getEmptyCollection();
		if ($series->community_series)
		{
			$communityContributors = $seriesRepo->findCommunityContributors($series)->fetch();
		}
		$totalContributors = $communityContributors->count();
		
		$poll = ($series->has_poll ? $series->Poll : null);
		
		$viewParams = [
			'series' => $series,
			
			'poll' => $poll,
				
			'communityContributors' => $communityContributors,
			'totalContributors' => $totalContributors
		];
		return $this->view('XenAddons\AMS\Series:Series\Details', 'xa_ams_series_details', $viewParams);		
	}
	
	/**
	 * @param \XenAddons\AMS\Entity\SeriesItem $series
	 *
	 * @return \XenAddons\AMS\Service\Series\Edit
	 */
	protected function setupSeriesEdit(\XenAddons\AMS\Entity\SeriesItem $series)
	{
		/** @var \XenAddons\AMS\Service\Series\Edit $editor */
		$editor = $this->service('XenAddons\AMS:Series\Edit', $series);

		$editor->setTitle($this->filter('title', 'str'));
		
		$editor->setMessage($this->plugin('XF:Editor')->fromInput('message'));
		
		$series->edit_date = time();
		
		$basicFields = $this->filter([
			'description' => 'str',
			'community_series' => 'bool'
		]);
		$series->bulkSet($basicFields);
		
		if ($series->canUploadAndManageSeriesAttachments())
		{
			$editor->setAttachmentHash($this->filter('attachment_hash', 'str'));
		}
		
		if ($this->filter('author_alert', 'bool') && $series->canSendModeratorActionAlert())
		{
			$editor->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
		}
		
		return $editor;
	}	
	
	public function actionEdit(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canEdit($error))
		{
			return $this->noPermission($error);
		}	

		if ($this->isPost())
		{
			$editor = $this->setupSeriesEdit($series);
			$editor->checkForSpam();
			
			if (!$editor->validate($errors))
			{
				return $this->error($errors);
			}
			
			$series = $editor->save();
			
			if ($this->filter('post_as_update', 'bool'))
			{
				$editor->sendNotifications();
			}

			return $this->redirect($this->buildLink('ams/series/details', $series));
		}
		else
		{
			if ($series && $series->canUploadAndManageSeriesAttachments())
			{
				/** @var \XF\Repository\Attachment $attachmentRepo */
				$attachmentRepo = $this->repository('XF:Attachment');
				$attachmentData = $attachmentRepo->getEditorData('ams_series', $series);
			}
			else
			{
				$attachmentData = null;
			}
			
			$viewParams = [
				'series' => $series,
				'attachmentData' => $attachmentData,
			];
			return $this->view('XenAddons\AMS:Series\Edit', 'xa_ams_series_edit', $viewParams);
		}	
	}
	
	public function actionPreview(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		if ($params->series_id)
		{
			$series = $this->assertViewableSeries($params->series_id);
			if (!$series->canEdit($error))
			{
				return $this->noPermission($error);
			}
				
			$editor = $this->setupSeriesEdit($series);
				
			if (!$editor->validate($errors))
			{
				return $this->error($errors);
			}
		}
		else
		{
			$series = $this->em()->create('XenAddons\AMS:SeriesItem');
				
			$creator = $this->setupSeriesCreate($series);
	
			if (!$creator->validate($errors))
			{
				return $this->error($errors);
			}
		}
	
		$attachments = [];
		$tempHash = $this->filter('attachment_hash', 'str');
	
		if ($series->canUploadAndManageSeriesAttachments())
		{
			/** @var \XF\Repository\Attachment $attachmentRepo */
			$attachmentRepo = $this->repository('XF:Attachment');
			$attachmentData = $attachmentRepo->getEditorData('ams_series', $series, $tempHash);
			$attachments = $attachmentData['attachments'];
		}
	
		return $this->plugin('XF:BbCodePreview')->actionPreview(
			$series->message, 'ams_series', $series->User, $attachments, $series->canViewSeriesAttachments()
		);
	}
	
	public function actionEditIcon(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canEditIcon($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			$this->plugin('XenAddons\AMS:SeriesIcon')->actionUpload($series);
	
			return $this->redirect($this->buildLink('ams/series', $series));
		}
		else
		{
			$viewParams = [
				'series' => $series,
			];
			return $this->view('XenAddons\AMS:SeriesItem\EditIcon', 'xa_ams_series_edit_icon', $viewParams);
		}
	}	
	
	public function actionDelete(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canDelete('soft', $error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			$type = $this->filter('hard_delete', 'bool') ? 'hard' : 'soft';
			$reason = $this->filter('reason', 'str');
			
			if (!$series->canDelete($type, $error))
			{
				return $this->noPermission($error);
			}
			
			/** @var \XenAddons\AMS\Service\Series\Deleter $deleter */
			$deleter = $this->service('XenAddons\AMS:Series\Deleter', $series);
			
			if ($this->filter('author_alert', 'bool'))
			{
				$deleter->setSendAlert(true, $this->filter('author_alert_reason', 'str'));
			}
			
			$deleter->delete($type, $reason);

			$this->plugin('XF:InlineMod')->clearIdFromCookie('ams_series', $series->series_id);
			
			return $this->redirect($this->buildLink('ams/series'));
		}
		else
		{
			$viewParams = [
				'series' => $series,
			];
			return $this->view('XenAddons\AMS:Series\Delete', 'xa_ams_series_delete', $viewParams);
		}
	}
	
	public function actionUndelete(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canUndelete($error))
		{
			return $this->noPermission($error);
		}
	
		if ($this->isPost())
		{
			if ($series->series_state == 'deleted')
			{
				$series->series_state = 'visible';
				$series->save();
			}
	
			return $this->redirect($this->buildLink('ams/series', $series));
		}
		else
		{
			$viewParams = [
				'series' => $series,
			];
			return $this->view('XF:SeriesItem\Undelete', 'xa_ams_series_undelete', $viewParams);
		}
	}
	
	public function actionReassign(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canReassign($error))
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
	
			$canTargetView = \XF::asVisitor($user, function() use ($series)
			{
				return $series->canView();
			});
			if (!$canTargetView)
			{
				return $this->error(\XF::phrase('xa_ams_new_owner_must_be_able_to_view_this_series'));
			}
	
			/** @var \XenAddons\AMS\Service\Series\Reassign $reassigner */
			$reassigner = $this->service('XenAddons\AMS:Series\Reassign', $series);
	
			if ($this->filter('alert', 'bool'))
			{
				$reassigner->setSendAlert(true, $this->filter('alert_reason', 'str'));
			}
	
			$reassigner->reassignTo($user);
	
			return $this->redirect($this->buildLink('ams/series', $series));
		}
		else
		{
			$viewParams = [
				'series' => $series,
			];
			return $this->view('XenAddons\AMS:Series\Reassign', 'xa_ams_series_reassign', $viewParams);
		}
	}
	
	public function actionQuickFeature(ParameterBag $params)
	{
		$this->assertPostOnly();
	
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canFeatureUnfeature($error))
		{
			return $this->error($error);
		}
	
		/** @var \XenAddons\AMS\Service\Series\Feature $featurer */
		$featurer = $this->service('XenAddons\AMS:Series\Feature', $series);
	
		if ($series->Featured)
		{
			$featurer->unfeature();
			$featured = false;
			$text = \XF::phrase('xa_ams_series_quick_feature');
		}
		else
		{
			$featurer->feature();
			$featured = true;
			$text = \XF::phrase('xa_ams_series_quick_unfeature');
		}
	
		$reply = $this->redirect($this->getDynamicRedirect());
		$reply->setJsonParams([
			'text' => $text,
			'featured' => $featured
		]);
		return $reply;
	}
	
	public function actionBookmark(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
	
		/** @var \XF\ControllerPlugin\Bookmark $bookmarkPlugin */
		$bookmarkPlugin = $this->plugin('XF:Bookmark');
	
		return $bookmarkPlugin->actionBookmark(
			$series, $this->buildLink('ams/series/bookmark', $series)
		);
	}
	
	public function actionTags(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canEditTags($error))
		{
			return $this->noPermission($error);
		}
	
		/** @var \XF\Service\Tag\Changer $tagger */
		$tagger = $this->service('XF:Tag\Changer', 'ams_series', $series);
	
		if ($this->isPost())
		{
			$tagger->setEditableTags($this->filter('tags', 'str'));
			if ($tagger->hasErrors())
			{
				return $this->error($tagger->getErrors());
			}
	
			$tagger->save();
				
			if ($this->filter('_xfInlineEdit', 'bool'))
			{
				$viewParams = [
					'series' => $series
				];
				$reply = $this->view('XenAddons\AMS:SeriesItem\TagsInline', 'xa_ams_series_tags_list', $viewParams);
				$reply->setJsonParam('message', \XF::phrase('your_changes_have_been_saved'));
				return $reply;
			}
			else
			{
				return $this->redirect($this->buildLink('ams/series', $series));
			}
		}
		else
		{
			$grouped = $tagger->getExistingTagsByEditability();
	
			$viewParams = [
				'series' => $series,
				'editableTags' => $grouped['editable'],
				'uneditableTags' => $grouped['uneditable']
			];
			return $this->view('XenAddons\AMS:SeriesItem\Tags', 'xa_ams_series_tags', $viewParams);
		}
	}	
	
	public function actionWatch(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canWatch($error))
		{
			return $this->noPermission($error);
		}
	
		$visitor = \XF::visitor();
	
		if ($this->isPost())
		{
			if ($this->filter('stop', 'bool'))
			{
				$action = 'delete';
				$config = [];
			}
			else
			{
				$action = 'watch';
				$config = $this->filter([
					'notify_on' => 'str',
					'send_alert' => 'bool',
					'send_email' => 'bool',
				]);
			}
	
			/** @var \XenAddons\AMS\Repository\SeriesWatch $watchRepo */
			$watchRepo = $this->repository('XenAddons\AMS:SeriesWatch');
			$watchRepo->setWatchState($series, $visitor, $action, $config);
	
			$redirect = $this->redirect($this->buildLink('ams/series', $series));
			$redirect->setJsonParam('switchKey', $action == 'delete' ? 'watch' : 'unwatch');
			return $redirect;
		}
		else
		{
			$viewParams = [
				'series' => $series,
				'isWatched' => !empty($series->Watch[$visitor->user_id])
			];
			return $this->view('XenAddons\AMS:Series\Watch', 'xa_ams_series_watch', $viewParams);
		}
	}
	
	public function actionIp(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		$breadcrumbs = $series->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Ip $ipPlugin */
		$ipPlugin = $this->plugin('XF:Ip');
		return $ipPlugin->actionIp($series, $breadcrumbs);
	}
	
	public function actionReport(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canReport($error))
		{
			return $this->noPermission($error);
		}
	
		/** @var \XF\ControllerPlugin\Report $reportPlugin */
		$reportPlugin = $this->plugin('XF:Report');
		return $reportPlugin->actionReport(
			'ams_series', $series,
			$this->buildLink('ams/series/report', $series),
			$this->buildLink('ams/series', $series)
		);
	}
	
	public function actionWarn(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
	
		if (!$series->canWarn($error))
		{
			return $this->noPermission($error);
		}
	
		$breadcrumbs = $series->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Warn $warnPlugin */
		$warnPlugin = $this->plugin('XF:Warn');
		return $warnPlugin->actionWarn(
			'ams_series', $series,
			$this->buildLink('ams/series/warn', $series),
			$breadcrumbs
		);
	}

	public function actionApprove(ParameterBag $params)
	{
		$this->assertValidCsrfToken($this->filter('t', 'str'));
	
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canApproveUnapprove($error))
		{
			return $this->noPermission($error);
		}
	
		/** @var \XenAddons\AMS\Service\Series\Approve $approver */
		$approver = \XF::service('XenAddons\AMS:Series\Approve', $series);
		$approver->approve();
	
		return $this->redirect($this->buildLink('ams/series/details', $series));
	}
	
	public function actionUnapprove(ParameterBag $params)
	{
		$this->assertValidCsrfToken($this->filter('t', 'str'));
	
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canApproveUnapprove($error))
		{
			return $this->noPermission($error);
		}
	
		$series->series_state = 'moderated';
		$series->save();
	
		return $this->redirect($this->buildLink('ams/series/details', $series));
	}
	
	public function actionModeratorActions(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canViewModeratorLogs($error))
		{
			return $this->noPermission($error);
		}
	
		$breadcrumbs = $series->getBreadcrumbs();
		$title = $series->title;
	
		$this->request()->set('page', $params->page);
	
		/** @var \XF\ControllerPlugin\ModeratorLog $modLogPlugin */
		$modLogPlugin = $this->plugin('XF:ModeratorLog');
		return $modLogPlugin->actionModeratorActions(
			$series,
			['ams/series/moderator-actions', $series],
			$title, $breadcrumbs
		);
	}
	
	public function actionHistory(ParameterBag $params)
	{
		return $this->rerouteController('XF:EditHistory', 'index', [
			'content_type' => 'ams_series',
			'content_id' => $params->series_id
		]);
	}
	
	public function actionReact(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactSimple($series, 'ams/series');
	}
	
	public function actionReactions(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
	
		$breadcrumbs = $series->getBreadcrumbs();
		$title = \XF::phrase('xa_ams_members_who_reacted_to_series', ['title' => $series->title]);
	
		$this->request()->set('page', $params->page);
	
		/** @var \XF\ControllerPlugin\Reaction $reactionPlugin */
		$reactionPlugin = $this->plugin('XF:Reaction');
		return $reactionPlugin->actionReactions(
			$series,
			'ams/series/reactions',
			$title, $breadcrumbs
		);
	}	

	public function actionDialogYours()
	{
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsSeriesPerPage;
		
		$seriesRepo = $this->getSeriesRepo();
		
		$seriesList = $seriesRepo->findSeriesForSeriesList()
			->where('user_id', \XF::visitor()->user_id)
			->limitByPage($page, $perPage, 1);
		
		$series = $seriesList->fetch();
	
		$hasMore = false;
		if ($series->count() > $perPage)
		{
			$hasMore = true;
			$series = $series->slice(0, $perPage);
		}
	
		$viewParams = [
			'series' => $series,
			'page' => $page,
			'hasMore' => $hasMore
		];
		return $this->view('XenAddons\AMS:Series\Dialog\Yours', 'xa_ams_dialog_your_series', $viewParams);
	}
	
	public function actionDialogBrowse()
	{
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsSeriesPerPage;		
		
		$seriesRepo = $this->getSeriesRepo();
		
		$seriesList = $seriesRepo->findSeriesForSeriesList()
			->where('user_id', '<>', \XF::visitor()->user_id)
			->limitByPage($page, $perPage, 1);
		
		$series = $seriesList->fetch();
	
		$hasMore = false;
		if ($series->count() > $perPage)
		{
			$hasMore = true;
			$series = $series->slice(0, $perPage);
		}
	
		$viewParams = [
			'series' => $series,
			'page' => $page,
			'hasMore' => $hasMore
		];
		return $this->view('XenAddons\AMS:Series\Dialog\Browse', 'xa_ams_dialog_browse_series', $viewParams);
	}

	public function actionPollCreate(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
	
		$breadcrumbs = $series->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionCreate('ams_series', $series, $breadcrumbs);
	}
	
	public function actionPollEdit(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		$poll = $series->Poll;
	
		$breadcrumbs = $series->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionEdit($poll, $breadcrumbs);
	}
	
	public function actionPollDelete(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		$poll = $series->Poll;
	
		$breadcrumbs = $series->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionDelete($poll, $breadcrumbs);
	}
	
	public function actionPollVote(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		$poll = $series->Poll;
	
		$breadcrumbs = $series->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionVote($poll, $breadcrumbs);
	}
	
	public function actionPollResults(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		$poll = $series->Poll;
	
		$breadcrumbs = $series->getBreadcrumbs();
	
		/** @var \XF\ControllerPlugin\Poll $pollPlugin */
		$pollPlugin = $this->plugin('XF:Poll');
		return $pollPlugin->actionResults($poll, $breadcrumbs);
	}	
	
	/**
	 * @param \XenAddons\AMS\Entity\SeriesItem $series
	 *
	 * @return \XenAddons\AMS\Service\Series\AddSeriesPart
	 */
	protected function setupAddSeriesPart(\XenAddons\AMS\Entity\SeriesItem $series)
	{
		/** @var \XenAddons\AMS\Service\Series\AddSeriesPart $seriesPartAdder */
		$seriesPartAdder = $this->service('XenAddons\AMS:Series\AddSeriesPart', $series);

		$seriesPartAdder->setTitle($this->filter('title', 'str'));
		
		$basicFields = $this->filter([
			'display_order' => 'int',
			'article_id' => 'int'
		]);
		
		$seriesPartAdder->getSeriesPart()->bulkSet($basicFields);
		
		return $seriesPartAdder;
	}
	
	protected function finalizeAddSeriesPart(\XenAddons\AMS\Service\Series\AddSeriesPart $seriesPartAdder)
	{
		$seriesPartAdder->sendNotifications(); 
	
		$seriesPart = $seriesPartAdder->getSeriesPart();
	}
	
	public function actionAddSeriesPart(ParameterBag $params)
	{
		$series = $this->assertViewableSeries($params->series_id);
		if (!$series->canAddArticleToSeries($error))
		{
			return $this->error($error);
		}
		
		$visitor = \XF::visitor();
		
		if ($this->isPost())
		{
			if (!$this->filter('article_id', 'int'))
			{
				return $this->error(\XF::phrase('xa_ams_you_must_select_recent_article_or_set_article_id'));
			}
			
			$creator = $this->setupAddSeriesPart($series);
				
			if (!$creator->validate($errors))
			{
				return $this->error($errors);
			}
				
			$seriesPart = $creator->save();
			
			$this->finalizeAddSeriesPart($creator); 
			
			return $this->redirect($this->buildLink('ams/series', $series));
		}
		else
		{
			/** @var \XenAddons\AMS\Repository\Article $articleRepo */
			$articleRepo = $this->getArticleRepo();
			$articleFinder = $articleRepo->findArticlesForSelectList($visitor->user_id)
				->where('series_part_id', '=', 0)
				->setDefaultOrder('last_update', 'desc');
			
			$articles = $articleFinder->fetch(50);
			
			$seriesPart = $series->getNewSeriesPart();
			
			$viewParams = [
				'seriesPart' => $seriesPart,
				'series' => $series,
				'articles' => $articles
			];
			return $this->view('XenAddons\AMS:Series\AddSeriesPart', 'xa_ams_series_add_series_part', $viewParams);
		}		
	}

	public static function getActivityDetails(array $activities)
	{
		return self::getActivityDetailsForContent(
			$activities, \XF::phrase('xa_ams_viewing_series'), 'series_id',
			function(array $ids)
			{
				$series = \XF::em()->findByIds(
					'XenAddons\AMS:SeriesItem',
					$ids
				);
	
				$router = \XF::app()->router('public');
				$data = [];
	
				foreach ($series->filterViewable() AS $id => $series)
				{
					$data[$id] = [
						'title' => $series->title,
						'url' => $router->buildLink('ams/series', $series)
					];
				}
	
				return $data;
			},
			\XF::phrase('xa_ams_viewing_series')
		);
	}
}