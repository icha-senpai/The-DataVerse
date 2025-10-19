<?php

namespace XenAddons\AMS\Widget;

use XF\Widget\AbstractPollWidget;

class ArticlePagePoll extends AbstractPollWidget
{
	public function getPollFromRoutePath($routePath, &$error = null)
	{
		$articlePage = $this->repository('XenAddons\AMS:ArticlePage')->getArticlePageFromUrl($routePath, 'public', $error);
		if (!$articlePage)
		{
			return false;
		}

		if (!$articlePage->Poll)
		{
			$error = \XF::phrase('xa_ams_specified_article_page_does_not_have_poll_attached_to_it');
			return false;
		}

		return $articlePage->Poll;
	}

	public function getDefaultTitle()
	{
		/** @var \XenAddons\AMS\Entity\ArticlePage $content */
		$content = $this->getContent();
		if ($content && $content->canView() && $content->Poll)
		{
			return $content->Poll->question;
		}
		else
		{
			return parent::getDefaultTitle();
		}
	}

	public function render()
	{
		/** @var \XenAddons\AMS\Entity\ArticlePage $content */
		$content = $this->getContent();
		if ($content && $content->canView() && $content->Poll)
		{
			$viewParams = [
				'content' => $content,
				'poll' => $content->Poll
			];
			return $this->renderer('xa_ams_widget_article_page_poll', $viewParams);
		}

		return '';
	}

	public function getEntityWith()
	{
		return [
			'Poll',
			'Article',
			'Article.Category',
			'Article.Category.Permissions|' . \XF::visitor()->permission_combination_id
		];
	}
}