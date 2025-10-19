<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class PublishDraft extends \XF\Service\AbstractService
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

	public function publishDraft($isAutomated = false)
	{
		if ($this->article->article_state == 'draft' || $this->article->article_state == 'awaiting')
		{
			if ($isAutomated)
			{
				// TODO for now, set this to visible... in the future, check the article authors permissions on whether to bypass queue or not
				$this->article->article_state = 'visible';
			}
			else 
			{
				$this->article->article_state = $this->article->Category->getNewArticleState();
			}	
			
			$this->article->publish_date = \XF::$time;
			$this->article->edit_date = \XF::$time;
			$this->article->last_update = \XF::$time;
			$this->article->save();

			$this->onPublishDraft();
			
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function onPublishDraft()
	{
		$visitor = \XF::visitor();
		$article = $this->article;

		if ($article && $article->article_state == 'visible')
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\Notify $notifier */
			$notifier = $this->service('XenAddons\AMS:ArticleItem\Notify', $article, 'article');
			$notifier->notifyAndEnqueue($this->notifyRunTime);

			if ($article->Discussion && $article->Discussion->discussion_type == 'ams_article')
			{
				$thread = $article->Discussion;
				$thread->discussion_state = 'visible';
				$thread->discussion_open = true;
				$thread->saveIfChanged($saved, false, false);
			}
		}			
	}
}