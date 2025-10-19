<?php

namespace XenAddons\AMS\Service\Feed;

use XF\Service\AbstractService;
use XenAddons\AMS\Entity\Feed;
use XenAddons\AMS\Entity\ArticleItem;
use XenAddons\AMS\Service\Feed\Reader;

class Feeder extends AbstractService
{
	/** @var Feed */
	protected $feed;

	protected $feedData;

	protected $pendingEntries;

	public function setupImport(Feed $feed, $haltOnError = false)
	{
		$this->setFeed($feed);
		$this->pendingEntries = null;

		$reader = $this->getFeedReader();
		$feedData = $reader->getFeedData();
		if (!$feedData && empty($feedData['entries']))
		{
			// we've tried to fetch the feed data and failed we should trigger an update
			// of the last_fetch date to ensure we don't end up infinitely trying to fetch
			$this->updateLastFetch($feed);

			if ($haltOnError)
			{
				throw new \XF\PrintableException(\XF::phrase('there_was_problem_requesting_feed', [
					'message' => $reader->getException()
						? $reader->getException()->getMessage()
						: \XF::phrase('n_a')
				]));
			}

			return null;
		}

		$this->setFeedData($feedData);
		$this->updateLastFetch($feed);

		return $feedData;
	}

	protected function updateLastFetch(\XenAddons\AMS\Entity\Feed $feed, $time = null)
	{
		$feed->last_fetch = $time ?: time();
		$feed->save(false);
	}

	protected function setFeed(Feed $feed)
	{
		$this->feed = $feed;
	}

	protected function setFeedData(array $feedData)
	{
		$this->feedData = $feedData;
	}

	protected function getPendingEntries()
	{
		if ($this->pendingEntries === null)
		{
			$feedData = $this->feedData;

			$ids = [];
			foreach ($feedData['entries'] AS $id => $entry)
			{
				$uniqueEntryId = utf8_substr($entry['id'], 0, 250);
				if (isset($ids[$uniqueEntryId]))
				{
					unset($feedData['entries'][$id]);
					continue;
				}

				$ids[$uniqueEntryId] = $id;
			}

			$existingEntries = $this->app->finder('XenAddons\AMS:FeedLog')
				->where('feed_id', $this->feed->feed_id)
				->where('unique_id', array_keys($ids))
				->keyedBy('unique_id')
				->fetch();

			$this->pendingEntries = array_reverse(
				array_filter($feedData['entries'], function($entry) use($existingEntries)
				{
					return !isset($existingEntries[$entry['id']]);
				})
			);
		}

		return $this->pendingEntries;
	}

	public function countPendingEntries()
	{
		return count($this->getPendingEntries());
	}

	public function importEntries()
	{
		$entries = $this->getPendingEntries();

		foreach ($entries AS $entry)
		{
			$this->importEntry($entry);
		}
	}

	public function importEntry(array $entry)
	{
		$actionUser = $this->getEntryActionUser($entry);

		return \XF::asVisitor($actionUser, function() use ($entry)
		{
			$creator = $this->setupArticleCreate($entry);
			if (!$creator->validate($errors))
			{
				$error = reset($errors);
				$feedId = $this->feed->feed_id;
				\XF::logException(new \Exception("Error posting feed entry $entry[id] for feed $feedId: $error"));
				return false;
			}

			$db = $this->db();
			$db->beginTransaction();

			$creator->save();
			$this->logEntry($entry, $creator->getArticle());

			$db->commit();

			$creator->sendNotifications();

			return true;
		});
	}

	protected function getEntryActionUser(array $entry)
	{
		$feed = $this->feed;

		if ($feed->user_id && $feed->User)
		{
			return $feed->User;
		}

		$userRepo = $this->repository('XF:User');
		$strFormatter = $this->app->stringFormatter();

		if (!empty($entry['author']))
		{
			return $userRepo->getGuestUser($strFormatter->wholeWordTrim($entry['author'], 50, 0, ''));
		}
		else
		{
			return $userRepo->getGuestUser($strFormatter->wholeWordTrim($feed->title, 50, 0, ''));
		}
	}
	
	/**
	 * @return \XenAddons\AMS\Service\ArticleItem\Create
	 */
	protected function setupArticleCreate(array $entry)
	{
		$feed = $this->feed;
	
		/** @var \XenAddons\AMS\Service\ArticleItem\Create $creator */
		$creator = $this->service('XenAddons\AMS:ArticleItem\Create', $feed->Category);
	
		$creator->setIsAutomated();
		$creator->setContent($feed->getEntryTitle($entry), $feed->getEntryMessage($entry));
		$creator->setPrefix($feed->prefix_id);
		$creator->setArticleState($feed->article_visible ? 'visible' : 'moderated');
	
		return $creator;
	}	

	protected function logEntry(array $entry, ArticleItem $article)
	{
		$feed = $this->feed;

		$log = $this->em()->create('XenAddons\AMS:FeedLog');
		$log->feed_id = $feed->feed_id;
		$log->unique_id = utf8_substr($entry['id'], 0, 250);
		$log->hash = md5($entry['id'] . $entry['title'] . $entry['content']);
		$log->article_id = $article->article_id;
		$log->save(false, false);
	}

	/**
	 * @return Reader
	 */
	protected function getFeedReader()
	{
		return $this->service('XenAddons\AMS:Feed\Reader', $this->feed->url);
	}
}