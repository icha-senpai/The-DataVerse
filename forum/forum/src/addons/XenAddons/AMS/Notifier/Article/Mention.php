<?php

namespace XenAddons\AMS\Notifier\Article;

use XF\Notifier\AbstractNotifier;
use XenAddons\AMS\Entity\ArticleItem;

class Mention extends AbstractNotifier
{
	/**
	 * @var ArticleItem
	 */
	protected $articleItem;

	public function __construct(\XF\App $app, ArticleItem $articleItem)
	{
		parent::__construct($app);

		$this->articleItem = $articleItem;
	}

	public function canNotify(\XF\Entity\User $user)
	{
		return ($this->articleItem->isVisible() && $user->user_id != $this->articleItem->user_id);
	}

	public function sendAlert(\XF\Entity\User $user)
	{
		$articleItem = $this->articleItem;

		return $this->basicAlert(
			$user, $articleItem->user_id, $articleItem->username, 'ams_article', $articleItem->article_id, 'mention'
		);
	}
}