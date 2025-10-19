<?php

namespace XenAddons\AMS\Tag;

use XF\Mvc\Entity\Entity;
use XF\Tag\AbstractHandler;

class SeriesItem extends AbstractHandler
{
	public function getPermissionsFromContext(Entity $entity)
	{
		if ($entity instanceof \XenAddons\AMS\Entity\SeriesItem)
		{
			$series = $entity;
		}
		else
		{
			throw new \InvalidArgumentException("Entity must be a series");
		}

		$visitor = \XF::visitor();
		$options = $entity->app()->options();
		
		if ($series)
		{
			if ($series->user_id == $visitor->user_id && $series->hasPermission('manageOthersTagsOwnSeries'))
			{
				$removeOthers = true;
			}
			else
			{
				$removeOthers = $series->hasPermission('manageAnySeriesTag');
			}

			$edit = $series->canEditTags();
		}
		else
		{
			$removeOthers = false;
			$edit = false;
		}

		return [
			'edit' => $edit,
			'removeOthers' => $removeOthers,
			'minTotal' => $options->xaAmsSeriesMinTags, 
		];
	}

	public function getContentDate(Entity $entity)
	{
		return $entity->create_date;
	}

	public function getContentVisibility(Entity $entity)
	{
		return true; // series are always visible
	}

	public function getTemplateData(Entity $entity, array $options = [])
	{
		return [
			'series' => $entity,
			'options' => $options
		];
	}

	public function getEntityWith($forView = false)
	{
		$get = [];
		if ($forView)
		{
			$get[] = 'User';
		}

		return $get;
	}

	public function canUseInlineModeration(Entity $entity, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\SeriesItem $entity */
		return $entity->canUseInlineModeration($error);
	}
}