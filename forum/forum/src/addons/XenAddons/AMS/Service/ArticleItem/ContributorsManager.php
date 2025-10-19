<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XF\Entity\User;
use XF\Mvc\Entity\AbstractCollection;
use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;
use XF\Util\Arr;
use XenAddons\AMS\Entity\ArticleItem;

class ContributorsManager extends AbstractService
{
	use ValidateAndSavableTrait;

	/**
	 * @var ArticleItem
	 */
	protected $article;

	/**
	 * @var bool
	 */
	protected $autoSendNotifications = true;

	/**
	 * @var \XF\Entity\User[]|AbstractCollection
	 */
	protected $addCoAuthors;
	
	/**
	 * @var \XF\Entity\User[]|AbstractCollection
	 */
	protected $setCoAuthors;
	
	/**
	 * @var \XF\Entity\User[]|AbstractCollection
	 */
	protected $addContributors;

	/**
	 * @var \XF\Entity\User[]|AbstractCollection
	 */
	protected $removeContributors;

	/**
	 * @var bool
	 */
	protected $sendLeaveNotification = false;

	/**
	 * @var array
	 */
	protected $errors = [];

	public function __construct(\XF\App $app, ArticleItem $article)
	{
		parent::__construct($app);

		$this->article = $article;
		$this->addCoAuthors = $this->em()->getEmptyCollection();
		$this->setCoAuthors = $this->em()->getEmptyCollection();
		$this->addContributors = $this->em()->getEmptyCollection();
		$this->removeContributors = $this->em()->getEmptyCollection();
	}

	public function getArticle(): ArticleItem
	{
		return $this->article;
	}

	public function getCurrentContributorCount(): int
	{
		return $this->article->Contributors->count();
	}

	public function getNewContributorCount(): int
	{
		$currentCount = $this->getCurrentContributorCount();
		$addingNewContributorsCount = $this->addContributors ? $this->addContributors->count() : 0;
		$addingNewCoAuthorsCount = $this->addCoAuthors ? $this->addCoAuthors->count() : 0;
		$addingCount = $addingNewContributorsCount + $addingNewCoAuthorsCount;
		$removingCount = $this->removeContributors ? $this->removeContributors->count() : 0;
		
		return $currentCount + $addingCount - $removingCount;
	}

	public function setAutoSendNotifications(bool $send)
	{
		$this->autoSendNotifications = $send;
	}
	
	/**
	 * @param AbstractCollection|string $coAuthors Collection of users or comma-separated usernames (Co Authors are also and count as, "contributors"). 
	 */
	public function addCoAuthors($coAuthors)
	{
		if (is_string($coAuthors))
		{
			/** @var \XF\Repository\User $userRepo */
			$userRepo = $this->repository('XF:User');
			$usernames = Arr::stringToArray($coAuthors, '/\s*,\s*/');
			$users = $userRepo->getUsersByNames($usernames, $notFound);
	
			if ($notFound)
			{
				$this->errors[] = \XF::phrase(
					'following_members_not_found_x',
					['members' => implode(', ', $notFound)]
				);
			}
		}
		else if ($coAuthors instanceof AbstractCollection)
		{
			$users = $coAuthors->filter(function($member)
			{
				return $member instanceof User;
			});
		}
		else
		{
			throw new \InvalidArgumentException(
				'CoAuthors must be a collection of users or a string of comma-separated usernames'
			);
		}
	
		$addCoAuthors = [];
		$setCoAuthors = [];
		$invalidCoAuthors = [];
		foreach ($users AS $userId => $user)
		{
			if (
				$userId == $this->article->user_id 
				|| $this->article->Contributors[$userId]  // Member is already a contributor 
			)
			{
				if ($this->article->user_id == $user->user_id)
				{
					$invalidCoAuthors[$userId] = $user->username;
					continue;
				}
				
				$setCoAuthors[$userId] = $user;
				continue;
			}
	
			if (!$this->article->canContributorBeAdded($user))  // Note, this method is used for both co authors and contributors!
			{
				$invalidCoAuthors[$userId] = $user->username;
				continue;
			}
	
			$addCoAuthors[$userId] = $user;
		}
	
		if ($invalidCoAuthors)
		{
			$this->errors[] = \XF::phrase(
				'xa_ams_you_may_not_add_following_co_authors_to_this_article_x',
				['names' => implode(', ', $invalidCoAuthors)]
			);
		}
		
		$this->setCoAuthors = $this->em()->getBasicCollection($setCoAuthors);
		$this->addCoAuthors = $this->em()->getBasicCollection($addCoAuthors);
	}
	
	/**
	 * @var \XF\Entity\User[]|AbstractCollection
	 */
	public function getAddCoAuthors(): AbstractCollection
	{
		return $this->addCoAuthors;
	}

	/**
	 * @param AbstractCollection|string $contributors Collection of users or comma-separated usernames
	 */
	public function addContributors($contributors)
	{
		if (is_string($contributors))
		{
			/** @var \XF\Repository\User $userRepo */
			$userRepo = $this->repository('XF:User');
			$usernames = Arr::stringToArray($contributors, '/\s*,\s*/');
			$users = $userRepo->getUsersByNames($usernames, $notFound);

			if ($notFound)
			{
				$this->errors[] = \XF::phrase(
					'following_members_not_found_x',
					['members' => implode(', ', $notFound)]
				);
			}
		}
		else if ($contributors instanceof AbstractCollection)
		{
			$users = $contributors->filter(function($member)
			{
				return $member instanceof User;
			});
		}
		else
		{
			throw new \InvalidArgumentException(
				'Contributors must be a collection of users or a string of comma-separated usernames'
			);
		}

		$addContributors = [];
		$invalidContributors = [];
		foreach ($users AS $userId => $user)
		{
			if (
				$userId == $this->article->user_id ||
				$this->article->Contributors[$userId]
			)
			{
				continue;
			}

			if (!$this->article->canContributorBeAdded($user))
			{
				$invalidContributors[$userId] = $user->username;
				continue;
			}

			$addContributors[$userId] = $user;
		}

		if ($invalidContributors)
		{
			$this->errors[] = \XF::phrase(
				'xa_ams_you_may_not_add_following_contributors_to_this_article_x',
				['names' => implode(', ', $invalidContributors)]
			);
		}

		$this->addContributors = $this->em()->getBasicCollection($addContributors);
	}

	/**
	 * @var \XF\Entity\User[]|AbstractCollection
	 */
	public function getAddContributors(): AbstractCollection
	{
		return $this->addContributors;
	}

	/**
	 * @param AbstractCollection|int[] $contributors Collection of users or array of user IDs
	 */
	public function removeContributors($contributors)
	{
		if (is_array($contributors))
		{
			$userIds = array_intersect(
				$contributors,
				$this->article->Contributors->keys()
			);
			$users = $this->em()->findByIds('XF:User', $userIds);
		}
		else if ($contributors instanceof AbstractCollection)
		{
			$users = $contributors->filter(function($member)
			{
				return $member instanceof User;
			});
		}
		else
		{
			throw new \InvalidArgumentException(
				'Contributors must be a collection of users or an array of user IDs'
			);
		}

		$this->removeContributors = $users;
	}

	public function leaveContributorsTeam(\XF\Entity\User $user, bool $sendLeaveNotification = true)
	{
		if ($this->removeContributors->count())
		{
			throw new \LogicException("Only one of leaveContributorsTeam and removeContributors should be called");
		}

		$this->removeContributors = $this->em()->getBasicCollection([$user]);

		$this->sendLeaveNotification = $sendLeaveNotification;
	}

	/**
	 * @var \XF\Entity\User[]|AbstractCollection
	 */
	public function getRemoveContributors(): AbstractCollection
	{
		return $this->removeContributors;
	}

	public function setSendLeaveNotification(bool $send)
	{
		$this->sendLeaveNotification = $send;
	}

	public function getSendLeaveNotification(): bool
	{
		return $this->sendLeaveNotification;
	}

	protected function _validate(): array
	{
		if ($this->addContributors && $this->addContributors->count())
		{
			$maxAllowed = $this->article->getMaxContributorCount();
			$newCount = $this->getNewContributorCount();
			if ($newCount > $maxAllowed)
			{
				$this->errors[] = \XF::phrase(
					'xa_ams_you_may_only_add_x_contributors_co_authors_to_this_article',
					['count' => $maxAllowed]
				);
			}
		}
		
		if ($this->addCoAuthors && $this->addCoAuthors->count())
		{
			$maxAllowed = $this->article->getMaxContributorCount();
			$newCount = $this->getNewContributorCount();
			if ($newCount > $maxAllowed)
			{
				$this->errors[] = \XF::phrase(
					'xa_ams_you_may_only_add_x_contributors_co_authors_to_this_article',
					['count' => $maxAllowed]
				);
			}
		}

		return $this->errors;
	}

	protected function _save(): bool
	{
		$this->db()->beginTransaction();

		foreach ($this->addCoAuthors AS $addCoAuthor)
		{
			$coAuthor = $this->em()->create('XenAddons\AMS:ArticleContributor');
			$coAuthor->article_id = $this->article->article_id;
			$coAuthor->user_id = $addCoAuthor->user_id;
			$coAuthor->is_co_author = true;
			$coAuthor->save(true, false);
		}
		
		foreach ($this->setCoAuthors AS $setCoAuthor)
		{
			$coAuthor = $this->article->Contributors[$setCoAuthor->user_id];
			if (!$coAuthor)
			{
				continue;
			}
			
			$coAuthor->is_co_author = true;
			$coAuthor->save(true, false);
		}
		
		foreach ($this->addContributors AS $addContributor)
		{
			$contributor = $this->em()->create('XenAddons\AMS:ArticleContributor');
			$contributor->article_id = $this->article->article_id;
			$contributor->user_id = $addContributor->user_id;
			$contributor->save(true, false);
		}

		foreach ($this->removeContributors AS $removeContributor)
		{
			$contributor = $this->article->Contributors[$removeContributor->user_id];
			if (!$contributor)
			{
				continue;
			}

			$contributor->delete(true, false);
		}

		$this->db()->commit();

		if ($this->autoSendNotifications)
		{
			$this->sendNotifications();
		}

		return true;
	}

	public function sendNotifications()
	{
		foreach ($this->addCoAuthors AS $addCoAuthor)
		{
			/** @var \XF\Repository\UserAlert $alertRepo */
			$alertRepo = $this->repository('XF:UserAlert');
			$alertRepo->alert(
				$addCoAuthor,
				$this->article->user_id,
				$this->article->username,
				'ams_article',
				$this->article->article_id,
				'co_author_add'
			);
		}
		
		foreach ($this->setCoAuthors AS $setCoAuthor)
		{
			/** @var \XF\Repository\UserAlert $alertRepo */
			$alertRepo = $this->repository('XF:UserAlert');
			$alertRepo->alert(
				$setCoAuthor,
				$this->article->user_id,
				$this->article->username,
				'ams_article',
				$this->article->article_id,
				'co_author_add'
			);
		}		
		
		foreach ($this->addContributors AS $addContributor)
		{
			/** @var \XF\Repository\UserAlert $alertRepo */
			$alertRepo = $this->repository('XF:UserAlert');
			$alertRepo->alert(
				$addContributor,
				$this->article->user_id,
				$this->article->username,
				'ams_article',
				$this->article->article_id,
				'contributor_add'
			);
		}

		if ($this->sendLeaveNotification && $this->article->User)
		{
			foreach ($this->removeContributors AS $removeContributor)
			{
				/** @var \XF\Repository\UserAlert $alertRepo */
				$alertRepo = $this->repository('XF:UserAlert');
				$alertRepo->alert(
					$this->article->User,
					$removeContributor->user_id,
					$removeContributor->username,
					'ams_article',
					$this->article->article_id,
					'contributor_leave'
				);
			}
		}
	}
}