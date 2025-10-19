<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Repository;

class Comment extends Repository
{
	public function findCommentsForContent(\XF\Mvc\Entity\Entity $content, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\Comment $finder */
		$finder = $this->finder('XenAddons\AMS:Comment');
		$finder
			->forContent($content, $limits)
			->orderByDate()
			->with('full');

		return $finder;
	}

	public function findLatestCommentsForContent(\XF\Mvc\Entity\Entity $content, $newerThan, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\Comment $finder */
		$finder = $this->finder('XenAddons\AMS:Comment');
		$finder
			->forContent($content, $limits)
			->orderByDate('DESC')
			->newerThan($newerThan)
			->with('full');

		return $finder;
	}

	public function findNextCommentsInContent(\XF\Mvc\Entity\Entity $content, $newerThan, array $limits = [])
	{
		/** @var \XenAddons\AMS\Finder\Comment $finder */
		$finder = $this->finder('XenAddons\AMS:Comment');
		$finder
			->forContent($content, $limits)
			->orderByDate()
			->newerThan($newerThan);

		return $finder;
	}

	public function findLatestCommentsForWidget(array $viewableCategoryIds = null)
	{
		$finder = $this->finder('XenAddons\AMS:Comment');

		if (is_array($viewableCategoryIds))
		{
			$finder->where('Article.category_id', $viewableCategoryIds);
		}
		else
		{
			$finder->with('Article.Category.Permissions|' . \XF::visitor()->permission_combination_id);
		}
		
		$finder
			->where('comment_state', 'visible')
			->orderByDate('DESC')
			->with([
				'Article.Category',
			]);

		return $finder;
	}

	public function findCommentsForUser(\XF\Entity\User $user)
	{
		/** @var \XenAddons\AMS\Finder\Comment $finder */
		$finder = $this->finder('XenAddons\AMS:Comment');
		$finder->where('user_id', $user->user_id);
	
		$finder->where([
			'user_id' => $user->user_id,
			'comment_state' => 'visible'
		]);
	
		return $finder;
	}
	
	public function sendModeratorActionAlert(\XenAddons\AMS\Entity\Comment $comment, $action, $reason = '', array $extra = [], \XF\Entity\User $forceUser = null)
	{
		if (!$forceUser)
		{
			if (!$comment->user_id || !$comment->User)
			{
				return false;
			}
		
			$forceUser = $comment->User;
		}

		$extra = array_merge([
			'title' => $comment->Content->title,
			'prefix_id' => $comment->Content->prefix_id,
			'link' => $this->app()->router('public')->buildLink('nopath:ams/comments', $comment),
			'articleLink' => $this->app()->router('public')->buildLink('nopath:ams', $comment->Content),
			'reason' => $reason,
			'content_type' => 'ams_comment'
		], $extra);

		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = $this->repository('XF:UserAlert');
		$alertRepo->alert(
			$forceUser,
			0, '',
			'user', $forceUser->user_id,
			"ams_comment_{$action}", $extra,
			['dependsOnAddOnId' => 'XenAddons/AMS']
		);

		return true;
	}
}