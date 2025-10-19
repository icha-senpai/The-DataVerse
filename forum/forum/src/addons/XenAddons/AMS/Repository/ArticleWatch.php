<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Repository;

class ArticleWatch extends Repository
{
	public function autoWatchAmsArticleItem(\XenAddons\AMS\Entity\ArticleItem $article, \XF\Entity\User $user, $onCreation = false)
	{
		$userField = $onCreation ? 'creation_watch_state' : 'interaction_watch_state';

		if (!$article->article_id || !$user->user_id || !$user->Option->getValue($userField))
		{
			return null;
		}

		$watch = $this->em->find('XenAddons\AMS:ArticleWatch', [
			'article_id' => $article->article_id,
			'user_id' => $user->user_id
		]);
		if ($watch)
		{
			return null;
		}
		
		/** @var \XenAddons\AMS\Entity\ArticleWatch $watch */
		$watch = $this->em->create('XenAddons\AMS:ArticleWatch');
		$watch->article_id = $article->article_id;
		$watch->user_id = $user->user_id;
		$watch->email_subscribe = ($user->Option->getValue($userField) == 'watch_email');

		try
		{
			$watch->save();
		}
		catch (\XF\Db\DuplicateKeyException $e)
		{
			return null;
		}

		return $watch;
	}

	public function setWatchState(\XenAddons\AMS\Entity\ArticleItem $article, \XF\Entity\User $user, $action, array $config = [])
	{
		if (!$article->article_id || !$user->user_id)
		{
			throw new \InvalidArgumentException("Invalid article or user");
		}

		$watch = $this->em->find('XenAddons\AMS:ArticleWatch', [
			'article_id' => $article->article_id,
			'user_id' => $user->user_id
		]);

		switch ($action)
		{
			case 'watch':
				if (!$watch)
				{
					$watch = $this->em->create('XenAddons\AMS:ArticleWatch');
					$watch->article_id = $article->article_id;
					$watch->user_id = $user->user_id;
				}
				unset($config['article_id'], $config['user_id']);

				$watch->bulkSet($config);
				$watch->save();
				break;

			case 'update':
				if ($watch)
				{
					unset($config['article_id'], $config['user_id']);

					$watch->bulkSet($config);
					$watch->save();
				}
				break;

			case 'delete':
				if ($watch)
				{
					$watch->delete();
				}
				break;

			default:
				throw new \InvalidArgumentException("Unknown action '$action' (expected: delete/watch)");
		}
	}

	public function setWatchStateForAll(\XF\Entity\User $user, $action, array $updates = [])
	{
		if (!$user->user_id)
		{
			throw new \InvalidArgumentException("Invalid user");
		}

		$db = $this->db();

		switch ($action)
		{
			case 'update':
				unset($updates['article_id'], $updates['user_id']);
				return $db->update('xf_xa_ams_article_watch', $updates, 'user_id = ?', $user->user_id);

			case 'delete':
				return $db->delete('xf_xa_ams_article_watch', 'user_id = ?', $user->user_id);

			default:
				throw new \InvalidArgumentException("Unknown action '$action'");
		}
	}

	public function isValidWatchState($state)
	{
		switch ($state)
		{
			case 'watch':
			case 'update':
			case 'delete':

			default:
				return false;
		}
	}
}