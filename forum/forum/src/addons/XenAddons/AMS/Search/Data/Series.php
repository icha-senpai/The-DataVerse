<?php

namespace XenAddons\AMS\Search\Data;

use XF\Mvc\Entity\Entity;
use XF\Search\Data\AbstractData;
use XF\Search\IndexRecord;
use XF\Search\MetadataStructure;

class Series extends AbstractData
{
	public function getEntityWith($forView = false)
	{
		return ['User'];
	}

	public function getIndexData(Entity $entity)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $entity */

		$index = IndexRecord::create('ams_series', $entity->series_id, [
			'title' => $entity->title_,
			'message' => $entity->description_ . ' ' . $entity->message_,
			'date' => $entity->create_date,
			'user_id' => $entity->user_id,
			'discussion_id' => $entity->series_id,
			'metadata' => $this->getMetaData($entity)
		]);

		if ($entity->tags)
		{
			$index->indexTags($entity->tags);
		}

		return $index;
	}
	
	protected function getMetaData(\XenAddons\AMS\Entity\SeriesItem $entity)
	{
		$metadata = [
			'series' => $entity->series_id
		];

		return $metadata;
	}
	
	public function setupMetadataStructure(MetadataStructure $structure)
	{
		$structure->addField('series', MetadataStructure::INT);
	}

	public function getResultDate(Entity $entity)
	{
		return $entity->create_date;
	}

	public function getTemplateData(Entity $entity, array $options = [])
	{
		return [
			'series' => $entity, 
			'options' => $options
		];
	}

	public function canUseInlineModeration(Entity $entity, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $entity */
		return $entity->canUseInlineModeration($error);
	}
}