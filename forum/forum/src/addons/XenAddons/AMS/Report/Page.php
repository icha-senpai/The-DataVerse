<?php

namespace XenAddons\AMS\Report;

use XF\Entity\Report;
use XF\Mvc\Entity\Entity;
use XF\Report\AbstractHandler;

class Page extends AbstractHandler
{
	protected function canViewContent(Report $report)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		
		$contentInfo = $report->content_info['article'];
		
		if (!empty($contentInfo['category_id']))
		{
			if (!method_exists($visitor, 'hasAmsArticleCategoryPermission'))
			{
				return false;
			}
		
			return $visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'view');
		}
		else
		{
			return $visitor->hasPermission('xa_ams', 'view');
		}
	}

	protected function canActionContent(Report $report)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		
		$contentInfo = $report->content_info['article'];
		
		if (!empty($contentInfo['category_id']))
		{
			if (!method_exists($visitor, 'hasAmsArticleCategoryPermission'))
			{
				return false;
			}
		
			return (
				$visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'editAny')
				|| $visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'deleteAny')
			);
		}
		else
		{
			return (
				$visitor->hasPermission('xa_ams', 'editAny')
				|| $visitor->hasPermission('xa_ams', 'deleteAny')
			);
		}
	}

	public function setupReportEntityContent(Report $report, Entity $content)
	{
		/** @var \XenAddons\AMS\Entity\ArticlePage $page */
		$page = $content;
		$article = $content->Article;
		$category = $article->Category;

		if (!empty($article->prefix_id))
		{
			$articleTitle = $article->Prefix->title . ' - ' . $article->title;
		}
		else
		{
			$articleTitle = $article->title;
		}

		$report->content_user_id = $page->user_id;
		$report->content_info = [
			'page' => [
				'page_id' => $page->page_id,
				'article_id' => $page->article_id,
				'title' => $page->title,
				'message' => $page->message,
				'user_id' => $page->user_id,
				'username' => $page->username
			],
			'article' => [
				'article_id' => $article->article_id,
				'title' => $articleTitle,
				'prefix_id' => $article->prefix_id,
				'category_id' => $article->category_id,
				'user_id' => $article->user_id,
				'username' => $article->username
			],
			'category' => [
				'category_id' => $category->category_id,
				'title' => $category->title
			]
		];
	}

	public function getContentTitle(Report $report)
	{
		return \XF::phrase('xa_ams_article_page_in_x', [
			'page_title' => \XF::app()->stringFormatter()->censorText($report->content_info['page']['title']),
			'article_title' => \XF::app()->stringFormatter()->censorText($report->content_info['article']['title'])
		]);
	}

	public function getContentMessage(Report $report)
	{
		if (isset($report->content_info['page']['message']))
		{
			return $report->content_info['page']['message'];
		}
		else
		{
			if (isset($report->content_info['message']))
			{
				return $report->content_info['message'];
			}
			else
			{
				return 'N/A';
			}
		}
	}

	public function getContentLink(Report $report)
	{
		$info = $report->content_info;

		return \XF::app()->router()->buildLink(
			'canonical:ams/page',
			[
				'article_id' => $info['article']['article_id'],
				'article_title' => $info['article']['title'],
				'page_id' => $info['page']['page_id'],
				'title' => $info['page']['title'],
			]
		);
	}

	public function getEntityWith()
	{
		return ['Article', 'Article.Category'];
	}
}