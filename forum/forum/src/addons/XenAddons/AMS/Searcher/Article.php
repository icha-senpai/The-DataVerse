<?php

namespace XenAddons\AMS\Searcher;

use XF\Mvc\Entity\Finder;
use XF\Searcher\AbstractSearcher;

/**
 * @method \XenAddons\AMS\Finder\ArticleItem getFinder()
 */
class Article extends AbstractSearcher
{
	protected $allowedRelations = ['Category'];

	protected $formats = [
		'title' => 'like',
		'description' => 'like',
		'username' => 'like',
		'publish_date' => 'date',
	];

	protected $whitelistOrder = [
		'title' => true,
		'username' => true,
		'publish_date' => true,
		'page_count' => true,
		'comment_count' => true,
		'rating_count' => true,
		'review_count' => true,
		'view_count' => true,
		'reaction_score' => true
	];

	protected $order = [['publish_date', 'desc']];

	protected function getEntityType()
	{
		return 'XenAddons\AMS:ArticleItem';
	}

	protected function getDefaultOrderOptions()
	{
		return [
			'publish_date' => \XF::phrase('date'),
			'title' => \XF::phrase('title'),
			'page_count' => \XF::phrase('xa_ams_pages'),
			'comment_count' => \XF::phrase('comments'),
			'rating_count' => \XF::phrase('xa_ams_ratings'),
			'review_count' => \XF::phrase('xa_ams_reviews'),
			'view_count' => \XF::phrase('views'),
			'reaction_score' => \XF::phrase('reaction_score')
		];
	}

	protected function applySpecialCriteriaValue(Finder $finder, $key, $value, $column, $format, $relation)
	{
		if ($key == 'prefix_id' && $value == -1)
		{
			// any prefix so skip condition
			return true;
		}

		if ($key == 'category_id' && $value == 0)
		{
			// any node so skip condition
			return true;
		}

		if ($key == 'article_field')
		{
			$exactMatchFields = !empty($value['exact']) ? $value['exact'] : [];
			$customFields = array_merge($value, $exactMatchFields);
			unset($customFields['exact']);

			$conditions = [];
			foreach ($customFields AS $fieldId => $value)
			{
				if ($value === '' || (is_array($value) && !$value))
				{
					continue;
				}

				$finder->with('CustomFields|' . $fieldId);
				$isExact = !empty($exactMatchFields[$fieldId]);

				foreach ((array)$value AS $possible)
				{
					$columnName = 'CustomFields|' . $fieldId . '.field_value';
					if ($isExact)
					{
						$conditions[] = [$columnName, '=', $possible];
					}
					else
					{
						$conditions[] = [$columnName, 'LIKE', $finder->escapeLike($possible, '%?%')];
					}
				}
			}
			if ($conditions)
			{
				$finder->whereOr($conditions);
			}
		}

		return false;
	}

	public function getFormData()
	{
		/** @var \XenAddons\AMS\Repository\ArticlePrefix $prefixRepo */
		$prefixRepo = $this->em->getRepository('XenAddons\AMS:ArticlePrefix');
		$prefixes = $prefixRepo->getPrefixListData();

		/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
		$categoryRepo = $this->em->getRepository('XenAddons\AMS:Category');
		$categories = $categoryRepo->getCategoryOptionsData(false);
		
		return [
			'prefixes' => $prefixes,
			'categories' => $categories
		];
	}

	public function getFormDefaults()
	{
		return [
			'prefix_id' => -1,
			'category_id' => 0,

			'page_count' => ['end' => -1],
			'comment_count' => ['end' => -1],
			'rating_count' => ['end' => -1],
			'review_count' => ['end' => -1],
			'view_count' => ['end' => -1],

			'article_state' => ['visible', 'moderated', 'deleted'],
			'comments_open' => [0, 1],
			'ratings_open' => [0, 1],
		];
	}
}