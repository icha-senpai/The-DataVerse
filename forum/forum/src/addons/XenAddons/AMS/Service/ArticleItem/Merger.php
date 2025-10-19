<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;
use XF\Entity\User;

class Merger extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $target;

	protected $alert = false;
	protected $alertReason = '';

	protected $log = true;

	protected $sourceArticles = [];
	protected $sourceComments = [];
	protected $sourceRatings = [];
	protected $sourceReviews = [];

	public function __construct(\XF\App $app, ArticleItem $target)
	{
		parent::__construct($app);

		$this->target = $target;
	}

	public function getTarget()
	{
		return $this->target;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function setLog($log)
	{
		$this->log = (bool)$log;
	}

	public function merge($sourceArticlesRaw)
	{
		if ($sourceArticlesRaw instanceof \XF\Mvc\Entity\AbstractCollection)
		{
			$sourceArticlesRaw = $sourceArticlesRaw->toArray();
		}
		else if ($sourceArticlesRaw instanceof ArticleItem)
		{
			$sourceArticlesRaw = [$sourceArticlesRaw];
		}
		else if (!is_array($sourceArticlesRaw))
		{
			throw new \InvalidArgumentException('Articles must be provided as collection, array or entity');
		}

		if (!$sourceArticlesRaw)
		{
			return false;
		}

		$db = $this->db();

		/** @var ArticleItem[] $sourceArticles */
		$sourceArticles = [];
		foreach ($sourceArticlesRaw AS $sourceArticle)
		{
			$sourceArticle->setOption('log_moderator', false);
			$sourceArticles[$sourceArticle->article_id] = $sourceArticle;
		}

		$comments = $db->fetchAllKeyed("
			SELECT comment_id, article_id, user_id, comment_state, reactions
			FROM xf_xa_ams_comment
			WHERE article_id IN (" . $db->quote(array_keys($sourceArticles)) . ")
		", 'comment_id');
		
		$ratings = $db->fetchAllKeyed("
			SELECT rating_id, article_id, user_id, rating_state, reactions
			FROM xf_xa_ams_article_rating
			WHERE article_id IN (" . $db->quote(array_keys($sourceArticles)) . ")
				AND is_review = 0
		", 'rating_id');
		
		$reviews = $db->fetchAllKeyed("
			SELECT rating_id, article_id, user_id, rating_state, reactions
			FROM xf_xa_ams_article_rating
			WHERE article_id IN (" . $db->quote(array_keys($sourceArticles)) . ")
				AND is_review = 1
		", 'rating_id');
		


		$this->sourceArticles = $sourceArticles;
		$this->sourceComments = $comments;
		$this->sourceRatings = $ratings;
		$this->sourceReviews = $reviews;
		
		$target = $this->target;
		$target->setOption('log_moderator', false);

		$db->beginTransaction();

		$this->moveDataToTarget();
		$this->updateTargetData();
		$this->updateUserCounters();

		if ($this->alert)
		{
			$this->sendAlert();
		}

		foreach ($sourceArticles AS $sourceArticle)
		{
			$sourceArticle->delete();
		}

		$this->finalActions();

		$db->commit();

		return true;
	}

	protected function moveDataToTarget()
	{
		$db = $this->db();
		$target = $this->target;

		$sourceComments = $this->sourceComments;
		$sourceRatings = $this->sourceRatings;
		$sourceReviews = $this->sourceReviews;
		
		$sourceArticles = $this->sourceArticles;
		$sourceArticleIds = array_keys($sourceArticles);
		$sourceIdsQuoted = $db->quote($sourceArticleIds);

		$db->update('xf_xa_ams_article_rating',
			['article_id' => $target->article_id],
			"article_id IN ($sourceIdsQuoted)"
		);
		
		$db->update('xf_xa_ams_comment',
			['article_id' => $target->article_id],
			"article_id IN ($sourceIdsQuoted)"
		);
		
		$db->update('xf_xa_ams_article_watch',
			['article_id' => $target->article_id],
			"article_id IN ($sourceIdsQuoted)",
			[], 'IGNORE'
		);
		
		$db->update('xf_xa_ams_article_reply_ban',
			['article_id' => $target->article_id],
			"article_id IN ($sourceIdsQuoted)",
			[], 'IGNORE'
		);
		
		$db->update('xf_tag_content',
			['content_id' => $target->article_id],
			"content_type = 'ams_article' AND content_id IN ($sourceIdsQuoted)",
			[], 'IGNORE'
		);
	}

	protected function updateTargetData()
	{
		$db = $this->db();
		$target = $this->target;
		$sourceArticles = $this->sourceArticles;

		foreach ($sourceArticles AS $sourceArticle)
		{
			$target->view_count += $sourceArticle->view_count;
		}

		$target->rebuildCounters();
		$target->save();

		/** @var \XF\Repository\Tag $tagRepo */
		$tagRepo = $this->repository('XF:Tag');
		$tagRepo->rebuildContentTagCache('ams_article', $target->article_id);
	}

	protected function updateUserCounters()
	{
		// nothing for now, unless she weighs more than a duck
	}

	protected function sendAlert()
	{
		$target = $this->target;
		$actor = \XF::visitor();

		/** @var \XenAddons\AMS\Repository\Article $articleRepo */
		$articleRepo = $this->repository('XenAddons\AMS:Article');

		$alertExtras = [
			'targetTitle' => $target->title,
			'targetLink' => $this->app->router('public')->buildLink('nopath:ams', $target)
		];

		foreach ($this->sourceArticles AS $sourceArticle)
		{
			if ($sourceArticle->article_state == 'visible'
				&& $sourceArticle->user_id != $actor->user_id
			)
			{
				$articleRepo->sendModeratorActionAlert($sourceArticle, 'merge', $this->alertReason, $alertExtras);
			}
		}
	}

	protected function finalActions()
	{
		$target = $this->target;
		$sourceArticles = $this->sourceArticles;
		$sourceArticleIds = array_keys($sourceArticles);
		$commentIds = array_keys($this->sourceComments);
		$ratingIds = array_keys($this->sourceRatings);
		$reviewIds = array_keys($this->sourceReviews);
		
		if ($commentIds)
		{
			$this->app->jobManager()->enqueue('XF:SearchIndex', [
				'content_type' => 'ams_comment',
				'content_ids' => $commentIds
			]);
		}

		if ($reviewIds)
		{
			$this->app->jobManager()->enqueue('XF:SearchIndex', [
				'content_type' => 'ams_rating',
				'content_ids' => $reviewIds
			]);
		}
		
		if ($this->log)
		{
			$this->app->logger()->logModeratorAction('ams_article', $target, 'merge_target',
				['ids' => implode(', ', $sourceArticleIds)]
			);
		}
	}
}