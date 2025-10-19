<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null article_reply_ban_id
 * @property int article_id
 * @property int user_id
 * @property int ban_date
 * @property int|null expiry_date
 * @property string reason
 * @property int ban_user_id
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ArticleItem ArticleItem
 * @property \XF\Entity\User User
 * @property \XF\Entity\User BannedBy
 */
class ArticleReplyBan extends Entity
{
	protected function _preSave()
	{
		$ban = $this->em()->findOne('XenAddons\AMS:ArticleReplyBan', [
			'article_id' => $this->article_id,
			'user_id' => $this->user_id
		]);
		if ($ban && $ban != $this)
		{
			$this->error(\XF::phrase('xa_ams_this_user_is_already_reply_banned_from_this_article'));
		}
	}

	protected function _postDelete()
	{
		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = $this->repository('XF:UserAlert');
		$alertRepo->fastDeleteAlertsFromUser($this->BannedBy, 'ams_article', $this->article_id, 'reply_ban');

		$this->app()->logger()->logModeratorAction(
			'ams_article', $this->ArticleItem, 'reply_ban_delete', ['name' => $this->User->username]
		);
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_article_reply_ban';
		$structure->shortName = 'XenAddons\AMS:ArticleReplyBan';
		$structure->primaryKey = 'article_reply_ban_id';
		$structure->columns = [
			'article_reply_ban_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'article_id' => ['type' => self::UINT, 'required' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'ban_date' => ['type' => self::UINT, 'default' => \XF::$time],
			'expiry_date' => ['type' => self::UINT, 'required' => true, 'nullable' => true],
			'reason' => ['type' => self::STR, 'default' => '', 'maxLength' => 100],
			'ban_user_id' => ['type' => self::UINT, 'required' => true],
		];
		$structure->getters = [];
		$structure->relations = [
			'ArticleItem' => [
				'entity' => 'XenAddons\AMS:ArticleItem',
				'type' => self::TO_ONE,
				'conditions' => 'article_id',
				'primary' => true
			],
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
			'BannedBy' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => [['user_id', '=', '$ban_user_id']],
				'primary' => true
			]
		];

		return $structure;
	}
}