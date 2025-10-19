<?php

namespace XenAddons\AMS\Repository;

use XF\Repository\AbstractCategoryTree;

class Category extends AbstractCategoryTree
{
	protected function getClassName()
	{
		return 'XenAddons\AMS:Category';
	}

	public function mergeCategoryListExtras(array $extras, array $childExtras)
	{
		$output = array_merge([
			'childCount' => 0,
			'article_count' => 0,
			'last_article_date' => 0,
			'last_article_title' => '',
			'last_article_id' => 0
		], $extras);

		foreach ($childExtras AS $child)
		{
			if (!empty($child['article_count']))
			{
				$output['article_count'] += $child['article_count'];
			}

			if (!empty($child['last_article_date']) && $child['last_article_date'] > $output['last_article_date'])
			{
				$output['last_article_date'] = $child['last_article_date'];
				$output['last_article_title'] = $child['last_article_title'];
				$output['last_article_id'] = $child['last_article_id'];
			}

			$output['childCount'] += 1 + (!empty($child['childCount']) ? $child['childCount'] : 0);
		}

		return $output;
	}
}