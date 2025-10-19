<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\AbstractPrefixGroup;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null prefix_group_id
 * @property int display_order
 *
 * GETTERS
 * @property \XF\Phrase|string title
 *
 * RELATIONS
 * @property \XF\Entity\Phrase MasterTitle
 * @property \XenAddons\AMS\Entity\ArticlePrefix[] Prefixes
 */
class ArticlePrefixGroup extends AbstractPrefixGroup
{
	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ArticlePrefix';
	}

	protected static function getContentType()
	{
		return 'ams_article';
	}

	public static function getStructure(Structure $structure)
	{
		self::setupDefaultStructure(
			$structure,
			'xf_xa_ams_article_prefix_group',
			'XenAddons\AMS:ArticlePrefixGroup',
			'XenAddons\AMS:ArticlePrefix'
		);

		return $structure;
	}
}