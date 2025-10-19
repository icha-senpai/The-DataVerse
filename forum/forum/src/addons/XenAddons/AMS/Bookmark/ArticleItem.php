<?php

namespace XenAddons\AMS\Bookmark;

use XF\Bookmark\AbstractHandler;
use XF\Mvc\Entity\Entity;

class ArticleItem extends AbstractHandler
{
	public function getContentTitle(Entity $content)
	{
		if ($content->Category->content_term)
		{
			return \XF::phrase('xa_ams_term_x_article_y', [
				'term' => $content->Category->content_term,
				'title' => $content->title
			]);
		}
		else
		{
			return \XF::phrase('xa_ams_article_x', [
				'title' => $content->title
			]);
		}
	}

	/**
	 * @return string
	 */
	public function getContentRoute(Entity $content)
	{
		return 'ams';
	}

	/**
	 * @return string
	 */
	public function getCustomIconTemplateName()
	{
		return 'public:xa_ams_article_bookmark_custom_icon';
	}

	public function getEntityWith()
	{
		$visitor = \XF::visitor();

		return ['CoverImage', 'Category', 'Category.Permissions|' . $visitor->permission_combination_id, 'User'];
	}
}