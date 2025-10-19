<?php

namespace XenAddons\AMS\ActivitySummary;

use XF\ActivitySummary\AbstractSection;
use XF\ActivitySummary\Instance;
use XF\Mvc\Entity\Finder;

class LatestArticles extends AbstractSection
{
	protected $defaultOptions = [
		'limit' => 5,
		'category_ids' => [0],
		'condition' => 'last_update',
		'min_comments' => null,
		'min_reaction_score' => null,
		'has_cover_image' => false,
		'order' => 'last_update',
		'direction' => 'DESC',
		'display_header' => false,
		'display_attribution' => false,
		'display_description' => false,
		'display_footer' => false,
		'display_footer_opposite' => false,
		'snippet_type' => 'plain_text',
	];

	public function getDefaultTitle(\XF\Entity\ActivitySummaryDefinition $definition)
	{
		return \XF::phrase('xa_ams_latest_articles');
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
		return $this->finder('XenAddons\AMS:ArticleItem')
			->with(['Category', 'User', 'User.PermissionCombination'])
			->setDefaultOrder($this->options['order'], $this->options['direction']);
	}

	protected function findDataForFetch(Finder $finder): Finder
	{
		$options = $this->options;

		$limit = $options['limit'];
		$categoryIds = $options['category_ids'];
		$condition = $options['condition'];

		$finder
			->where('article_state', 'visible')
			->limit(max($limit * 5, 25));

		if ($categoryIds && !in_array(0, $categoryIds))
		{
			$finder->where('category_id', $categoryIds);
		}

		$finder->where($condition, '>', $this->getActivityCutOff());

		if ($options['min_reaction_score'] !== null)
		{
			$finder->where('reaction_score', '>=', $minReactionScore);
		}
		
		if ($options['min_comments'] !== null)
		{
			$finder->where('comment_count', '>=', $options['min_comments']);
		}
		
		if ($options['has_cover_image'])
		{
			$finder->whereOr(
				['cover_image_id', '!=', 0],
				['Category.content_image', '!=', '']
			);
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
		
		$options = $this->options;

		/** @var \XF\Mvc\Entity\ArrayCollection|\XenAddons\AMS\Entity\ArticleItem[] $articles */
		$articles = $this->fetchData();

		$categoryIds = $articles->pluckNamed('category_id');

		$user->cacheAmsArticleCategoryPermissions(array_unique($categoryIds));

		foreach ($articles AS $articleId => $article)
		{
			if (!$article->canView() || $article->isIgnored())
			{
				unset($articles[$articleId]);
				continue;
			}

			if ($instance->hasSeen('ams_article', $articleId))
			{
				unset($articles[$articleId]);
				continue;
			}
		}

		if (!$articles->count())
		{
			return '';
		}

		$articles = $articles->slice(0, $this->options['limit']);

		foreach ($articles AS $article)
		{
			$instance->addSeen('ams_article', $article->article_id);
		}

		$viewParams = [
			'articles' => $articles,
			
			'displayHeader' => $options['display_header'],
			'displayAttribution' => $options['display_attribution'],
			'displayDescription' => $options['display_description'],
			'displayFooter' => $options['display_footer'],
			'displayFooterOpposite' => $options['display_footer_opposite'],
			'snippetType' => $options['snippet_type'],
		];
		return $this->renderSectionTemplate($instance, 'xa_ams_activity_summary_latest_articles', $viewParams);
	}

	protected function getDefaultOrderOptions()
	{
		return [
			'publish_date' => \XF::phrase('xa_ams_publish_date'),
			'last_update' => \XF::phrase('xa_ams_last_update'),
			'comment_count' => \XF::phrase('comments'),
			'rating_weighted' => \XF::phrase('rating'),
			'reaction_score' => \XF::phrase('reaction_score'),
			'view_count' => \XF::phrase('views')
		];
	}

	public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
			'limit' => 'uint',
			'category_ids' => 'array-uint',
			'condition' => 'str',
			'min_comments' => '?int',
			'min_reaction_score' => '?int',
			'has_cover_image' => 'bool',
			'order' => 'str',
			'direction' => 'str',
			'display_header' => 'bool',
			'display_attribution' => 'bool',
			'display_description' => 'bool',
			'display_footer' => 'bool',
			'display_footer_opposite' => 'bool',
			'snippet_type' => 'str',
		]);

		if (in_array(0, $options['category_ids']))
		{
			$options['category_ids'] = [0];
		}

		if ($options['limit'] < 1)
		{
			$options['limit'] = 1;
		}

		if (!in_array($options['condition'], ['publish_date', 'last_update']))
		{
			$options['condition'] = 'last_update';
		}
		
		if (!in_array($options['snippet_type'], ['rich_text', 'plain_text']))
		{
			$options['condition'] = 'plain_text';
		}

		$orders = $this->getDefaultOrderOptions();
		if (!isset($orders[$options['order']]))
		{
			$options['order'] = 'publish_date';
		}

		$options['direction'] = strtoupper($options['direction']);
		if (!in_array($options['direction'], ['ASC', 'DESC']))
		{
			$options['direction'] = 'DESC';
		}

		return true;
	}
}
