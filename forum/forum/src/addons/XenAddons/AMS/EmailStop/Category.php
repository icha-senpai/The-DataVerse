<?php

namespace XenAddons\AMS\EmailStop;

use XF\EmailStop\AbstractHandler;

class Category extends AbstractHandler
{
	public function getStopOneText(\XF\Entity\User $user, $contentId)
	{
		/** @var \XenAddons\AMS\Entity\Category|null $category */
		$category = \XF::em()->find('XenAddons\AMS:Category', $contentId);
		$canView = \XF::asVisitor(
			$user,
			function() use ($category) { return $category && $category->canView(); }
		);

		if ($canView)
		{
			return \XF::phrase('stop_notification_emails_from_x', ['title' => $category->title]);
		}
		else
		{
			return null;
		}
	}

	public function getStopAllText(\XF\Entity\User $user)
	{
		return \XF::phrase('stop_notification_emails_from_all_categories');
	}

	public function stopOne(\XF\Entity\User $user, $contentId)
	{
		/** @var \XenAddons\AMS\Entity\Category $category */
		$category = \XF::em()->find('XenAddons\AMS:Category', $contentId);
		if ($category)
		{
			/** @var \XenAddons\AMS\Repository\CategoryWatch $categoryWatchRepo */
			$categoryWatchRepo = \XF::repository('XenAddons\AMS:CategoryWatch');
			$categoryWatchRepo->setWatchState($category, $user, 'update', ['email_subscribe' => false]);
		}
	}

	public function stopAll(\XF\Entity\User $user)
	{
		/** @var \XenAddons\AMS\Repository\ArticleWatch $articleWatchRepo */
		$articleWatchRepo = \XF::repository('XenAddons\AMS:ArticleWatch');
		$articleWatchRepo->setWatchStateForAll($user, 'update', ['email_subscribe' => 0]);

		/** @var \XenAddons\AMS\Repository\CategoryWatch $categoryWatchRepo */
		$categoryWatchRepo = \XF::repository('XenAddons\AMS:CategoryWatch');
		$categoryWatchRepo->setWatchStateForAll($user, 'update', ['send_email' => 0]);
	}
}