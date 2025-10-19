<?php

namespace XenAddons\AMS\Repository;

use XF\Mvc\Entity\Repository;

class SeriesWatch extends Repository
{
	public function setWatchState(\XenAddons\AMS\Entity\SeriesItem $series, \XF\Entity\User $user, $action, array $config = [])
	{
		if (!$series->series_id || !$user->user_id)
		{
			throw new \InvalidArgumentException("Invalid series or user");
		}

		$watch = $this->em->find('XenAddons\AMS:SeriesWatch', [
			'series_id' => $series->series_id,
			'user_id' => $user->user_id
		]);

		switch ($action)
		{
			case 'delete':
				if ($watch)
				{
					$watch->delete();
				}
				break;

			case 'watch':
				if (!$watch)
				{
					$watch = $this->em->create('XenAddons\AMS:SeriesWatch');
					$watch->series_id = $series->series_id;
					$watch->user_id = $user->user_id;
				}
				unset($config['series_id'], $config['user_id']);

				$watch->bulkSet($config);
				$watch->save();
				break;

			case 'update':
				if ($watch)
				{
					unset($config['series_id'], $config['user_id']);

					$watch->bulkSet($config);
					$watch->save();
				}
				break;

			default:
				throw new \InvalidArgumentException("Unknown action '$action' (expected: delete/watch/update)");
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
				unset($updates['series_id'], $updates['user_id']);
				return $db->update('xf_xa_ams_series_watch', $updates, 'user_id = ?', $user->user_id);

			case 'delete':
				return $db->delete('xf_xa_ams_series_watch', 'user_id = ?', $user->user_id);

			default:
				throw new \InvalidArgumentException("Unknown action '$action'");
		}
	}
}