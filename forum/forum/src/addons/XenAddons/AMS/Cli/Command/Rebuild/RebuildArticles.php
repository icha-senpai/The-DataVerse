<?php

namespace XenAddons\AMS\Cli\Command\Rebuild;

use XF\Cli\Command\Rebuild\AbstractRebuildCommand;

class RebuildArticles extends AbstractRebuildCommand
{
	protected function getRebuildName()
	{
		return 'xa-ams-articles';
	}

	protected function getRebuildDescription()
	{
		return 'Rebuilds ams article counters.';
	}

	protected function getRebuildClass()
	{
		return 'XenAddons\AMS:ArticleItem';
	}
}