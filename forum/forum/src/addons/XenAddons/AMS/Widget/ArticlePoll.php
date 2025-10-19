<?php

namespace XenAddons\AMS\Widget;

use XF\Widget\AbstractPollWidget;

class ArticlePoll extends AbstractPollWidget
{
	public function getPollFromRoutePath($routePath, &$error = null)
	{
		$article = $this->repository('XenAddons\AMS:Article')->getArticleFromUrl($routePath, 'public', $error);
		if (!$article)
		{
			return false;
		}

		if (!$article->Poll)
		{
			$error = \XF::phrase('xa_ams_specified_article_does_not_have_poll_attached_to_it');
			return false;
		}

		return $article->Poll;
	}

	public function getDefaultTitle()
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $content */
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
		/** @var \XenAddons\AMS\Entity\ArticleItem $content */
		$content = $this->getContent();
		if ($content && $content->canView() && $content->Poll)
		{
			$viewParams = [
				'content' => $content,
				'poll' => $content->Poll
			];
			return $this->renderer('xa_ams_widget_article_poll', $viewParams);
		}

		return '';
	}

	public function getEntityWith()
	{
		return [
			'Poll',
			'Category',
			'Category.Permissions|' . \XF::visitor()->permission_combination_id
		];
	}
}