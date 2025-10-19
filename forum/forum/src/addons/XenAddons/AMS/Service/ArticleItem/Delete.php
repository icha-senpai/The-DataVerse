<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class Delete extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

	/**
	 * @var \XF\Entity\User|null
	 */
	protected $user;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, ArticleItem $article)
	{
		parent::__construct($app);
		$this->article = $article;
	}

	public function getArticle()
	{
		return $this->article;
	}

	public function setUser(\XF\Entity\User $user = null)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function delete($type, $reason = '')
	{
		$user = $this->user ?: \XF::visitor();
		$wasVisible = $this->article->isVisible();

		if ($type == 'soft')
		{
			$result = $this->article->softDelete($reason, $user);
		}
		else
		{
			$result = $this->article->delete();
		}

		$this->updateArticleThread();

		if ($result && $wasVisible && $this->alert && $this->article->user_id != $user->user_id)
		{
			/** @var \XenAddons\AMS\Repository\Article $articleRepo */
			$articleRepo = $this->repository('XenAddons\AMS:Article');
			$articleRepo->sendModeratorActionAlert($this->article, 'delete', $this->alertReason);
		}

		return $result;
	}

	protected function updateArticleThread()
	{
		$article = $this->article;
		$thread = $article->Discussion;
		if (!$thread)
		{
			return;
		}

		$asUser = $article->User ?: $this->repository('XF:User')->getGuestUser($article->username);

		\XF::asVisitor($asUser, function() use ($thread)
		{
			$replier = $this->setupArticleThreadReply($thread);
			if ($replier && $replier->validate())
			{
				$existingLastPostDate = $replier->getThread()->last_post_date;

				$post = $replier->save();
				$this->afterArticleThreadReplied($post, $existingLastPostDate);

				\XF::runLater(function() use ($replier)
				{
					$replier->sendNotifications();
				});
			}
		});
	}

	protected function setupArticleThreadReply(\XF\Entity\Thread $thread)
	{
		/** @var \XF\Service\Thread\Replier $replier */
		$replier = $this->service('XF:Thread\Replier', $thread);
		$replier->setIsAutomated();
		$replier->setMessage($this->getThreadReplyMessage(), false);

		return $replier;
	}

	protected function getThreadReplyMessage()
	{
		$article = $this->article;

		$phrase = \XF::phrase('xa_ams_article_thread_delete', [
			'title' => $article->title_,
			'description' => $article->description_,
			'username' => $article->User ? $article->User->username : $article->username
		]);

		return $phrase->render('raw');
	}

	protected function afterArticleThreadReplied(\XF\Entity\Post $post, $existingLastPostDate)
	{
		$thread = $post->Thread;

		if (\XF::visitor()->user_id && $post->Thread->getVisitorReadDate() >= $existingLastPostDate)
		{
			$this->repository('XF:Thread')->markThreadReadByVisitor($thread);
		}
	}
}