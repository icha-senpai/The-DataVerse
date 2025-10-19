<?php

namespace XenAddons\AMS\Search\Data;

use XF\Mvc\Entity\Entity;
use XF\Search\Data\AbstractData;
use XF\Search\IndexRecord;
use XF\Search\MetadataStructure;

class Comment extends AbstractData
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
		/** @var \XenAddons\AMS\Entity\Comment $entity */
		
		if (!$entity->Content || !$entity->Content->Category)
		{
			return null;
		}
		
		/** @var \XenAddons\AMS\Entity\ArticleItem $article */
		$article = $entity->Content;

		$index = IndexRecord::create('ams_comment', $entity->comment_id, [
			'title' => '',
			'message' => $entity->message_,
			'date' => $entity->comment_date,
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

	protected function getMetaData(\XenAddons\AMS\Entity\Comment $entity)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $article */
		$article = $entity->Content;
	
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
		return $entity->comment_date;
	}

	public function getTemplateData(Entity $entity, array $options = [])
	{
		return [
			'comment' => $entity,
			'article' => $entity->Content,
			'options' => $options
		];
	}

	public function canUseInlineModeration(Entity $entity, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\Comment $entity */
		return $entity->canUseInlineModeration($error);
	}
}