<?php

namespace XenAddons\AMS\Report;

use XF\Entity\Report;
use XF\Mvc\Entity\Entity;
use XF\Report\AbstractHandler;

class Rating extends AbstractHandler
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
		
			return $visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'viewReviews');
		}
		else
		{
			return $visitor->hasPermission('xa_ams', 'viewReviews');
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
				$visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'editAnyReview')
				|| $visitor->hasAmsArticleCategoryPermission($contentInfo['category_id'], 'deleteAnyReview')
			);
		}
		else
		{
			return (
				$visitor->hasPermission('xa_ams', 'editAnyReview')
				|| $visitor->hasPermission('xa_ams', 'deleteAnyReview')
			);
		}
	}

	public function setupReportEntityContent(Report $report, Entity $content)
	{
		/** @var \XenAddons\AMS\Entity\ArticleRating $rating */
		$rating = $content;
		$article = $content->Article;
		$category = $article->Category;

		if (!empty($article->prefix_id))
		{
			$title = $article->Prefix->title . ' - ' . $article->title;
		}
		else
		{
			$title = $article->title;
		}

		$report->content_user_id = $rating->user_id;
		$report->content_info = [
			'rating' => [
				'rating_id' => $rating->rating_id,
				'article_id' => $rating->article_id,
				'rating' => $rating->rating,
				'message' => $rating->message
			],
			'article' => [
				'article_id' => $article->article_id,
				'title' => $title,
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
		return \XF::phrase('xa_ams_article_review_in_x', [
			'title' => \XF::app()->stringFormatter()->censorText($report->content_info['article']['title'])
		]);
	}

	public function getContentMessage(Report $report)
	{
		if (isset($report->content_info['rating']['message']))
		{
			return $report->content_info['rating']['message'];
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
			'canonical:ams/review',
			[
				'article_id' => $info['article']['article_id'],
				'article_title' => $info['article']['title'],
				'rating_id' => $info['rating']['rating_id']
			]
		);
	}

	public function getEntityWith()
	{
		return ['Article', 'Article.Category'];
	}
}