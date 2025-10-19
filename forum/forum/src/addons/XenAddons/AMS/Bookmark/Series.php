<?php

namespace XenAddons\AMS\Bookmark;

use XF\Bookmark\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Series extends AbstractHandler
{
	public function getContentTitle(Entity $content)
	{
		return \XF::phrase('xa_ams_series_x', [
			'title' => $content->title
		]);
	}

	/**
	 * @return string
	 */
	public function getContentRoute(Entity $content)
	{
		return 'ams/series';
	}

	/**
	 * @return string
	 */
	public function getCustomIconTemplateName()
	{
		return 'public:xa_ams_series_bookmark_custom_icon';
	}

	public function getEntityWith()
	{
		return ['User'];
	}
}