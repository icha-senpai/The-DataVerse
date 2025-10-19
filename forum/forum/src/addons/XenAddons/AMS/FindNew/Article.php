<?php

namespace XenAddons\AMS\FindNew;

use XF\Entity\FindNew;
use XF\FindNew\AbstractHandler;

class Article extends AbstractHandler
{
	public function getRoute()
	{
		return 'whats-new/ams-articles';
	}

	public function getPageReply(
		\XF\Mvc\Controller $controller, FindNew $findNew, array $results, $page, $perPage
	)
	{
		$canInlineMod = false;

		/** @var \XenAddons\AMS\Entity\ArticleItem $articleItem */
		foreach ($results AS $articleItem)
		{
			if ($articleItem->canUseInlineModeration())
			{
				$canInlineMod = true;
				break;
			}
		}

		$viewParams = [
			'findNew' => $findNew,

			'page' => $page,
			'perPage' => $perPage,

			'articles' => $results,
			'canInlineMod' => $canInlineMod
		];
		return $controller->view('XenAddons\AMS:WhatsNew\Articles', 'xa_ams_whats_new_articles', $viewParams);
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

		$watched = $request->filter('watched', 'bool');
		if ($watched && $visitor->user_id)
		{
			$filters['watched'] = true;
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
			->where('article_state', '<>', 'deleted')
			->orderByDate();

		$this->applyFilters($articleFinder, $filters);

		$articles = $articleFinder->fetch($maxResults);
		$articles = $this->filterResults($articles);

		return $articles->keys();
	}

	public function getPageResultsEntities(array $ids)
	{
		$visitor = \XF::visitor();

		$ids = array_map('intval', $ids);

		$articleFinder = \XF::finder('XenAddons\AMS:ArticleItem')
			->where('article_id', $ids)
			->with('fullCategory')
			->with('Category.Permissions|' . $visitor->permission_combination_id)
			->orderByDate();

		return $articleFinder->fetch();
	}

	protected function filterResults(\XF\Mvc\Entity\AbstractCollection $results) 
	{
		$visitor = \XF::visitor();

		return $results->filter(function(\XenAddons\AMS\Entity\ArticleItem $articleItem) use($visitor)
		{
			return ($articleItem->canView() && !$visitor->isIgnoring($articleItem->user_id));
		});
	}

	protected function applyFilters(\XenAddons\AMS\Finder\ArticleItem $articleItem, array $filters)
	{
		$visitor = \XF::visitor();

		if (!empty($filters['unread']))
		{
			$articleItem->unreadOnly($visitor->user_id);
		}
		else if (sizeof($filters) != 1)
		{
			$articleItem->where('last_update', '>', \XF::$time - (86400 * \XF::options()->readMarkingDataLifetime));
		}

		if (!empty($filters['watched']))
		{
			$articleItem->watchedOnly($visitor->user_id);
		}

		if (!empty($filters['own']))
		{
			$articleItem->where('user_id', $visitor->user_id);
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
		return $visitor->canViewAmsArticles();
	}
}