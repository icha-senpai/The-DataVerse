<?php

namespace XenAddons\AMS\Notifier\Article;

use XF\Notifier\AbstractNotifier;

abstract class AbstractWatch extends AbstractNotifier
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

	protected $actionType;
	protected $isApplicable;

	abstract protected function getDefaultWatchNotifyData();
	abstract protected function getApplicableActionTypes();
	abstract protected function getWatchEmailTemplateName();

	public function __construct(\XF\App $app, \XenAddons\AMS\Entity\ArticleItem $article, $actionType)
	{
		parent::__construct($app);

		$this->article = $article;
		$this->actionType = $actionType;
		$this->isApplicable = $this->isApplicable();
	}

	protected function isApplicable()
	{
		if (!in_array($this->actionType, $this->getApplicableActionTypes()))
		{
			return false;
		}

		if (!$this->article->isVisible())
		{
			return false;
		}

		return true;
	}

	public function canNotify(\XF\Entity\User $user)
	{
		if (!$this->isApplicable)
		{
			return false;
		}

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
			$user, $article->user_id, $article->username, 'ams_article', $article->article_id, 'insert'
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
			'category' => $article->Category,
			'receiver' => $user
		];

		$template = $this->getWatchEmailTemplateName();

		$this->app()->mailer()->newMail()
			->setToUser($user)
			->setTemplate($template, $params)
			->queue();

		return true;
	}

	public function getDefaultNotifyData()
	{
		if (!$this->isApplicable)
		{
			return [];
		}

		return $this->getDefaultWatchNotifyData();
	}
}