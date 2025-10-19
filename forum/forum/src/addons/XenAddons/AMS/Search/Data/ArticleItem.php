<?php

namespace XenAddons\AMS\Search\Data;

use XF\Mvc\Entity\Entity;
use XF\Search\Data\AbstractData;
use XF\Search\IndexRecord;
use XF\Search\MetadataStructure;
use XF\Search\Query\MetadataConstraint;

class ArticleItem extends AbstractData
{
	public function getEntityWith($forView = false)
	{
		$get = ['Category'];
		if ($forView)
		{
			$get[] = 'CoverImage';
			$get[] = 'User';

			$visitor = \XF::visitor();
			$get[] = 'Category.Permissions|' . $visitor->permission_combination_id;
		}

		return $get;
	}

	public function getIndexData(Entity $entity)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $entity */

		if (!$entity->Category)
		{
			return null;
		}

		$index = IndexRecord::create('ams_article', $entity->article_id, [
			'title' => $entity->title_,
			'message' => $entity->description_ . ' ' . $entity->message_,
			'date' => $entity->publish_date,
			'user_id' => $entity->user_id,
			'discussion_id' => $entity->article_id,
			'metadata' => $this->getMetaData($entity)
		]);

		if (!$entity->isVisible())
		{
			$index->setHidden();
		}

		if ($entity->tags)
		{
			$index->indexTags($entity->tags);
		}

		return $index;
	}

	protected function getMetaData(\XenAddons\AMS\Entity\ArticleItem $entity)
	{
		$metadata = [
			'article' => $entity->article_id,
			'articlecat' => $entity->category_id
		];
		if ($entity->prefix_id)
		{
			$metadata['articleprefix'] = $entity->prefix_id;
		}
		if ($entity->series_part_id)
		{
			$metadata['series'] = $entity->SeriesPart->series_id;
		}
		
		return $metadata;
	}

	public function setupMetadataStructure(MetadataStructure $structure)
	{
		$structure->addField('article', MetadataStructure::INT);
		$structure->addField('articlecat', MetadataStructure::INT);
		$structure->addField('articleprefix', MetadataStructure::INT);
		$structure->addField('series', MetadataStructure::INT);
	}

	public function getResultDate(Entity $entity)
	{
		return $entity->publish_date;
	}

	public function getTemplateData(Entity $entity, array $options = [])
	{
		return [
			'article' => $entity,
			'options' => $options
		];
	}

	public function canUseInlineModeration(Entity $entity, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
		return $entity->canUseInlineModeration($error);
	}

	public function getSearchableContentTypes()
	{
		return ['ams_article', 'ams_comment', 'ams_page', 'ams_rating', 'ams_series']; // TODO add ams_rating_replies once that functionality is added!
	}

	public function getSearchFormTab()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		if (!method_exists($visitor, 'canViewAmsArticles') || !$visitor->canViewAmsArticles())
		{
			return null;
		}

		return [
			'title' => \XF::phrase('xa_ams_search_articles'),
			'order' => 131 // AMS = 131, UBS = 132, RMS = 133, Showcase = 134
		];
	}

	public function getSectionContext()
	{
		return 'xa_ams';
	}

	public function getSearchFormData()
	{
		$prefixListData = $this->getPrefixListData();

		return [
			'prefixGroups' => $prefixListData['prefixGroups'],
			'prefixesGrouped' => $prefixListData['prefixesGrouped'],

			'categoryTree' => $this->getSearchableCategoryTree()
		];
	}

	/**
	 * @return \XF\Tree
	 */
	protected function getSearchableCategoryTree()
	{
		/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
		$categoryRepo = \XF::repository('XenAddons\AMS:Category');
		return $categoryRepo->createCategoryTree($categoryRepo->getViewableCategories());
	}

	protected function getPrefixListData()
	{
		/** @var \XenAddons\AMS\Repository\ArticlePrefix $prefixRepo */
		$prefixRepo = \XF::repository('XenAddons\AMS:ArticlePrefix');
		return $prefixRepo->getVisiblePrefixListData();
	}

	public function applyTypeConstraintsFromInput(\XF\Search\Query\Query $query, \XF\Http\Request $request, array &$urlConstraints)
	{
		$series = $request->filter('c.series', 'array-uint');
		$series = array_unique($series);
		if ($series && reset($series))
		{
			$query->withMetadata('series', $series);
		}
		else
		{
			unset($urlConstraints['series']);
		}
		
		$prefixes = $request->filter('c.prefixes', 'array-uint');
		$prefixes = array_unique($prefixes);
		if ($prefixes && reset($prefixes))
		{
			$query->withMetadata('articleprefix', $prefixes);
		}
		else
		{
			unset($urlConstraints['prefixes']);
		}

		$categoryIds = $request->filter('c.categories', 'array-uint');
		$categoryIds = array_unique($categoryIds);
		if ($categoryIds && reset($categoryIds))
		{
			if ($request->filter('c.child_categories', 'bool'))
			{
				$categoryTree = $this->getSearchableCategoryTree();

				$searchCategoryIds = array_fill_keys($categoryIds, true);
				$categoryTree->traverse(function($id, $category) use (&$searchCategoryIds)
				{
					if (isset($searchCategoryIds[$id]) || isset($searchCategoryIds[$category->parent_category_id]))
					{
						$searchCategoryIds[$id] = true;
					}
				});

				$categoryIds = array_unique(array_keys($searchCategoryIds));
			}
			else
			{
				unset($urlConstraints['child_categories']);
			}

			$query->withMetadata('articlecat', $categoryIds);
		}
		else
		{
			unset($urlConstraints['categories']);
			unset($urlConstraints['child_categories']);
		}

		$includeComments = $request->filter('c.include_comments', 'bool');
		$includeReviews = $request->filter('c.include_reviews', 'bool');
		
		if (!$includeComments && !$includeReviews)
		{
			$query->inType('ams_article');
			unset($urlConstraints['include_comments']);
			unset($urlConstraints['include_reviews']);
		}
		elseif (!$includeComments && $includeReviews)
		{
			$query->inTypes(['ams_article', 'ams_rating']);
			unset($urlConstraints['include_comments']);
		}
		elseif (!$includeReviews && $includeComments)
		{
			$query->inTypes(['ams_article', 'ams_comment']);
			unset($urlConstraints['include_reviews']);
		}
	}

	public function getTypePermissionConstraints(\XF\Search\Query\Query $query, $isOnlyType)
	{
		/** @var \XenAddons\AMS\Repository\Category $categoryRepo */
		$categoryRepo = \XF::repository('XenAddons\AMS:Category');

		$with = ['Permissions|' . \XF::visitor()->permission_combination_id];
		$categories = $categoryRepo->findCategoryList(null, $with)->fetch();

		$skip = [];
		foreach ($categories AS $category)
		{
			/** @var \XenAddons\AMS\Entity\Category $category */
			if (!$category->canView())
			{
				$skip[] = $category->category_id;
			}
		}

		if ($skip)
		{
			return [
				new MetadataConstraint('articlecat', $skip, MetadataConstraint::MATCH_NONE)
			];
		}
		else
		{
			return [];
		}
	}

	public function getGroupByType()
	{
		return 'ams_article';
	}
}