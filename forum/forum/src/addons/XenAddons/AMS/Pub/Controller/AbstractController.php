<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View;

abstract class AbstractController extends \XF\Pub\Controller\AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		if (!$visitor->canViewAmsArticles($error))
		{
			throw $this->exception($this->noPermission($error));
		}
		
		if ($this->options()->xaAmsOverrideStyle)
		{
			$this->setViewOption('style_id', $this->options()->xaAmsOverrideStyle);
		}
	}

	protected function postDispatchController($action, ParameterBag $params, AbstractReply &$reply)
	{
		if ($reply instanceof \XF\Mvc\Reply\View)
		{
			$viewParams = $reply->getParams();
			$category = null;

			if (isset($viewParams['category']))
			{
				$category = $viewParams['category'];
			}
			if (isset($viewParams['articleItem']))
			{
				$category = $viewParams['articleItem']->Category;
			}
			if ($category)
			{
				$reply->setContainerKey('amsCategory-' . $category->category_id);
			}
		}
	}
	
	/**
	 * @param integer $articleId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\ArticleItem
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableArticle($articleId, array $extraWith = [])
	{
		$visitor = \XF::visitor();
	
		$extraWith[] = 'User';
		$extraWith[] = 'Category';
		$extraWith[] = 'Category.Permissions|' . $visitor->permission_combination_id;
		$extraWith[] = 'Discussion';
		$extraWith[] = 'Discussion.Forum';
		$extraWith[] = 'Discussion.Forum.Node';
		$extraWith[] = 'Discussion.Forum.Node.Permissions|' . $visitor->permission_combination_id;
	
		/** @var \XenAddons\AMS\Entity\ArticleItem $article */
		$article = $this->em()->find('XenAddons\AMS:ArticleItem', $articleId, $extraWith);
		if (!$article)
		{
			throw $this->exception($this->notFound(\XF::phrase('xa_ams_requested_article_not_found')));
		}
	
		if (!$article->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		return $article;
	}	
	
	/**
	 * @param integer $categoryId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\Category
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableCategory($categoryId, array $extraWith = [])
	{
		$visitor = \XF::visitor();

		$extraWith[] = 'Permissions|' . $visitor->permission_combination_id;

		/** @var \XenAddons\AMS\Entity\Category $category */
		$category = $this->em()->find('XenAddons\AMS:Category', $categoryId, $extraWith);
		if (!$category)
		{
			throw $this->exception($this->notFound(\XF::phrase('requested_category_not_found')));
		}

		if (!$category->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}

		return $category;
	}

	/**
	 * @param integer $commentId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\Comment
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableComment($commentId, array $extraWith = [])
	{
		/** @var \XenAddons\AMS\Entity\Comment $comment */
		$comment = $this->em()->find('XenAddons\AMS:Comment', $commentId, $extraWith);

		if (!$comment)
		{
			throw $this->exception($this->notFound(\XF::phrase('xa_ams_requested_comment_not_found')));
		}
		if (!$comment->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}

		return $comment;
	}

	/**
	 * @param integer $pageId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\ArticlePage
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewablePage($pageId, array $extraWith = [])
	{
		$visitor = \XF::visitor();
	
		$extraWith[] = 'Article';
		$extraWith[] = 'Article.User';
		$extraWith[] = 'Article.Category';
		$extraWith[] = 'Article.Category.Permissions|' . $visitor->permission_combination_id;
	
		/** @var \XenAddons\AMS\Entity\ArticlePage $page */
		$page = $this->em()->find('XenAddons\AMS:ArticlePage', $pageId, $extraWith);
		if (!$page)
		{
			throw $this->exception($this->notFound(\XF::phrase('xa_ams_requested_article_page_not_found')));
		}
	
		if (!$page->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		return $page;
	}
	
	/**
	 * @param integer $ratingId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\ArticleRating
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableReview($ratingId, array $extraWith = [])
	{
		$visitor = \XF::visitor();
	
		$extraWith[] = 'Article';
		$extraWith[] = 'Article.User';
		$extraWith[] = 'Article.Category';
		$extraWith[] = 'Article.Category.Permissions|' . $visitor->permission_combination_id;
	
		/** @var \XenAddons\AMS\Entity\ArticleRating $review */
		$review = $this->em()->find('XenAddons\AMS:ArticleRating', $ratingId, $extraWith);
		if (!$review)
		{
			throw $this->exception($this->notFound(\XF::phrase('xa_ams_requested_review_not_found')));
		}
	
		if (!$review->canView($error) || !$review->is_review)
		{
			throw $this->exception($this->noPermission($error));
		}
	
		return $review;
	}	
	
	// NOTE... this is for RATINGS ONLY (not a review). 
	/**
	 * @param integer $ratingId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\ArticleRating
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableRating($ratingId, array $extraWith = [])
	{
		$visitor = \XF::visitor();
	
		$extraWith[] = 'Article';
		$extraWith[] = 'Article.User';
		$extraWith[] = 'Article.Category';
		$extraWith[] = 'Article.Category.Permissions|' . $visitor->permission_combination_id;
	
		/** @var \XenAddons\AMS\Entity\ArticleRating $rating */
		$rating = $this->em()->find('XenAddons\AMS:ArticleRating', $ratingId, $extraWith);
		if (!$rating)
		{
			throw $this->exception($this->notFound(\XF::phrase('xa_ams_requested_rating_not_found')));
		}
	
		if (!$rating->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		return $rating;
	}

	/**
	 * @param integer $seriesId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\SeriesItem
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableSeries($seriesId, array $extraWith = [])
	{
		$visitor = \XF::visitor();
	
		$extraWith[] = 'LastArticle';
		$extraWith[] = 'User';
	
		/** @var \XenAddons\AMS\Entity\SeriesItem $series */
		$series = $this->em()->find('XenAddons\AMS:SeriesItem', $seriesId, $extraWith);
		if (!$series)
		{
			throw $this->exception($this->notFound(\XF::phrase('xa_ams_requested_series_not_found')));
		}
	
		if (!$series->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		return $series;
	}	
	
	/**
	 * @param integer $partId
	 * @param array $extraWith
	 *
	 * @return \XenAddons\AMS\Entity\SeriesPart
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableSeriesPart($partId, array $extraWith = [])
	{
		$visitor = \XF::visitor();
	
		$extraWith[] = 'Series';
		$extraWith[] = 'Series.User';
		$extraWith[] = 'Article';
		$extraWith[] = 'Article.User';
		$extraWith[] = 'Article.Category';
		$extraWith[] = 'Article.Category.Permissions|' . $visitor->permission_combination_id;
	
		/** @var \XenAddons\AMS\Entity\SeriesPart $part */
		$part = $this->em()->find('XenAddons\AMS:SeriesPart', $partId, $extraWith);
		if (!$part)
		{
			throw $this->exception($this->notFound(\XF::phrase('xa_ams_requested_series_part_not_found')));
		}
	
		if (!$part->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		return $part;
	}	

	/**
	 * @param integer $threadId
	 * @param array $extraWith
	 *
	 * @return \XF\Entity\Thread
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableThread($threadId, array $extraWith = [])
	{
		$visitor = \XF::visitor();
	
		$extraWith[] = 'Forum';
		$extraWith[] = 'Forum.Node';
		$extraWith[] = 'Forum.Node.Permissions|' . $visitor->permission_combination_id;
	
		/** @var \XF\Entity\Thread $thread */
		$thread = $this->em()->find('XF:Thread', $threadId, $extraWith);
		if (!$thread)
		{
			throw $this->exception($this->notFound(\XF::phrase('requested_thread_not_found')));
		}
	
		if (!$thread->canView($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		$this->plugin('XF:Node')->applyNodeContext($thread->Forum->Node);
		$this->setContentKey('thread-' . $thread->thread_id);
	
		return $thread;
	}


	/**
	 * @return \XenAddons\AMS\Repository\Article
	 */
	protected function getArticleRepo()
	{
		return $this->repository('XenAddons\AMS:Article');
	}
	
	/**
	 * @return \XenAddons\AMS\Repository\ArticlePage
	 */
	protected function getPageRepo()
	{
		return $this->repository('XenAddons\AMS:ArticlePage');
	}
	
	/**
	 * @return \XenAddons\AMS\Repository\ArticleRating
	 */
	protected function getRatingRepo()
	{
		return $this->repository('XenAddons\AMS:ArticleRating');
	}	

	/**
	 * @return \XenAddons\AMS\Repository\Category
	 */
	protected function getCategoryRepo()
	{
		return $this->repository('XenAddons\AMS:Category');
	}
	
	/**
	 * @return \XenAddons\AMS\Repository\Comment
	 */
	protected function getCommentRepo()
	{
		return $this->repository('XenAddons\AMS:Comment');
	}

	/**
	 * @return \XenAddons\AMS\Repository\Series
	 */
	protected function getSeriesRepo()
	{
		return $this->repository('XenAddons\AMS:Series');
	}
	
	/**
	 * @return \XenAddons\AMS\Repository\SeriesPart
	 */
	protected function getSeriesPartRepo()
	{
		return $this->repository('XenAddons\AMS:SeriesPart');
	}
	
}