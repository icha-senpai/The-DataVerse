<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XF\Repository\AbstractFieldMap;

class CategoryReviewField extends AbstractFieldMap
{
	protected function getMapEntityIdentifier()
	{
		return 'XenAddons\AMS:CategoryReviewField';
	}

	protected function getAssociationsForField(\XF\Entity\AbstractField $field)
	{
		return $field->getRelation('CategoryReviewFields');
	}

	protected function updateAssociationCache(array $cache)
	{
		$categoryIds = array_keys($cache);
		$categories = $this->em->findByIds('XenAddons\AMS:Category', $categoryIds);

		foreach ($categories AS $category)
		{
			/** @var \XenAddons\AMS\Entity\Category $category */
			$category->review_field_cache = $cache[$category->category_id];
			$category->saveIfChanged();
		}
	}
}