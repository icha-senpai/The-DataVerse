<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Repository;
use XF\Mvc\Entity\Finder;

class ArticleReplyBan extends Repository
{
	/**
	 * @return Finder
	 */
	public function findReplyBansForList()
	{
		$finder = $this->finder('XenAddons\AMS:ArticleReplyBan');
		$finder->setDefaultOrder('ban_date', 'DESC')
			->with('ArticleItem', true);
		return $finder;
	}

	/**
	 * @return Finder
	 */
	public function findReplyBansForArticle(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		$finder = $this->findReplyBansForList();
		$finder->where('article_id', $article->article_id)
			->with(['User', 'BannedBy']);
		return $finder;
	}

	public function cleanUpExpiredBans($cutOff = null)
	{
		if ($cutOff === null)
		{
			$cutOff = time();
		}
		$this->db()->delete('xf_xa_ams_article_reply_ban', 'expiry_date > 0 AND expiry_date < ?', $cutOff);
	}
}