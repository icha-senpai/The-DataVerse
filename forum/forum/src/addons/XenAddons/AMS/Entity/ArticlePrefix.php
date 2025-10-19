<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\AbstractPrefix;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null prefix_id
 * @property int prefix_group_id
 * @property int display_order
 * @property int materialized_order
 * @property string css_class
 * @property array allowed_user_group_ids
 *
 * GETTERS
 * @property \XF\Phrase|string title
 * @property bool has_usage_help
 * @property string|\XF\Phrase description
 * @property string|\XF\Phrase usage_help
 * @property array category_ids
 *
 * RELATIONS
 * @property \XF\Entity\Phrase MasterTitle
 * @property \XenAddons\AMS\Entity\ArticlePrefixGroup PrefixGroup
 * @property \XF\Entity\Phrase MasterDescription
 * @property \XF\Entity\Phrase MasterUsageHelp
 * @property \XenAddons\AMS\Entity\CategoryPrefix[] CategoryPrefixes
 */
class ArticlePrefix extends AbstractPrefix
{
	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ArticlePrefix';
	}

	protected static function getContentType()
	{
		return 'ams_article';
	}

	/**
	 * @return array
	 */
	public function getCategoryIds()
	{
		if (!$this->prefix_id)
		{
			return [];
		}

		return $this->db()->fetchAllColumn("
			SELECT category_id
			FROM xf_xa_ams_category_prefix
			WHERE prefix_id = ?
		", $this->prefix_id);
	}

	protected function _postDelete()
	{
		parent::_postDelete();

		$this->repository('XenAddons\AMS:CategoryPrefix')->removePrefixAssociations($this);
	}

	public static function getStructure(Structure $structure)
	{
		self::setupDefaultStructure($structure, 'xf_xa_ams_article_prefix', 'XenAddons\AMS:ArticlePrefix', [
			'has_description' => true,
			'has_usage_help' => true
		]);
		
		$structure->getters['category_ids'] = true;

		$structure->relations['CategoryPrefixes'] = [
			'entity' => 'XenAddons\AMS:CategoryPrefix',
			'type' => self::TO_MANY,
			'conditions' => 'prefix_id'
		];

		return $structure;
	}
}