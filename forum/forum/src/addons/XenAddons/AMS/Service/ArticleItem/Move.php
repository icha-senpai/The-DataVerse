<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class Move extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

	protected $alert = false;
	protected $alertReason = '';

	protected $notifyWatchers = false;

	protected $prefixId = null;

	protected $extraSetup = [];

	public function __construct(\XF\App $app, ArticleItem $article)
	{
		parent::__construct($app);
		$this->article = $article;
	}

	public function getArticle()
	{
		return $this->article;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function setPrefix($prefixId)
	{
		$this->prefixId = ($prefixId === null ? $prefixId : intval($prefixId));
	}

	public function setNotifyWatchers($value = true)
	{
		$this->notifyWatchers = (bool)$value;
	}

	public function addExtraSetup(callable $extra)
	{
		$this->extraSetup[] = $extra;
	}

	public function move(\XenAddons\AMS\Entity\Category $category)
	{
		$user = \XF::visitor();

		$article = $this->article;
		$oldCategory = $article->Category;

		$moved = ($article->category_id != $category->category_id);

		foreach ($this->extraSetup AS $extra)
		{
			call_user_func($extra, $article, $category);
		}

		$article->category_id = $category->category_id;
		if ($this->prefixId !== null)
		{
			$article->prefix_id = $this->prefixId;
		}

		if (!$article->preSave())
		{
			throw new \XF\PrintableException($article->getErrors());
		}

		$db = $this->db();
		$db->beginTransaction();

		$article->save(true, false);

		$db->commit();

		if ($moved && $article->isVisible() && $this->alert && $article->user_id != $user->user_id)
		{
			/** @var \XenAddons\AMS\Repository\Article $articleRepo */
			$articleRepo = $this->repository('XenAddons\AMS:Article');
			$articleRepo->sendModeratorActionAlert($this->article, 'move', $this->alertReason);
		}

		if ($moved && $this->notifyWatchers)
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\Notify $notifier */
			$notifier = $this->service('XenAddons\AMS:ArticleItem\Notify', $article, 'article');
			if ($oldCategory)
			{
				$notifier->skipUsersWatchingCategory($oldCategory);
			}
			$notifier->notifyAndEnqueue(3);
		}

		return $moved;
	}
}