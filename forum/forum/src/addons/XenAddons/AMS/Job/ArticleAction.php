<?php

namespace XenAddons\AMS\Job;

use XF\Job\AbstractJob;

class ArticleAction extends AbstractJob
{
	protected $defaultData = [
		'start' => 0,
		'count' => 0,
		'total' => null,
		'criteria' => null,
		'articleIds' => null,
		'actions' => []
	];

	public function run($maxRunTime)
	{
		if (is_array($this->data['criteria']) && is_array($this->data['articleIds']))
		{
			throw new \LogicException("Cannot have both criteria and articleIds values; one must be null");
		}

		$startTime = microtime(true);
		$em = $this->app->em();

		$ids = $this->prepareArticleIds();
		if (!$ids)
		{
			return $this->complete();
		}

		$db = $this->app->db();
		$db->beginTransaction();

		$limitTime = ($maxRunTime > 0);
		foreach ($ids AS $key => $id)
		{
			$this->data['count']++;
			$this->data['start'] = $id;
			unset($ids[$key]);

			/** @var \XenAddons\AMS\Entity\ArticleItem $article */
			$article = $em->find('XenAddons\AMS:ArticleItem', $id);
			if ($article)
			{
				if ($this->getActionValue('delete'))
				{
					$article->delete(false, false);
					continue; // no further action required
				}

				$this->applyInternalArticleChange($article);
				$article->save(false, false);

				$this->applyExternalArticleChange($article);
			}

			if ($limitTime && microtime(true) - $startTime > $maxRunTime)
			{
				break;
			}
		}

		if (is_array($this->data['articleIds']))
		{
			$this->data['articleIds'] = $ids;
		}

		$db->commit();

		return $this->resume();
	}

	protected function getActionValue($action)
	{
		$value = null;
		if (!empty($this->data['actions'][$action]))
		{
			$value = $this->data['actions'][$action];
		}
		return $value;
	}

	protected function prepareArticleIds()
	{
		if (is_array($this->data['criteria']))
		{
			$searcher = $this->app->searcher('XenAddons\AMS:Article', $this->data['criteria']);
			$results = $searcher->getFinder()
				->where('article_id', '>', $this->data['start'])
				->order('article_id')
				->limit(1000)
				->fetchColumns('article_id');
			$ids =array_column($results, 'article_id'); 
		}
		else if (is_array($this->data['articleIds']))
		{
			$ids = $this->data['articleIds'];
		}
		else
		{
			$ids = [];
		}
		sort($ids, SORT_NUMERIC);
		return $ids;
	}

	protected function applyInternalArticleChange(\XenAddons\AMS\Entity\ArticleItem $article)
	{
		if ($categoryId = $this->getActionValue('category_id'))
		{
			$article->category_id = $categoryId;
		}

		if ($this->getActionValue('apply_article_prefix'))
		{
			$article->prefix_id = intval($this->getActionValue('prefix_id'));
		}
		
		if ($this->getActionValue('lock'))
		{
			$article->comments_open = false;
		}
		if ($this->getActionValue('unlock'))
		{
			$article->comments_open = true;
		}
		
		if ($this->getActionValue('lockRatings'))
		{
			$article->ratings_open = false;
		}
		if ($this->getActionValue('unlockRatings'))
		{
			$article->ratings_open = true;
		}

		if ($this->getActionValue('approve'))
		{
			$article->article_state = 'visible';
		}
		if ($this->getActionValue('unapprove'))
		{
			$article->article_state = 'moderated';
		}

		if ($this->getActionValue('soft_delete'))
		{
			$article->article_state = 'deleted';
		}
	}

	protected function applyExternalArticleChange(\XenAddons\AMS\Entity\ArticleItem $article)
	{
	}

	public function getStatusMessage()
	{
		$actionPhrase = \XF::phrase('updating');
		$typePhrase = \XF::phrase('xa_ams_articles');

		if ($this->data['total'] !== null)
		{
			return sprintf('%s... %s (%d/%d)', $actionPhrase, $typePhrase, $this->data['count'], $this->data['total']);
		}
		else
		{
			return sprintf('%s... %s (%d)', $actionPhrase, $typePhrase, $this->data['start']);
		}
	}

	public function canCancel()
	{
		return true;
	}

	public function canTriggerByChoice()
	{
		return false;
	}
}