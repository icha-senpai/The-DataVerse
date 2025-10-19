<?php

namespace XenAddons\AMS\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
	public function actionIndex(ParameterBag $params)
	{
		$reply = parent::actionIndex($params);

		if ($reply instanceof \XF\Mvc\Reply\View && $reply->getParam('posts'))
		{
			$amsArticleRepo = $this->repository('XenAddons\AMS:Article');
			$amsArticleRepo->addArticleEmbedsToContent($reply->getParam('posts'));
		}

		return $reply;
	}
}