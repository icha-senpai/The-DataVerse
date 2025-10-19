<?php

namespace XenAddons\AMS\Cli\Command\Rebuild;

use XF\Cli\Command\Rebuild\AbstractRebuildCommand;

class RebuildUserCounts extends AbstractRebuildCommand
{
	protected function getRebuildName()
	{
		return 'xa-ams-user-counts';
	}

	protected function getRebuildDescription()
	{
		return 'Rebuilds ams related user counters.';
	}

	protected function getRebuildClass()
	{
		return 'XenAddons\AMS:UserArticleCount';
	}
}