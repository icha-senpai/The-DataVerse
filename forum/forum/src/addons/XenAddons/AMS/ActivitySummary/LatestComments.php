<?php

namespace XenAddons\AMS\ActivitySummary;

use XF\ActivitySummary\AbstractSection;
use XF\ActivitySummary\Instance;
use XF\Mvc\Entity\Finder;

class LatestComments extends AbstractSection
{
	protected $defaultOptions = [
		'limit' => 5,
		'category_ids' => [0],
		'min_reaction_score' => null,
		'order' => 'comment_date',
		'direction' => 'DESC'
	];

	public function getDefaultTitle(\XF\Entity\ActivitySummaryDefinition $definition)
	{
		return \XF::phrase('xa_ams_latest_comments');
	}

	protected function getDefaultTemplateParams($context)
	{
		$params = parent::getDefaultTemplateParams($context);
		if ($context == 'options')
		{
			$categoryRepo = $this->app->repository('XenAddons\AMS:Category');
			$params['categoryTree'] = $categoryRepo->createCategoryTree($categoryRepo->findCategoryList()->fetch());
			$params['sortOrders'] = $this->getDefaultOrderOptions();
		}
		return $params;
	}

	protected function getBaseFinderForFetch(): Finder
	{
		return $this->finder('XenAddons\AMS:Comment')
			->with('Article', true)
			->with(['Article.Category', 'User'])
			->setDefaultOrder($this->options['order'], $this->options['direction']);
	}

	protected function findDataForFetch(Finder $finder): Finder
	{
		$options = $this->options;
		
		$limit = $options['limit'];
		$categoryIds = $options['category_ids'];
		
		$finder
			->where('comment_state', 'visible')
			->limit(max($limit * 10, 25));

		if ($categoryIds && !in_array(0, $categoryIds))
		{
			$finder->where('Article.category_id', $categoryIds);
		}
		
		$finder->where('comment_date', '>', $this->getActivityCutOff());

		if ($options['min_reaction_score'] !== null)
		{
			$finder->where('reaction_score', '>=', $options['min_reaction_score']);
		}

		return $finder;
	}

	protected function renderInternal(Instance $instance): string
	{
		$user = $instance->getUser();
		if (!method_exists($user, 'cacheAmsArticleCategoryPermissions'))  
		{
			return '';
		}

		/** @var \XF\Mvc\Entity\ArrayCollection|\XenAddons\AMS\Entity\Comment[] $comments */
		$comments = $this->fetchData();

		$categoryIds = $comments->pluck(
			function (\XenAddons\AMS\Entity\Comment $comment)
			{
				return $comment->Content ? [$comment->comment_id, $comment->Content->category_id] : null;
			},
			false
		);
		$user->cacheAmsArticleCategoryPermissions(array_unique($categoryIds));

		foreach ($comments AS $commentId => $comment)
		{
			if (!$comment->canView() || $comment->isIgnored())
			{
				unset($comments[$commentId]);
				continue;
			}

			if ($instance->hasSeen('ams_comment', $commentId))
			{
				unset($comments[$commentId]);
				continue;
			}
		}

		if (!$comments->count())
		{
			return '';
		}

		$comments = $comments->slice(0, $this->options['limit']);

		foreach ($comments AS $comment)
		{
			$instance->addSeen('ams_comment', $comment->comment_id);
		}

		$viewParams = [
			'comments' => $comments
		];
		return $this->renderSectionTemplate($instance, 'xa_ams_activity_summary_latest_comments', $viewParams);
	}

	protected function getDefaultOrderOptions()
	{
		return [
			'comment_date' => \XF::phrase('date'),
			'reaction_score' => \XF::phrase('reaction_score')
		];
	}

	public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
			'limit' => 'uint',
			'category_ids' => 'array-uint',
			'min_reaction_score' => '?int',
			'order' => 'str',
			'direction' => 'str'
		]);

		if (in_array(0, $options['category_ids']))
		{
			$options['category_ids'] = [0];
		}
		
		if ($options['limit'] < 1)
		{
			$options['limit'] = 1;
		}

		$orders = $this->getDefaultOrderOptions();
		if (!isset($orders[$options['order']]))
		{
			$options['order'] = 'comment_date';
		}

		$options['direction'] = strtoupper($options['direction']);
		if (!in_array($options['direction'], ['ASC', 'DESC']))
		{
			$options['direction'] = 'DESC';
		}

		return true;
	}
}