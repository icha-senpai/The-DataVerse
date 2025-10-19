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
 * @property array editable_user_group_ids
 *
 * GETTERS
 * @property \XF\Phrase title
 * @property \XF\Phrase description
 *
 * RELATIONS
 * @property \XF\Entity\Phrase MasterTitle
 * @property \XF\Entity\Phrase MasterDescription
 * @property \XenAddons\AMS\Entity\CategoryReviewField[] CategoryReviewFields
 */
class ReviewField extends AbstractField
{
	protected function getClassIdentifier()
	{
		return 'XenAddons\AMS:ReviewField';
	}

	protected static function getPhrasePrefix()
	{
		return 'xa_ams_review_field';
	}

	protected function _postDelete()
	{
		/** @var \XenAddons\AMS\Repository\CategoryReviewField $repo */
		$repo = $this->repository('XenAddons\AMS:CategoryReviewField');
		$repo->removeFieldAssociations($this);

		$this->db()->delete('xf_xa_ams_review_field_value', 'field_id = ?', $this->field_id);

		parent::_postDelete();
	}

	public static function getStructure(Structure $structure)
	{
		self::setupDefaultStructure(
			$structure,
			'xf_xa_ams_review_field',
			'XenAddons\AMS:ReviewField',
			[
				'groups' => ['top', 'middle', 'bottom', 'self_place'],
				'has_user_group_editable' => true,
				'has_wrapper_template' => true
			]
		);

		$structure->relations['CategoryReviewFields'] = [
			'entity' => 'XenAddons\AMS:CategoryReviewField',
			'type' => self::TO_MANY,
			'conditions' => 'field_id'
		];

		return $structure;
	}
}