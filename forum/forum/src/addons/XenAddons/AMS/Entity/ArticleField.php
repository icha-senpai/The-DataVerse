<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\AbstractField;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property string field_id
 * @property int display_order
 * @property string field_type
 * @property array field_choices
 * @property string match_type
 * @property array match_params
 * @property int max_length
 * @property bool required
 * @property string display_template
 * @property string wrapper_template
 * @property string display_group
 * @property bool hide_title
 * @property bool display_on_list
 * @property array editable_user_group_ids
 *
 * GETTERS
 * @property \XF\Phrase title
 * @property \XF\Phrase description
 *
 * RELATIONS
 * @property \XF\Entity\Phrase MasterTitle
 * @property \XF\Entity\Phrase MasterDescription
 * @property \XenAddons\AMS\Entity\CategoryField[] CategoryFields
 */
class ArticleField extends AbstractField
{
	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ArticleField';
	}

	protected static function getPhrasePrefix()
	{
		return 'xa_ams_article_field';
	}

	protected function _postDelete()
	{
		/** @var \XenAddons\AMS\Repository\CategoryField $repo */
		$repo = $this->repository('XenAddons\AMS:CategoryField');
		$repo->removeFieldAssociations($this);

		$this->db()->delete('xf_xa_ams_article_field_value', 'field_id = ?', $this->field_id);

		parent::_postDelete();
	}

	public static function getStructure(Structure $structure)
	{
		self::setupDefaultStructure(
			$structure,
			'xf_xa_ams_article_field',
			'XenAddons\AMS:ArticleField',
			[
				'groups' => ['header', 'above_article', 'below_article', 'sidebar', 'new_tab', 'self_place'],
				'has_user_group_editable' => true,
				'has_wrapper_template' => true
			]
		);

		$structure->columns['hide_title'] = ['type' => self::BOOL, 'default' => false];
		$structure->columns['display_on_list'] = ['type' => self::BOOL, 'default' => false];
		$structure->columns['display_on_tab'] = ['type' => self::BOOL, 'default' => false];
		$structure->columns['display_on_tab_field_id'] = ['type' => self::STR, 'default' => ''];

		$structure->relations['CategoryFields'] = [
			'entity' => 'XenAddons\AMS:CategoryField',
			'type' => self::TO_MANY,
			'conditions' => 'field_id'
		];

		return $structure;
	}
}