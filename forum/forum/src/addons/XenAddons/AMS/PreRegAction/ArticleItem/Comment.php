<?php

namespace XenAddons\AMS\PreRegAction\ArticleItem;

use XenAddons\AMS\PreRegAction\AbstractCommentHandler;

class Comment extends AbstractCommentHandler
{
	public function getContainerContentType(): string
	{
		return 'ams_article';
	}
}