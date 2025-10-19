<?php

namespace XenAddons\AMS\Search\Data;

use XF\Mvc\Entity\Entity;
use XF\Search\Data\AbstractData;
use XF\Search\IndexRecord;
use XF\Search\MetadataStructure;

class Rating extends AbstractData
{
	public function getEntityWith($forView = false)
	{
		$get = ['User', 'Article', 'Article.Category'];
		if ($forView)
		{
			$visitor = \XF::visitor();
			$get[] = 'Article.Category.Permissions|' . $visitor->permission_combination_id;
		}
		
		return $get;
	}

	public function getIndexData(Entity $entity)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
		
		if (!$entity->Article || !$entity->Article->Category)
		{
			return null;
		}
		
		/** @var \XenAddons\AMS\Entity\ArticleItem $article */
		$article = $entity->Article;

		$index = IndexRecord::create('ams_rating', $entity->rating_id, [
			'title' => '',
			'message' => $entity->message_,
			'date' => $entity->rating_date,
			'user_id' => $entity->user_id,
			'discussion_id' => $entity->article_id,
			'metadata' => $this->getMetaData($entity)
		]);

		if (!$entity->isVisible())
		{
			$index->setHidden();
		}

		return $index;
	}
	
	protected function getMetaData(\XenAddons\AMS\Entity\ArticleRating $entity)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $article */
		$article = $entity->Article;
	
		$metadata = [
			'articlecat' => $article->category_id,
			'article' => $article->article_id
		];
		if ($article->prefix_id)
		{
			$metadata['articleprefix'] = $article->prefix_id;
		}
	
		return $metadata;
	}
	
	public function setupMetadataStructure(MetadataStructure $structure)
	{
		$structure->addField('articlecat', MetadataStructure::INT);
		$structure->addField('article', MetadataStructure::INT);
		$structure->addField('articleprefix', MetadataStructure::INT);
	}

	public function getResultDate(Entity $entity)
	{
		return $entity->rating_date;
	}

	public function getTemplateData(Entity $entity, array $options = [])
	{
		return [
			'review' => $entity,
			'article' => $entity->Article,
			'options' => $options
		];
	}

	public function canUseInlineModeration(Entity $entity, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\ArticleRating $entity */
		return $entity->canUseInlineModeration($error);
	}
}