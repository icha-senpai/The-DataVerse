<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class Author extends AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		if ($params->user_id)
		{
			return $this->rerouteController('XenAddons\AMS:Author', 'Author', $params);
		}

		/** @var \XF\Entity\MemberStat $memberStat */
		$memberStat = $this->em()->findOne('XF:MemberStat', ['member_stat_key' => 'xa_ams_most_articles']);

		if ($memberStat && $memberStat->canView())
		{
			return $this->redirectPermanently(
				$this->buildLink('members', null, ['key' => $memberStat->member_stat_key])
			);
		}
		else
		{
			return $this->redirect($this->buildLink('ams'));
		}
	}
	
	public function actionAuthor(ParameterBag $params)
	{
		$this->assertNotEmbeddedImageRequest();
		
		/** @var \XF\Entity\User $user */
		$user = $this->assertRecordExists('XF:User', $params->user_id);
	
		/** @var \XenAddons\AMS\ControllerPlugin\AuthorArticleList $authorArticleListPlugin */
		$authorArticleListPlugin = $this->plugin('XenAddons\AMS:AuthorArticleList');
	
		$categoryParams = $authorArticleListPlugin->getCategoryListData();
		$viewableCategoryIds = $categoryParams['categories']->keys();
	
		$listParams = $authorArticleListPlugin->getAuthorArticleListData($viewableCategoryIds, $user);
	
		$this->assertValidPage($listParams['page'], $listParams['perPage'], $listParams['total'], 'ams/authors', $user);
		$this->assertCanonicalUrl($this->buildPaginatedLink('ams/authors', $user, $listParams['page']));
		
		$viewParams = $categoryParams + $listParams;
	
		return $this->view('XenAddons\AMS:Author\View', 'xa_ams_author_view', $viewParams);
	}
	
	public function actionFilters(ParameterBag $params)
	{
		/** @var \XF\Entity\User $user */
		$user = $this->assertRecordExists('XF:User', $params->user_id);
		
		/** @var \XenAddons\AMS\ControllerPlugin\AuthorArticleList $authorArticleListPlugin */
		$authorArticleListPlugin = $this->plugin('XenAddons\AMS:AuthorArticleList');
	
		return $authorArticleListPlugin->actionFilters($user);
	}	
	
	public function actionDraftArticles(ParameterBag $params)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		
		/** @var \XF\Entity\User $user */
		$user = $this->assertRecordExists('XF:User', $params->user_id);

		if (!$visitor->hasAmsArticlePermission('viewDraft')
			&& (!$visitor->user_id || $visitor->user_id != $user->user_id)
		)
		{
			throw $this->exception($this->noPermission());
		}
		
		$articleRepo = $this->getArticleRepo();
	
		$articleFinder = $articleRepo->findDraftArticlesForUser($user);
		
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
	
		$articleFinder->limitByPage($page, $perPage);
		
		$draftArticles = $articleFinder->fetch()->filterViewable();
		$totalDraftArticles = $articleFinder->total();
		
		$viewParams = [
			'user' => $user,
			'draftArticles' =>  $draftArticles,
			'totalDraftArticles' => $totalDraftArticles,
			'page' => $page,
			'perPage' => $perPage,
		];
	
		return $this->view('XenAddons\AMS:Author\DraftArticles', 'xa_ams_author_draft_articles', $viewParams);
	}

	public function actionAwaitingArticles(ParameterBag $params)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
	
		/** @var \XF\Entity\User $user */
		$user = $this->assertRecordExists('XF:User', $params->user_id);
	
		if (!$visitor->hasAmsArticlePermission('viewAwaiting')
			&& (!$visitor->user_id || $visitor->user_id != $user->user_id)
		)
		{
			throw $this->exception($this->noPermission());
		}
	
		$articleRepo = $this->getArticleRepo();
	
		$articleFinder = $articleRepo->findAwaitingArticlesForUser($user);
	
		$page = $this->filterPage();
		$perPage = $this->options()->xaAmsArticlesPerPage;
	
		$articleFinder->limitByPage($page, $perPage);
	
		$awaitingArticles = $articleFinder->fetch()->filterViewable();
		$totalAwaitingArticles = $articleFinder->total();
	
		$viewParams = [
			'user' => $user,
			'awaitingArticles' =>  $awaitingArticles,
			'totalAwaitingArticles' => $totalAwaitingArticles,
			'page' => $page,
			'perPage' => $perPage,
		];
	
		return $this->view('XenAddons\AMS:Author\AwaitingArticles', 'xa_ams_author_awaiting_articles', $viewParams);
	}	
	
	public static function getActivityDetails(array $activities)
	{
		return \XF::phrase('xa_ams_viewing_articles');
	}
}