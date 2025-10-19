<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XF\Repository\AbstractFieldMap;

class CategoryField extends AbstractFieldMap
{
	protected function getMapEntityIdentifier()
	{
		return 'XenAddons\AMS:CategoryField';
	}

	protected function getAssociationsForField(\XF\Entity\AbstractField $field)
	{
		return $field->getRelation('CategoryFields');
	}

	protected function updateAssociationCache(array $cache)
	{
		$categoryIds = array_keys($cache);
		$categories = $this->em->findByIds('XenAddons\AMS:Category', $categoryIds);

		foreach ($categories AS $category)
		{
			/** @var \XenAddons\AMS\Entity\Category $category */
			$category->field_cache = $cache[$category->category_id];
			$category->saveIfChanged();
		}
	}
}