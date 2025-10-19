<?php

namespace XenAddons\AMS\Finder;

use XF\Mvc\Entity\Finder;

class SeriesItem extends Finder
{
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
	
		if ($visitor->hasPermission('xa_ams', 'viewDeletedSeries'))
		{
			$viewableStates[] = 'deleted';
	
			$this->with('DeletionLog');
		}
	
		if ($visitor->hasPermission('xa_ams', 'viewModeratedSeries'))
		{
			$viewableStates[] = 'moderated';
		}
		else if ($visitor->user_id && $allowOwnPending)
		{
			$conditions[] = [
				'series_state' => 'moderated',
				'user_id' => $visitor->user_id
			];
		}
	
		$conditions[] = ['series_state', $viewableStates];
	
		$this->whereOr($conditions);
	
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
			['Watch|' . $userId . '.user_id', '!=', null]
		);

		return $this;
	}

	/**
	 * @deprecated Use with('full') instead
	 *
	 * @return $this
	 */
	public function forFullView()
	{
		$this->with('full');
	
		return $this;
	}
	
	/**
	 * @param string $direction
	 *
	 * @return Finder
	 */
	public function orderByDate($order = 'create_date', $direction = 'DESC')
	{
		$this->setDefaultOrder([
			[$order, $direction],
			['series_id', $direction]
		]);
	
		return $this;
	}	

	public function useDefaultOrder()
	{
		$this->setDefaultOrder([
			['last_part_date', 'desc'],
			['create_date', 'desc']
		]);
		
		return $this;
	}
}