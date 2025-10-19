<?php

namespace XenAddons\AMS\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class GotoPage extends XFCP_GotoPage
{
	public function actionAmsComment(ParameterBag $params)
	{
		$params->offsetSet('comment_id', $this->filter('id', 'uint'));
		return $this->rerouteController('XenAddons\AMS:Comment', 'index', $params);
	}
}