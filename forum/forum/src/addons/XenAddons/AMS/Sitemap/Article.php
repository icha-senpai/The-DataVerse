<?php

namespace XenAddons\AMS\Sitemap;

use XF\Sitemap\AbstractHandler;
use XF\Sitemap\Entry;

class Article extends AbstractHandler
{
	public function getRecords($start)
	{
		$user = \XF::visitor();

		$ids = $this->getIds('xf_xa_ams_article', 'article_id', $start);

		$finder = $this->app->finder('XenAddons\AMS:ArticleItem');
		$articles = $finder
			->where('article_id', $ids)
			->with(['Category', 'Category.Permissions|' . $user->permission_combination_id])
			->order('article_id')
			->fetch();

		return $articles;
	}

	/**
	 * @param $record \XenAddons\AMS\Entity\ArticleItem
	 *
	 * @return Entry
	 */
	public function getEntry($record)
	{
		$url = $this->app->router('public')->buildLink('canonical:ams', $record);
		return Entry::create($url, [
			'lastmod' => $record->last_update
		]);
	}

	public function isIncluded($record)
	{
		/** @var $record \XenAddons\AMS\Entity\ArticleItem */
		if (!$record->isVisible())
		{
			return false;
		}
		return $record->canView();
	}
}