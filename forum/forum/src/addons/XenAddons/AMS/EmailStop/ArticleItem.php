<?php

namespace XenAddons\AMS\EmailStop;

use XF\EmailStop\AbstractHandler;

class ArticleItem extends AbstractHandler
{
	public function getStopOneText(\XF\Entity\User $user, $contentId)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem|null $article */
		$article = \XF::em()->find('XenAddons\AMS:ArticleItem', $contentId);
		$canView = \XF::asVisitor(
			$user,
			function() use ($article) { return $article && $article->canView(); }
		);

		if ($canView)
		{
			return \XF::phrase('stop_notification_emails_from_x', ['title' => $article->title]);
		}
		else
		{
			return null;
		}
	}

	public function getStopAllText(\XF\Entity\User $user)
	{
		return \XF::phrase('xa_ams_stop_notification_emails_from_all_articles');
	}

	public function stopOne(\XF\Entity\User $user, $contentId)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $article */
		$article = \XF::em()->find('XenAddons\AMS:ArticleItem', $contentId);
		if ($article)
		{
			/** @var \XenAddons\AMS\Repository\ArticleWatch $articleWatchRepo */
			$articleWatchRepo = \XF::repository('XenAddons\AMS:ArticleWatch');
			$articleWatchRepo->setWatchState($article, $user, 'update', ['email_subscribe' => false]);
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