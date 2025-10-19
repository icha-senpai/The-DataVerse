<?php

namespace XenAddons\AMS\BbCode;

class Ams
{
	public static function renderTagAms($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		if (!$tag['option'])
		{
			return $renderer->renderUnparsedTag($tag, $options);
		}

		$parts = explode(',', $tag['option']);
		foreach ($parts AS &$part)
		{
			$part = trim($part);
			$part = str_replace(' ', '', $part);
		}

		$type = $renderer->filterString(array_shift($parts),
			array_merge($options, [
				'stopSmilies' => true,
				'stopLineBreakConversion' => true
			])
		);
		$type = strtolower($type);
		$id = array_shift($parts);

		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();

		if (!$visitor->canViewAmsArticles()
			|| $renderer instanceof \XF\BbCode\Renderer\SimpleHtml
			|| $renderer instanceof \XF\BbCode\Renderer\EmailHtml
		)
		{
			return self::renderTagSimple($type, $id);
		}

		$viewParams = [
			'type' => $type,
			'id' => intval($id),
			'text' => isset($tag['children']) ? $tag['children'] : ''
		];

		if ($type == 'article')
		{
			if (isset($options['amsArticles'][$id]))
			{
				$article = $options['amsArticles'][$id];
			}
			else
			{
				$article = \XF::em()->find('XenAddons\AMS:ArticleItem', $id, [
					'Category.Permissions|' . $visitor->permission_combination_id
				]);
			}
			if (!$article || !$article->canView())
			{
				return self::renderTagSimple($type, $id);
			}
			else if ($visitor->isIgnoring($article->user_id))
			{
				return '';
			}
			$viewParams['article'] = $article;

			return $renderer->getTemplater()->renderTemplate('public:xa_ams_ams_bb_code_article', $viewParams);
		}
		
		if ($type == 'page')
		{
			if (isset($options['amsPages'][$id]))
			{
				$articlePage = $options['amsPages'][$id];
			}
			else
			{
				$articlePage = \XF::em()->find('XenAddons\AMS:ArticlePage', $id, [
					'Article',	
					'Article.Category.Permissions|' . $visitor->permission_combination_id
				]);
			}
			if (!$articlePage || !$articlePage->canView())
			{
				return self::renderTagSimple($type, $id);
			}
			else if ($visitor->isIgnoring($articlePage->user_id))
			{
				return '';
			}
			$viewParams['articlePage'] = $articlePage;
		
			return $renderer->getTemplater()->renderTemplate('public:xa_ams_ams_bb_code_article_page', $viewParams);
		}
		
		if ($type == 'series')
		{
			if (isset($options['amsSeries'][$id]))
			{
				$series = $options['amsSeries'][$id];
			}
			else
			{
				$series = \XF::em()->find('XenAddons\AMS:SeriesItem', $id, ['LastArticle']);
			}
			if (!$series || !$series->canView())
			{
				return self::renderTagSimple($type, $id);
			}
			else if ($visitor->isIgnoring($series->user_id))
			{
				return '';
			}
			$viewParams['series'] = $series;
		
			return $renderer->getTemplater()->renderTemplate('public:xa_ams_ams_bb_code_series', $viewParams);
		}		

		return self::renderTagSimple($type, $id);
	}

	protected static function renderTagSimple($type, $id)
	{
		$router = \XF::app()->router('public');

		switch ($type)
		{
			case 'article':

				$link = $router->buildLink('full:ams', ['article_id' => $id]);
				$phrase = \XF::phrase('xa_ams_view_article_item_x', ['id' => $id]);

				return '<a href="' . htmlspecialchars($link) .'">' . $phrase .'</a>';

			case 'page':
			
				$link = $router->buildLink('full:ams/page', ['page_id' => $id]);
				$phrase = \XF::phrase('xa_ams_view_article_page_x', ['id' => $id]);
			
				return '<a href="' . htmlspecialchars($link) .'">' . $phrase .'</a>';
					
			case 'series':

				$link = $router->buildLink('full:ams/series', ['series_id' => $id]);
				$phrase = \XF::phrase('xa_ams_view_series_x', ['id' => $id]);

				return '<a href="' . htmlspecialchars($link) .'">' . $phrase .'</a>';

			default:

				return '[AMS]';
		}
	}
}