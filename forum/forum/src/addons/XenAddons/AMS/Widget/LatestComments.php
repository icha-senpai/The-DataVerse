<?php

namespace XenAddons\AMS\Widget;

use XF\Widget\AbstractWidget;

class LatestComments extends AbstractWidget
{
	protected $defaultOptions = [
		'limit' => 5,
		'article_category_ids' => []
	];

	protected function getDefaultTemplateParams($context)
	{
		$params = parent::getDefaultTemplateParams($context);
		if ($context == 'options')
		{
			$categoryRepo = $this->app->repository('XenAddons\AMS:Category');
			$params['categoryTree'] = $categoryRepo->createCategoryTree($categoryRepo->findCategoryList()->fetch());
		}
		return $params;
	}
	
	public function render()
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		if (!method_exists($visitor, 'canViewAmsArticles') || !$visitor->canViewAmsArticles())
		{
			return '';
		}
		
		$options = $this->options;
		$limit = $options['limit'];
		$categoryIds = $options['article_category_ids'];
		
		$hasCategoryIds = ($categoryIds && !in_array(0, $categoryIds));
		$hasCategoryContext = (
			isset($this->contextParams['category'])
			&& $this->contextParams['category'] instanceof \XenAddons\AMS\Entity\Category
		);
		$useContext = false;
		$category = null;
		
		if (!$hasCategoryIds && $hasCategoryContext)
		{
			/** @var \XenAddons\AMS\Entity\Category $category */
			$category = $this->contextParams['category'];
			$viewableDescendents = $category->getViewableDescendants();
			$sourceCategoryIds = array_keys($viewableDescendents);
			$sourceCategoryIds[] = $category->category_id;
		
			$useContext = true;
		}
		else if ($hasCategoryIds)
		{
			$sourceCategoryIds = $categoryIds;
		}
		else
		{
			$sourceCategoryIds = null;
		}

		/** @var \XenAddons\AMS\Repository\Comment $commentRepo */
		$commentRepo = $this->repository('XenAddons\AMS:Comment');
		$finder = $commentRepo->findLatestCommentsForWidget($sourceCategoryIds);

		if (!$useContext)
		{
			// with the context, we already fetched the article category and permissions
			$finder->with('Article.Category.Permissions|' . $visitor->permission_combination_id);
		}		
		
		$comments = $finder->fetch($limit * 10)->filterViewable();
		$comments = $comments->slice(0, $limit);

		$router = $this->app->router('public');
		$link = $router->buildLink('whats-new/ams-comments', null, ['skip' => 1]);

		$viewParams = [
			'title' => $this->getTitle(),
			'comments' => $comments,
			'link' => $link
		];
		return $this->renderer('xa_ams_widget_latest_comments', $viewParams);
	}

	public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
			'limit' => 'uint',
			'article_category_ids' => 'array-uint'
		]);
		if ($options['limit'] < 1)
		{
			$options['limit'] = 1;
		}
		if (in_array(0, $options['article_category_ids']))
		{
			$options['article_category_ids'] = [0];
		}

		return true;
	}
}