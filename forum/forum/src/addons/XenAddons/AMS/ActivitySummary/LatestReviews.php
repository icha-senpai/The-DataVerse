<?php

namespace XenAddons\AMS\ActivitySummary;

use XF\ActivitySummary\AbstractSection;
use XF\ActivitySummary\Instance;
use XF\Mvc\Entity\Finder;

class LatestReviews extends AbstractSection
{
	protected $defaultOptions = [
		'limit' => 5,
		'category_ids' => [0],
		'min_reaction_score' => null,
		'order' => 'rating_date',
		'direction' => 'DESC'
	];

	public function getDefaultTitle(\XF\Entity\ActivitySummaryDefinition $definition)
	{
		return \XF::phrase('xa_ams_latest_reviews');
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
		return $this->finder('XenAddons\AMS:ArticleRating')
			->with('Article', true)
			->with(['Article.Category', 'User'])
			->setDefaultOrder($this->options['order'], $this->options['direction']);
	}

	protected function findDataForFetch(Finder $finder): Finder
	{
		$options = $this->options;

		$limit = $options['limit'];
		$categoryIds = $options['category_ids'];

		$finder->where([
			'Article.article_state' => 'visible',
			'rating_state' => 'visible',
			'is_review' => 1
		])->limit(max($limit * 2, 10));

		if ($categoryIds && !in_array(0, $categoryIds))
		{
			$finder->where('Article.category_id', $categoryIds);
		}

		$finder->where('rating_date', '>', $this->getActivityCutOff());

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

		/** @var \XF\Mvc\Entity\ArrayCollection|\XenAddons\AMS\Entity\ArticleRating[] $reviews */
		$reviews = $this->fetchData();

		$categoryIds = $reviews->pluck(
			function (\XenAddons\AMS\Entity\ArticleRating $review)
			{
				return $review->Article ? [$review->rating_id, $review->Article->category_id] : null;
			},
			false
		);
		$user->cacheAmsArticleCategoryPermissions(array_unique($categoryIds));

		foreach ($reviews AS $reviewId => $review)
		{
			if (!$review->canView() || $review->isIgnored())
			{
				unset($reviews[$reviewId]);
				continue;
			}

			if ($instance->hasSeen('ams_rating', $reviewId))
			{
				unset($reviews[$reviewId]);
				continue;
			}
		}

		if (!$reviews->count())
		{
			return '';
		}

		$reviews = $reviews->slice(0, $this->options['limit']);

		foreach ($reviews AS $review)
		{
			$instance->addSeen('ams_rating', $review->rating_id);
		}

		$viewParams = [
			'reviews' => $reviews
		];
		return $this->renderSectionTemplate($instance, 'xa_ams_activity_summary_latest_reviews', $viewParams);
	}
	
	protected function getDefaultOrderOptions()
	{
		return [
			'rating_date' => \XF::phrase('date'),
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
			$options['order'] = 'rating_date';
		}
		
		$options['direction'] = strtoupper($options['direction']);
		if (!in_array($options['direction'], ['ASC', 'DESC']))
		{
			$options['direction'] = 'DESC';
		}

		return true;
	}
}