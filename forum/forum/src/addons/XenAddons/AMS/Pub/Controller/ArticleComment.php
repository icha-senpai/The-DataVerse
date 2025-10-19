<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Mvc\ParameterBag;

class ArticleComment extends AbstractComment
{
	protected function assertViewableAndCommentableContent(ParameterBag $params)
	{
		$articleItem = $this->assertViewableArticle($params->article_id);
		if ($articleItem->canViewComments() && ($articleItem->canAddComment()|| $articleItem->canAddCommentPreReg()))
		{
			return $articleItem;
		}
		else
		{
			throw $this->exception($this->noPermission());
		}
	}
	
	protected function assertViewableContent(ParameterBag $params)
	{
		$articleItem = $this->assertViewableArticle($params->article_id);
		if ($articleItem->canViewComments())
		{
			return $articleItem;
		}
		else
		{
			throw $this->exception($this->noPermission());
		}
	}

	protected function getLinkPrefix()
	{
		return 'ams/article-comments';
	}
}