<?php

namespace XenAddons\AMS\Finder;

use XF\Mvc\Entity\Finder;

class ArticleItem extends Finder
{
	public function inCategory($categoryId)
	{
		$this->where('category_id', $categoryId);
	
		return $this;
	}
	
	public function byUser(\XF\Entity\User $user)
	{
		$this->where('user_id', $user->user_id);
	
		return $this;
	}
	
	public function applyGlobalVisibilityChecks($allowOwnPending = false)
	{
		$visitor = \XF::visitor();
		$conditions = [];
		$viewableStates = ['visible'];

		if ($visitor->hasPermission('xa_ams', 'viewDeleted'))
		{
			$viewableStates[] = 'deleted';

			$this->with('DeletionLog');
		}

		if ($visitor->hasPermission('xa_ams', 'viewModerated'))
		{
			$viewableStates[] = 'moderated';
		}
		else if ($visitor->user_id && $allowOwnPending)
		{
			$conditions[] = [
				'article_state' => 'moderated',
				'user_id' => $visitor->user_id
			];
		}

		$conditions[] = ['article_state', $viewableStates];

		$this->whereOr($conditions);

		return $this;
	}

	public function applyVisibilityChecksInCategory(\XenAddons\AMS\Entity\Category $category, $allowOwnPending = false)
	{
		$conditions = [];
		$viewableStates = ['visible'];

		if ($category->canViewDeletedArticles())
		{
			$viewableStates[] = 'deleted';

			$this->with('DeletionLog');
		}
		
		$visitor = \XF::visitor();
		if ($category->canViewModeratedArticles())
		{
			$viewableStates[] = 'moderated';
		}
		else if ($visitor->user_id && $allowOwnPending)
		{
			$conditions[] = [
				'article_state' => 'moderated',
				'user_id' => $visitor->user_id
			];
		}

		$conditions[] = ['article_state', $viewableStates];

		$this->whereOr($conditions);

		return $this;
	}
	
	public function withReadData($userId = null)
	{
		if ($userId === null)
		{
			$userId = \XF::visitor()->user_id;
		}
	
		if ($userId)
		{
			$this->with([
				'Read|' . $userId,
			]);
		}
	
		return $this;
	}
	
	public function unreadOnly($userId = null)
	{
		if ($userId === null)
		{
			$userId = \XF::visitor()->user_id;
		}
		if (!$userId)
		{
			// no user, no read tracking
			return $this;
		}
	
		$articleItemReadExpression = $this->expression(
			'%s > COALESCE(%s, 0)',
			'last_update',
			'Read|' . $userId . '.article_read_date'
		);
	
		$this
		->where('last_update', '>', (
			\XF::$time - $this->app()->options()->readMarkingDataLifetime * 86400)
		)
		->where($articleItemReadExpression);
	
		return $this;
	}	

	public function watchedOnly($userId = null)
	{
		if ($userId === null)
		{
			$userId = \XF::visitor()->user_id;
		}
		if (!$userId)
		{
			// no user, just ignore
			return $this;
		}

		$this->whereOr(
			['Watch|' . $userId . '.user_id', '!=', null],
			['Category.Watch|' . $userId . '.user_id', '!=', null]
		);

		return $this;
	}

	/**
	 * @deprecated Use with('full') or with('fullCategory') instead
	 *
	 * @param bool $includeCategory
	 * @return $this
	 */
	public function forFullView($includeCategory = true)
	{
		$this->with($includeCategory ? 'fullCategory' : 'full');
	
		return $this;
	}
	
	/**
	 * @param string $direction
	 *
	 * @return Finder
	 */
	public function orderByDate($order = 'publish_date', $direction = 'DESC')
	{
		$this->setDefaultOrder([
			[$order, $direction],
			['article_id', $direction]
		]);
	
		return $this;
	}	

	public function useDefaultOrder(\XenAddons\AMS\Entity\Category $category = null)
	{
		// Check to see if a Category Entity is being passed in and check if there is a Category Override for using a category specific default sort order.
		if ($category && isset($category->article_list_order) && $category->article_list_order)
		{
			$defaultOrder = $category->article_list_order ?: 'publish_date';
		}
		else
		{
			$defaultOrder = $this->app()->options()->xaAmsListDefaultOrder ?: 'publish_date';
		}
		
		$defaultDir = $defaultOrder == 'title' ? 'asc' : 'desc';

		$this->setDefaultOrder($defaultOrder, $defaultDir);

		return $this;
	}
	
	public function withUnreadCommentsOnly($userId = null)
	{
		if ($userId === null)
		{
			$userId = \XF::visitor()->user_id;
		}
		if (!$userId)
		{
			// no user, no read tracking
			return $this;
		}
	
		$articleCommentReadExpression = $this->expression(
			'%s > COALESCE(%s, 0)',
			'last_comment_date',
			'CommentRead|' . $userId . '.comment_read_date'
		);
	
		$this
		->where('last_comment_date', '>', (
			\XF::$time - $this->app()->options()->readMarkingDataLifetime * 86400)
		)
		->where($articleCommentReadExpression);
	
		return $this;
	}	
}