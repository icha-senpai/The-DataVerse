<?php

namespace XenAddons\AMS\InlineMod\ArticleItem;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;

class Merge extends AbstractAction
{
	public function getTitle()
	{
		return \XF::phrase('xa_ams_merge_articles...');
	}
	
	protected function canApplyInternal(AbstractCollection $entities, array $options, &$error)
	{
		$result = parent::canApplyInternal($entities, $options, $error);
		
		if ($result)
		{
			if ($options['target_article_id'])
			{
				if (!isset($entities[$options['target_article_id']]))
				{
					return false;
				}
			}

			if ($entities->count() < 2)
			{
				return false;
			}
		}
		
		return $result;
	}

	protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
		return $entity->canMerge($error);
	}

	public function applyInternal(AbstractCollection $entities, array $options)
	{
		if (!$options['target_article_id'])
		{
			throw new \InvalidArgumentException("No target article selected");
		}

		$source = $entities->toArray();
		$target = $source[$options['target_article_id']];
		unset($source[$options['target_article_id']]);

		/** @var \XenAddons\AMS\Service\ArticleItem\Merger $merger */
		$merger = $this->app()->service('XenAddons\AMS:ArticleItem\Merger', $target);

		if ($options['alert'])
		{
			$merger->setSendAlert(true, $options['alert_reason']);
		}

		$merger->merge($source);

		$this->returnUrl = $this->app()->router()->buildLink('ams', $target);
	}

	protected function applyToEntity(Entity $entity, array $options)
	{
		throw new \LogicException("applyToEntity should not be called on article merging");
	}

	public function getBaseOptions()
	{
		return [
			'target_article_id' => 0,
			'alert' => false,
			'alert_reason' => ''
		];
	}

	public function renderForm(AbstractCollection $entities, \XF\Mvc\Controller $controller)
	{
		$viewParams = [
			'articles' => $entities,
			'total' => count($entities),
			'first' => $entities->first()
		];
		return $controller->view('XenAddons\AMS:Public:InlineMod\ArticleItem\Merge', 'xa_ams_inline_mod_article_merge', $viewParams);
	}

	public function getFormOptions(AbstractCollection $entities, Request $request)
	{
		$options = [
			'target_article_id' => $request->filter('target_article_id', 'uint'),
			'alert' => $request->filter('starter_alert', 'bool'),
			'alert_reason' => $request->filter('starter_alert_reason', 'str')
		];

		return $options;
	}
}