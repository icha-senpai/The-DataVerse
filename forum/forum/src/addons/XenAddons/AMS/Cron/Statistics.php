<?php

namespace XenAddons\AMS\Cron;

class Statistics
{
	public static function cacheAmsStatistics()
	{
		$simpleCache = \XF::app()->simpleCache();
		$db = \XF::db();

		$categoryCount = $db->fetchOne('
			SELECT COUNT(*)
			FROM xf_xa_ams_category
		');

		$articleCount = $db->fetchOne('
			SELECT COUNT(*)
			FROM xf_xa_ams_article 
			WHERE article_state = \'visible\'
		');
		
		$seriesCount = $db->fetchOne('
			SELECT COUNT(*)
			FROM xf_xa_ams_series
		');
		
		$viewCount = $db->fetchOne('
			SELECT SUM(view_count)
			FROM xf_xa_ams_article
			WHERE article_state = \'visible\'
		');

		$commentCount = $db->fetchOne('
			SELECT COUNT(*)
			FROM xf_xa_ams_comment AS comment
			LEFT JOIN xf_xa_ams_article AS article ON
				(comment.article_id = article.article_id)
			WHERE comment.comment_state = \'visible\'
		');
		
		$ratingCount = $db->fetchOne('
			SELECT COUNT(*)
			FROM xf_xa_ams_article_rating AS rating
			LEFT JOIN xf_xa_ams_article AS article ON
				(rating.article_id = article.article_id)
			WHERE rating.rating_state = \'visible\'
		');	

		$reviewCount = $db->fetchOne('
			SELECT COUNT(*)
			FROM xf_xa_ams_article_rating AS rating
			LEFT JOIN xf_xa_ams_article AS article ON
				(rating.article_id = article.article_id)
			WHERE rating.rating_state = \'visible\'
				AND rating.is_review = 1
		');

		$simpleCache['XenAddons/AMS']['statisticsCache'] = [
			'category_count' => $categoryCount,
			'article_count' => $articleCount,
			'series_count' => $seriesCount,
			'view_count' => $viewCount,
			'comment_count' => $commentCount,
			'rating_count' => $ratingCount,
			'review_count' => $reviewCount,
		];
	}
}