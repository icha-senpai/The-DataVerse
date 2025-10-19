<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XF\PrintableException;

class ArticlePage extends Repository
{
	public function findPagesInArticle(\XenAddons\AMS\Entity\ArticleItem $article, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\ArticlePage $finder */
		$finder = $this->finder('XenAddons\AMS:ArticlePage');
		$finder->inArticle($article, $limits)
			->where('page_state', 'visible')
			->setDefaultOrder('display_order', 'asc');

		return $finder;
	}
	
	public function findPagesInArticleManagePages(\XenAddons\AMS\Entity\ArticleItem $article, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\ArticlePage $finder */
		$finder = $this->finder('XenAddons\AMS:ArticlePage');
		$finder->inArticle($article, $limits)
			->setDefaultOrder('display_order', 'asc');
	
		return $finder;
	}	
	
	public function findArticlePagesForUser(\XF\Entity\User $user, array $viewableCategoryIds = null, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\ArticlePage $articlePageFinder */
		$articlePageFinder = $this->finder('XenAddons\AMS:ArticlePage');
	
		$articlePageFinder->byUser($user)
			->with(['full'])
			->setDefaultOrder('create_date', 'desc');
	
		if (is_array($viewableCategoryIds))
		{
			// if we have viewable category IDs, we likely have those permissions
			$articlePageFinder->where('Article.category_id', $viewableCategoryIds);
		}
		else
		{
			$articlePageFinder->with('Article.Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}
	
		return $articlePageFinder;
	}	
	
	public function findArticlePagesForArticlePageList(array $viewableCategoryIds = null, array $limits = [], \XenAddons\AMS\Entity\Category $category = null)
	{
		/** @var \XenAddons\AMS\Finder\ArticlePage $articlePageFinder */
		$articlePageFinder = $this->finder('XenAddons\AMS:ArticlePage');
		
		if (is_array($viewableCategoryIds))
		{
			$articlePageFinder->where('Article.category_id', $viewableCategoryIds);
		}
		else
		{
			$articlePageFinder->with('Article.Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}
		
		$articlePageFinder
			->with(['full'])
			->setDefaultOrder('create_date', 'desc');
		
		return $articlePageFinder;		
	}
	
	public function getPagesImagesForArticle(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		$db = $this->db();
	
		$ids = $db->fetchAllColumn("
			SELECT page_id
			FROM xf_xa_ams_article_page
			WHERE article_id = ?
			AND page_state = 'visible'
			AND attach_count > 0
			ORDER BY page_id
			", $article->article_id
		);
	
		if ($ids)
		{
			$attachments = $this->finder('XF:Attachment')
				->where([
					'content_type' => 'ams_page',
					'content_id' => $ids
				])
				->order('attach_date')
				->fetch();
		}
		else
		{
			$attachments = $this->em->getEmptyCollection();
		}
	
		return $attachments;
	}	

	public function sendModeratorActionAlert(\XenAddons\AMS\Entity\ArticlePage $page, $action, $reason = '', array $extra = [], \XF\Entity\User $forceUser = null)
	{
		$article = $page->Article;

		if (!$article || !$article->user_id || !$article->User)
		{
			return false;
		}
		
		if (!$forceUser)
		{
			if (!$page->user_id || !$page->User)
			{
				return false;
			}
		
			$forceUser = $page->User;
		}

		$extra = array_merge([
			'title' => $page->title,
			'link' => $this->app()->router('public')->buildLink('nopath:ams/page', $page),
			'prefix_id' => $article->prefix_id,
			'articleTitle' => $article->title,
			'articleLink' => $this->app()->router('public')->buildLink('nopath:ams', $article),
			'reason' => $reason
		], $extra);

		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = $this->repository('XF:UserAlert');
		$alertRepo->alert(
			$forceUser,
			0, '',
			'user', $forceUser->user_id,
			"ams_page_{$action}", $extra,
			['dependsOnAddOnId' => 'XenAddons/AMS']
		);

		return true;
	}
	
	/**
	 * @param $url
	 * @param null $type
	 * @param null $error
	 *
	 * @return null|\XF\Mvc\Entity\Entity
	 */
	public function getArticlePageFromUrl($url, $type = null, &$error = null)
	{
		$routePath = $this->app()->request()->getRoutePathFromUrl($url);
		$routeMatch = $this->app()->router($type)->routeToController($routePath);
		$params = $routeMatch->getParameterBag();
	
		if (!$params->page_id)
		{
			$error = \XF::phrase('xa_ams_no_article_page_id_could_be_found_from_that_url');
			return null;
		}
	
		$articlePage = $this->app()->find('XenAddons\AMS:ArticlePage', $params->page_id);
		if (!$articlePage)
		{
			$error = \XF::phrase('xa_ams_no_article_page_could_be_found_with_id_x', ['page_id' => $params->page_id]);
			return null;
		}
	
		return $articlePage;
	}
}