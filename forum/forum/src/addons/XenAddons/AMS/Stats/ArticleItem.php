<?php

namespace XenAddons\AMS\Stats;

use XF\Stats\AbstractHandler;

class ArticleItem extends AbstractHandler
{
	public function getStatsTypes()
	{
		return [
			'ams_article' => \XF::phrase('xa_ams_articles'),
			'ams_article_reaction' => \XF::phrase('xa_ams_article_reactions'),
			
			'ams_comment' => \XF::phrase('xa_ams_comments'), 
			'ams_comment_reaction' => \XF::phrase('xa_ams_comment_reactions'), 
			
			'ams_rating' => \XF::phrase('xa_ams_ratings'),
			'ams_review' => \XF::phrase('xa_ams_reviews'),
			'ams_review_reaction' => \XF::phrase('xa_ams_review_reactions'),
			
			'ams_series' => \XF::phrase('xa_ams_series'),
			'ams_series_part' => \XF::phrase('xa_ams_series_parts'),
			'ams_series_reaction' => \XF::phrase('xa_ams_series_reactions'),
		];
	}

	public function getData($start, $end)
	{
		$db = $this->db();

		$articles = $db->fetchPairs(
			$this->getBasicDataQuery('xf_xa_ams_article', 'publish_date', 'article_state = ?'),
			[$start, $end, 'visible']
		);

		$articleReactions = $db->fetchPairs(
			$this->getBasicDataQuery('xf_reaction_content', 'reaction_date', 'content_type = ?'),
			[$start, $end, 'ams_article']
		);
		
		$comments = $db->fetchPairs(
			$this->getBasicDataQuery('xf_xa_ams_comment', 'comment_date', 'comment_state = ?'),
			[$start, $end, 'visible']
		);
		
		$commentReactions = $db->fetchPairs(
			$this->getBasicDataQuery('xf_reaction_content', 'reaction_date', 'content_type = ?'),
			[$start, $end, 'ams_comment']
		);

		$ratings = $db->fetchPairs(
			$this->getBasicDataQuery('xf_xa_ams_article_rating', 'rating_date', 'rating_state = ? AND is_review = 0'),
			[$start, $end, 'visible']
		);
		
		$reviews = $db->fetchPairs(
			$this->getBasicDataQuery('xf_xa_ams_article_rating', 'rating_date', 'rating_state = ? AND is_review = 1'),
			[$start, $end, 'visible']
		);
		
		$reviewReactions = $db->fetchPairs(
			$this->getBasicDataQuery('xf_reaction_content', 'reaction_date', 'content_type = ?'),
			[$start, $end, 'ams_rating']
		);
		
		$series = $db->fetchPairs(
			$this->getBasicDataQuery('xf_xa_ams_series', 'create_date', ''),
			[$start, $end]
		);
		
		$seriesParts = $db->fetchPairs(
			$this->getBasicDataQuery('xf_xa_ams_series_part', 'create_date', ''),
			[$start, $end]
		);
		
		$seriesReactions = $db->fetchPairs(
			$this->getBasicDataQuery('xf_reaction_content', 'reaction_date', 'content_type = ?'),
			[$start, $end, 'ams_series']
		);
		

		return [
			'ams_article' => $articles,
			'ams_article_reaction' => $articleReactions,

			'ams_comment' => $comments,
			'ams_comment_reaction' => $commentReactions,

			'ams_rating' => $ratings,
			'ams_review' => $reviews,
			'ams_review_reaction' => $reviewReactions,
			
			'ams_series' => $series,
			'ams_series_part' => $seriesParts,
			'ams_series_reaction' => $seriesReactions,
		];
	}
}