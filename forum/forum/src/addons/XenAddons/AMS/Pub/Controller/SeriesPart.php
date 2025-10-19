<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class SeriesPart extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
	
		if (!$visitor->canViewAmsArticles($error))
		{
			throw $this->exception($this->noPermission($error));
		}
		
		if (!$visitor->canViewAmsSeries($error))
		{
			throw $this->exception($this->noPermission($error));
		}
	
		if ($this->options()->xaAmsOverrideStyle)
		{
			$this->setViewOption('style_id', $this->options()->xaAmsOverrideStyle);
		}
	}
	
	public function actionIndex(ParameterBag $params)
	{
		if ($params->series_id)
		{
			return $this->redirect($this->buildLink('ams/series', $params));
		}
		
		return $this->redirect($this->buildLink('ams/series'));
	}
	
	/**
	 * @param \XenAddons\AMS\Entity\SeriesPart $seriesPart
	 *
	 * @return \XenAddons\AMS\Service\SeriesPart\Edit
	 */
	protected function setupSeriesPartEdit(\XenAddons\AMS\Entity\SeriesPart $seriesPart)
	{
		/** @var \XenAddons\AMS\Service\SeriesPart\Edit $editor */
		$editor = $this->service('XenAddons\AMS:SeriesPart\Edit', $seriesPart);

		$editor->setTitle($this->filter('title', 'str'));

		$seriesPart->edit_date = time();
		
		$basicFields = $this->filter([
			'display_order' => 'int'	
		]);
		$seriesPart->bulkSet($basicFields);
		
		return $editor;
	}	
	
	public function actionEdit(ParameterBag $params)
	{
		$seriesPart = $this->assertViewableSeriesPart($params->part_id);
		if (!$seriesPart->canEdit($error))
		{
			return $this->noPermission($error);
		}	

		if ($this->isPost())
		{
			$editor = $this->setupSeriesPartEdit($seriesPart);
			
			if (!$editor->validate($errors))
			{
				return $this->error($errors);
			}
			
			$seriesPart = $editor->save();

			return $this->redirect($this->buildLink('ams/series', $seriesPart));
		}
		else
		{
			$viewParams = [
				'seriesPart' => $seriesPart,
				'series' => $seriesPart->Series,
				'article' => $seriesPart->Article,
			];
			return $this->view('XenAddons\AMS:SeriesPart\Edit', 'xa_ams_series_part_edit', $viewParams);
		}	
	}
	
	public function actionRemove(ParameterBag $params)
	{
		$seriesPart = $this->assertViewableSeriesPart($params->part_id);
		if (!$seriesPart->canRemove($error))
		{
			return $this->noPermission($error);
		}

		if ($this->isPost())
		{
			if (!$seriesPart->canRemove($error))
			{
				return $this->noPermission($error);
			}
			
			/** @var \XenAddons\AMS\Service\SeriesPart\Deleter $deleter */
			$deleter = $this->service('XenAddons\AMS:SeriesPart\Deleter', $seriesPart);
			
			$deleter->delete();
			
			return $this->redirect($this->buildLink('ams/series', $seriesPart));
		}
		else
		{
			$viewParams = [
				'seriesPart' => $seriesPart,
				'series' => $seriesPart->Series,
				'article' => $seriesPart->Article,
			];
			return $this->view('XenAddons\AMS:Series\Remove', 'xa_ams_series_part_remove', $viewParams);
		}
	}

	public static function getActivityDetails(array $activities)
	{
		return \XF::phrase('xa_ams_managing_series');
	}
}