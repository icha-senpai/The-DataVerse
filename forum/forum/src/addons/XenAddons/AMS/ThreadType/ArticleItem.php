<?php

namespace XenAddons\AMS\ThreadType;

use XF\Entity\Thread;
use XF\Http\Request;
use XF\ThreadType\AbstractHandler;

class ArticleItem extends AbstractHandler
{
	public function getTypeIconClass(): string
	{
		return 'fa-newspaper';
	}

	public function getThreadViewAndTemplate(Thread $thread): array
	{
		return ['XenAddons\AMS:Thread\ViewTypeArticle', 'xa_ams_thread_view_type_article'];
	}

	public function adjustThreadViewParams(Thread $thread, array $viewParams, Request $request): array
	{
		$thread = $viewParams['thread'] ?? null;
		if ($thread)
		{
			/** @var \XenAddons\AMS\Entity\Article $article */
			$article = \XF::repository('XenAddons\AMS:Article')->findArticleForThread($thread)->fetchOne();
			if ($article && $article->canView())
			{
				$viewParams['amsArticle'] = $article;
			}
		}

		return $viewParams;
	}

	public function allowExternalCreation(): bool
	{
		return false;
	}
	
	public function canThreadTypeBeChanged(Thread $thread): bool
	{
		return false;
	}
	
	public function canConvertThreadToType(bool $isBulk): bool
	{
		return false;
	}
}