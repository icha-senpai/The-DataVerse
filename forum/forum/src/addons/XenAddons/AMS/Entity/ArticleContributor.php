<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int article_id
 * @property int user_id
 * @property int is_co_author
 *
 * RELATIONS
 * @property \XenAddons\AMS\Entity\ArticleItem Article
 * @property \XF\Entity\User User
 */
class ArticleContributor extends Entity
{
	protected function _postSave()
	{
		$this->rebuildArticleContributorCache();
	}

	protected function _postDelete()
	{
		$this->rebuildArticleContributorCache();

		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = $this->repository('XF:UserAlert');
		$alertRepo->fastDeleteAlertsToUser(
			$this->user_id,
			'ams_article',
			$this->article_id,
			'contributor_add'
		);
	}

	protected function rebuildArticleContributorCache()
	{
		\XF::runOnce(
			'xaAmsArticleContributorCache' . $this->article_id,
			function()
			{
				/** @var \XenAddons\AMS\Repository\Article */
				$articleRepo = $this->repository('XenAddons\AMS:Article');
				$articleRepo->rebuildArticleContributorCache($this->Article);
			}
		);
	}

	public static function getStructure(Structure $structure): Structure
	{
		$structure->table = 'xf_xa_ams_article_contributor';
		$structure->shortName = 'XenAddons\AMS:ArticleContributor';
		$structure->primaryKey = ['article_id', 'user_id'];
		$structure->columns = [
			'article_id' => [
				'type' => self::UINT,
				'required' => true
			],
			'user_id' => [
				'type' => self::UINT,
				'required' => true
			],
			'is_co_author' => [
				'type' => self::BOOL, 
				'default' => false
			]
		];
		$structure->relations = [
			'Article' => [
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
			]
		];

		return $structure;
	}
}
