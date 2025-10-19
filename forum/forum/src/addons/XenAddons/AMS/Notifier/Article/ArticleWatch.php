<?php

namespace XenAddons\AMS\Notifier\Article;

use XF\Notifier\AbstractNotifier;

class ArticleWatch extends AbstractNotifier
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

	public function __construct(\XF\App $app, \XenAddons\AMS\Entity\ArticleItem $article)
	{
		parent::__construct($app);

		$this->article = $article;
	}

	public function canNotify(\XF\Entity\User $user)
	{
		$article = $this->article;

		if ($user->user_id == $article->user_id || $user->isIgnoring($article->user_id))
		{
			return false;
		}

		return true;
	}

	public function sendAlert(\XF\Entity\User $user)
	{
		$article = $this->article;

		return $this->basicAlert(
			$user, $article->user_id, $article->username, 'ams_article', $article->article_id, 'update'
		);
	}

	public function sendEmail(\XF\Entity\User $user)
	{
		if (!$user->email || $user->user_state != 'valid')
		{
			return false;
		}

		$article = $this->article;

		$params = [
			'article' => $article,
			'receiver' => $user
		];

		$this->app()->mailer()->newMail()
			->setToUser($user)
			->setTemplate('xa_ams_watched_article_update', $params)
			->queue();

		return true;
	}

	public function getDefaultNotifyData()
	{
		$article = $this->article;

		if (!$article)
		{
			return [];
		}

		$finder = $this->app()->finder('XenAddons\AMS:ArticleWatch');

		$finder->where('article_id', $article->article_id)
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