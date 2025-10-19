<?php

namespace XenAddons\AMS\Pub\Controller;

use XF\Pub\Controller\AbstractWhatsNewFindType;

class WhatsNewArticle extends AbstractWhatsNewFindType
{
	protected function getContentType()
	{
		return 'ams_article';
	}
}