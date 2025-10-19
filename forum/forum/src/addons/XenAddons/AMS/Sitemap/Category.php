<?php

namespace XenAddons\AMS\Sitemap;

use XF\Sitemap\AbstractHandler;
use XF\Sitemap\Entry;

class Category extends AbstractHandler
{
	public function getRecords($start)
	{
		$user = \XF::visitor();

		$ids = $this->getIds('xf_xa_ams_category', 'category_id', $start);

		$finder = $this->app->finder('XenAddons\AMS:Category');
		$categories = $finder
			->where('category_id', $ids)
			->with(['Permissions|' . $user->permission_combination_id])
			->order('category_id')
			->fetch();

		return $categories;
	}

	/**
	 * @param $record \XenAddons\AMS\Entity\Category
	 *
	 * @return Entry
	 */
	public function getEntry($record)
	{
		$router = $this->app->router('public');
		$url = $router->buildLink('canonical:ams/categories', $record);

		return Entry::create($url);
	}

	public function isIncluded($record)
	{
		/** @var $record \XenAddons\AMS\Entity\Category */
		return $record->canView();
	}
}