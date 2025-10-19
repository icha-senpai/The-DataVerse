<?php

namespace XenAddons\AMS\FindNew;

use XF\Entity\FindNew;
use XF\FindNew\AbstractHandler;
use XF\Mvc\Entity\ArrayCollection;

class ArticleComment extends AbstractHandler
{
	public function getRoute()
	{
		return 'whats-new/ams-comments';
	}

	public function getPageReply(
		\XF\Mvc\Controller $controller, FindNew $findNew, array $results, $page, $perPage
	)
	{
		$viewParams = [
			'findNew' => $findNew,

			'page' => $page,
			'perPage' => $perPage,

			'items' => $results
		];
		
		return $controller->view('XenAddons\AMS:WhatsNew\ArticleComments', 'xa_ams_whats_new_article_comments', $viewParams);
	}

	public function getFiltersFromInput(\XF\Http\Request $request)
	{
		$filters = [];

		$visitor = \XF::visitor();

		$unread = $request->filter('unread', 'bool');
		if ($unread && $visitor->user_id)
		{
			$filters['unread'] = true;
		}

		$own = $request->filter('own', 'bool');
		if ($own && $visitor->user_id)
		{
			$filters['own'] = true;
		}

		return $filters;
	}

	public function getDefaultFilters()
	{
		$visitor = \XF::visitor();

		if ($visitor->user_id)
		{
			return ['unread' => true];
		}
		else
		{
			return [];
		}
	}

	public function getResultIds(array $filters, $maxResults)
	{
		$visitor = \XF::visitor();

		/** @var \XenAddons\AMS\Finder\ArticleItem $articleFinder */
		$articleFinder = \XF::finder('XenAddons\AMS:ArticleItem')
			->with(['Category'])
			->with('Category.Permissions|' . $visitor->permission_combination_id)
			->where('last_comment_date', '>', 0)
			->where('article_state', '<>', 'deleted')
			->where('Category.allow_comments', 1)
			->orderByDate('last_comment_date');

		$this->applyFilters($articleFinder, $filters);
		
		$articleItems = $articleFinder->fetch($maxResults);
		$articleItems = $this->filterResults($articleItems)->toArray();

		$merged = $this->mergeAndSortArticleItems($articleItems);
		
		return array_keys($merged);
	}

	protected function mergeAndSortArticleItems(array $articleItems)
	{
		$merged = [];
		foreach ($articleItems AS $articleId => $articleItem)
		{
			$merged[$articleId] = $articleItem;
		}

		uasort($merged, function($itemA, $itemB)
		{
			return ($itemB['last_comment_date'] - $itemA['last_comment_date']);
		});
		return $merged;
	}

	public function getPageResultsEntities(array $ids)
	{
		$articleIds = [];

		foreach ($ids AS $id)
		{
			$articleIds[] = $id;
		}

		$articleIds = array_map('intval', $articleIds);

		$visitor = \XF::visitor();

		$articleFinder = \XF::finder('XenAddons\AMS:ArticleItem')
			->with(['Category', 'CoverImage'])
			->with('Category.Permissions|' . $visitor->permission_combination_id)
			->with('CommentRead|' . $visitor->user_id)
			->with('LastComment', true)
			->with('LastCommenter')
			->where('article_id', $articleIds)
			->orderByDate('last_comment_date');

		$articleItems = $articleFinder->fetch()->toArray();

		$merged = $this->mergeAndSortArticleItems($articleItems);
		$merged = new ArrayCollection($merged);
		
		return $merged;
	}

	protected function filterResults(\XF\Mvc\Entity\AbstractCollection $results)
	{
		$visitor = \XF::visitor();

		return $results->filter(function(\XF\Mvc\Entity\Entity $entity) use($visitor)
		{
			/** @var \XenAddons\AMS\Entity\ArticleItem $entity */
			return ($entity->canView() && !$visitor->isIgnoring($entity->user_id));
		});
	}

	/**
	 * @param \XenAddons\AMS\Finder\ArticleItem $finder
	 *
	 * @param array $filters
	 */
	protected function applyFilters(\XF\Mvc\Entity\Finder $finder, array $filters)
	{
		$visitor = \XF::visitor();

		if (!empty($filters['unread']))
		{
			$finder->withUnreadCommentsOnly($visitor->user_id);
		}
		else if (sizeof($filters) != 1)
		{
			$finder->where('last_comment_date', '>', \XF::$time - (86400 * \XF::options()->readMarkingDataLifetime));
		}
	}

	public function getResultsPerPage()
	{
		return \XF::options()->xaAmsArticlesPerPage;
	}

	public function isAvailable()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		return $visitor->canViewAmsArticles() 
			&& $visitor->canViewAmsComments();
	}
}