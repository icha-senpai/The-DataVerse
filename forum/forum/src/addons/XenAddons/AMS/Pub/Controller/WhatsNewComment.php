<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Pub\Controller\AbstractWhatsNewFindType;

class WhatsNewComment extends AbstractWhatsNewFindType
{
	protected function getContentType()
	{
		return 'ams_comment';
	}
}