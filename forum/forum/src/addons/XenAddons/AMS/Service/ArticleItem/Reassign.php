<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class Reassign extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

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

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function reassignTo(\XF\Entity\User $newUser)
	{
		$article = $this->article;
		$oldUser = $article->User;
		$reassigned = ($article->user_id != $newUser->user_id);

		$article->user_id = $newUser->user_id;
		$article->username = $newUser->username;
		$article->save();

		if ($reassigned && $article->isVisible() && $this->alert)
		{
			if ($oldUser && \XF::visitor()->user_id != $oldUser->user_id)
			{
				/** @var \XenAddons\AMS\Repository\Article $articleRepo */
				$articleRepo = $this->repository('XenAddons\AMS:Article');
				$articleRepo->sendModeratorActionAlert(
					$this->article, 'reassign_from', $this->alertReason, ['to' => $newUser->username], $oldUser
				);
			}

			if (\XF::visitor()->user_id != $newUser->user_id)
			{
				/** @var \XenAddons\AMS\Repository\Article $articleRepo */
				$articleRepo = $this->repository('XenAddons\AMS:Article');
				$articleRepo->sendModeratorActionAlert(
					$this->article, 'reassign_to', $this->alertReason, [], $newUser
				);
			}
			
			// check to see if the Article has an associated discussion thread
			$thread = $article->Discussion;
			if ($thread)
			{
				// if the Article has a thread, lets reassign the thread and first post of the thread to the new article owner.
			
				// TODO might need to make this a bit more complex in handling (this is pretty basic).
			
				$thread->user_id = $newUser->user_id;
				$thread->username = $newUser->username;
				$thread->save();
			
				$firstPost = $thread->FirstPost;
				$firstPost->user_id = $newUser->user_id;
				$firstPost->username = $newUser->username;
				$firstPost->save();
			}
		}

		return $reassigned;
	}
}