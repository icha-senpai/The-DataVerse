<?php

namespace XenAddons\AMS\Service\Page;

use XF\Service\AbstractNotifier;
use XenAddons\AMS\Entity\ArticlePage;

class Notifier extends AbstractNotifier
{
	/**
	 * @var ArticlePage
	 */
	protected $page;

	public function __construct(\XF\App $app, ArticlePage $page)
	{
		parent::__construct($app);

		$this->page = $page;
	}

	public static function createForJob(array $extraData)
	{
		$page = \XF::app()->find('XenAddons\AMS:ArticlePage', $extraData['pageId']);
		if (!$page)
		{
			return null;
		}

		return \XF::service('XenAddons\AMS:Page\Notifier', $page);
	}

	protected function getExtraJobData()
	{
		return [
			'pageId' => $this->page->page_id
		];
	}

	protected function loadNotifiers()
	{
		$notifiers = [];
		
		$notifiers['articleWatch'] = $this->app->notifier('XenAddons\AMS:Page\ArticleWatch', $this->page);

		return $notifiers;
	}

	protected function loadExtraUserData(array $users)
	{
		return;
	}

	protected function canUserViewContent(\XF\Entity\User $user)
	{
		return \XF::asVisitor(
			$user,
			function() { return $this->page->canView(); }
		);
	}
}