<?php

namespace XenAddons\AMS\Repository;

use XF\Repository\AbstractPrefix;

class ArticlePrefix extends AbstractPrefix
{
	protected function getRegistryKey()
	{
		return 'xa_amsPrefixes';
	}

	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ArticlePrefix';
	}
	
	public function getVisiblePrefixListData()
	{
		if (!method_exists($this, '_getVisiblePrefixListData'))
		{
			// in case this version of AMS is used on an older version of XF.
			return $this->getPrefixListData();
		}
	
		$categories = $this->finder('XenAddons\AMS:Category')
			->with('Permissions|' . \XF::visitor()->permission_combination_id)
			->fetch();
	
		$prefixMap = $this->finder('XenAddons\AMS:CategoryPrefix')
			->fetch()
			->groupBy('prefix_id', 'category_id');
	
		$isVisibleClosure = function(\XenAddons\AMS\Entity\ArticlePrefix $prefix) use ($prefixMap, $categories)
		{
			if (!isset($prefixMap[$prefix->prefix_id]))
			{
				return false;
			}
	
			$isVisible = false;
	
			foreach ($prefixMap[$prefix->prefix_id] AS $categoryPrefix)
			{
				/** @var \XenAddons\AMS\Entity\CategoryPrefix $categoryPrefix */
	
				if (!isset($categories[$categoryPrefix->category_id]))
				{
					continue;
				}
	
				$isVisible = $categories[$categoryPrefix->category_id]->canView();
	
				if ($isVisible)
				{
					break;
				}
			}
	
			return $isVisible;
		};
		return $this->_getVisiblePrefixListData($isVisibleClosure);
	}	
}