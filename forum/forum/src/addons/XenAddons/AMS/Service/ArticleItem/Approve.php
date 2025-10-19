<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class Approve extends \XF\Service\AbstractService
{
	/**
	 * @var ArticleItem
	 */
	protected $article;

	protected $notifyRunTime = 3;

	public function __construct(\XF\App $app, ArticleItem $article)
	{
		parent::__construct($app);
		$this->article = $article;
	}

	public function getArticle()
	{
		return $this->article;
	}

	public function setNotifyRunTime($time)
	{
		$this->notifyRunTime = $time;
	}

	public function approve()
	{
		if ($this->article->article_state == 'moderated')
		{
			$this->article->article_state = 'visible';
			$this->article->save();

			$this->onApprove();
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function onApprove()
	{
		$visitor = \XF::visitor();
		$article = $this->article;

		if ($article)
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\Notify $notifier */
			$notifier = $this->service('XenAddons\AMS:ArticleItem\Notify', $article, 'article');
			$notifier->notifyAndEnqueue($this->notifyRunTime);
			
			// Sends an alert to the article author letting them know that their Article has been approved. 
			if ($article->user_id != $visitor->user_id)
			{
				/** @var \XenAddons\AMS\Repository\Article $articleRepo */
				$articleRepo = $this->repository('XenAddons\AMS:Article');
				$articleRepo->sendModeratorActionAlert($article, 'approve');
			}
			
			// check to see if there is an associated discussion thread and set the thread to visible and open
			if (
				$article->discussion_thread_id
				&& $article->Discussion
				&& $article->Discussion->discussion_type == 'ams_article'
			)
			{
				$thread = $article->Discussion;
				$thread->discussion_state = 'visible';
				$thread->discussion_open = true;
				$thread->saveIfChanged($saved, false, false);
			}
		}
	}
}