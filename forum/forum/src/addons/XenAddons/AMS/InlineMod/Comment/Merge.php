<?php

namespace XenAddons\AMS\InlineMod\Comment;

use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;

class Merge extends AbstractAction
{
	public function getTitle()
	{
		return \XF::phrase('xa_ams_merge_comments...');
	}

	protected function canApplyInternal(AbstractCollection $entities, array $options, &$error)
	{
		$result = parent::canApplyInternal($entities, $options, $error);

		if ($result)
		{
			if ($options['target_comment_id'])
			{
				if (!isset($entities[$options['target_comment_id']]))
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
		/** @var \XenAddons\AMS\Entity\Comment $entity */
		return $entity->canMerge($error);
	}

	public function applyInternal(AbstractCollection $entities, array $options)
	{
		if (!$options['target_comment_id'])
		{
			throw new \InvalidArgumentException("No target comment selected");
		}

		$source = $entities->toArray();
		$target = $source[$options['target_comment_id']];
		unset($source[$options['target_comment_id']]);

		/** @var \XenAddons\AMS\Service\Comment\Merger $merger */
		$merger = $this->app()->service('XenAddons\AMS:Comment\Merger', $target);

		$merger->setMessage($options['message']);

		if ($options['alert'])
		{
			$merger->setSendAlert(true, $options['alert_reason']);
		}

		$merger->merge($source);
	}

	protected function applyToEntity(Entity $entity, array $options)
	{
		throw new \LogicException("applyToEntity should not be called on comment merging");
	}

	public function getBaseOptions()
	{
		return [
			'target_comment_id' => 0,
			'message' => '',
			'alert' => false,
			'alert_reason' => ''
		];
	}

	public function renderForm(AbstractCollection $entities, \XF\Mvc\Controller $controller)
	{
		$viewParams = [
			'comments' => $entities,
			'message' => trim(implode("\n\n", $entities->pluckNamed('message'))),

			'total' => count($entities),
			'first' => $entities->first()
		];
		return $controller->view('XenAddons\AMS:Public:InlineMod\Comment\Merge', 'xa_ams_inline_mod_comment_merge', $viewParams);
	}

	public function getFormOptions(AbstractCollection $entities, Request $request)
	{
		$options = [
			'target_comment_id' => $request->filter('target_comment_id', 'uint'),
			'message' => $request->filter('message', 'str'),
			'alert' => $request->filter('author_alert', 'bool'),
			'alert_reason' => $request->filter('author_alert_reason', 'str')
		];

		return $options;
	}
}