<?php

namespace XenAddons\AMS\Notifier\Page;

use XF\Notifier\AbstractNotifier;

class ArticleWatch extends AbstractNotifier
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticlePage
	 */
	protected $page;

	public function __construct(\XF\App $app, \XenAddons\AMS\Entity\ArticlePage $page)
	{
		parent::__construct($app);

		$this->page = $page;
	}

	public function canNotify(\XF\Entity\User $user)
	{
		$page = $this->page;

		if ($user->user_id == $page->Article->user_id || $user->isIgnoring($page->Article->user_id))
		{
			return false;
		}

		return true;
	}

	public function sendAlert(\XF\Entity\User $user)
	{
		$page = $this->page;

		return $this->basicAlert(
			$user, $page->Article->user_id, $page->Article->username, 'ams_page', $page->page_id, 'insert'
		);
	}

	public function sendEmail(\XF\Entity\User $user)
	{
		if (!$user->email || $user->user_state != 'valid')
		{
			return false;
		}

		$page = $this->page;

		$params = [
			'page' => $page,
			'article' => $page->Article,
			'receiver' => $user
		];

		$this->app()->mailer()->newMail()
			->setToUser($user)
			->setTemplate('xa_ams_watched_article_page', $params)
			->queue();

		return true;
	}

	public function getDefaultNotifyData()
	{
		$page = $this->page;
		$article = $page->Article;

		if (!$article)
		{
			return [];
		}

		$finder = $this->app()->finder('XenAddons\AMS:ArticleWatch');

		$finder->where('article_id', $page->article_id)
			->where('User.user_state', '=', 'valid')
			->where('User.is_banned', '=', 0);

		$activeLimit = $this->app()->options()->watchAlertActiveOnly;
		if (!empty($activeLimit['enabled']))
		{
			$finder->where('User.last_activity', '>=', \XF::$time - 86400 * $activeLimit['days']);
		}

		$notifyData = [];
		foreach ($finder->fetchColumns(['user_id', 'email_subscribe']) AS $watch)
		{
			$notifyData[$watch['user_id']] = [
				'alert' => true,
				'email' => (bool)$watch['email_subscribe']
			];
		}

		return $notifyData;
	}
}