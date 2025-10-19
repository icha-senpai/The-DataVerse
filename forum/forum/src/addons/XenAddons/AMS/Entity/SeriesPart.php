<?php

namespace XenAddons\AMS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null part_id
 * @property int series_id
 * @property int user_id
 * @property int article_id
 * @property int display_order
 * @property string title
 * @property int create_date
 * @property int edit_date
 * 
 * GETTERS
 *
 * RELATIONS
 * @property \XF\Entity\User User
 * @property \XenAddons\AMS\Entity\SeriesItem Series
 * @property \XenAddons\AMS\Entity\ArticleItem Article
 * @property \XF\Entity\DeletionLog DeletionLog
 */
class SeriesPart extends Entity
{
	public function canView(&$error = null)
	{
		return $this->Article->canView();
	}
	
	public function canEdit(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}

		if ($this->hasPermission('editAnySeries'))
		{
			return true;
		}

		return (
			$this->Series->user_id == $visitor->user_id
			&& $this->hasPermission('editOwnSeries')
		);
	}	

	public function canRemove(&$error = null)
	{
		$visitor = \XF::visitor();
		if (!$visitor->user_id)
		{
			return false;
		}

		if ($this->hasPermission('editAnySeries'))
		{
			return true;
		}

		return (
			$this->Series->user_id == $visitor->user_id
			&& $this->hasPermission('manageSeriesOwn')
		);
	}
	
	public function hasPermission($permission)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		return $visitor->hasAmsSeriesPermission($permission);
	}
	
	protected function _preSave()
	{
		if (!$this->series_id || !$this->article_id)
		{
			throw new \LogicException("Need series and article IDs");
		}
		
		if ($this->isInsert())
		{
			// TODO add in same presave validation from XF1? (maybe add into service validation instead)
		}
	}

	protected function _postSave()
	{
		$this->updateSeriesRecord();
		$this->updateArticleRecord();
		
		if ($this->isUpdate() && $this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorChanges('ams_series_part', $this);
		}
	}
	
	protected function updateSeriesRecord()
	{
		if (!$this->Series)
		{
			return;
		}
		
		$series = $this->Series;
		
		if ($this->isInsert())
		{
			$series->partAdded($this);
			$series->save();
		}
		
		if ($this->isUpdate())
		{
			$series->partUpdated($this);
			$series->save();
		}
	}	
	
	protected function updateArticleRecord()
	{
		if (!$this->Article)
		{
			return;
		}
	
		$article = $this->Article;
	
		if ($this->isInsert())
		{
			$article->addedToSeries($this);
			$article->save();
		}
	}

	protected function _postDelete()
	{
		$db = $this->db();
		
		if($this->Series)
		{
			$this->Series->partRemoved($this);
			$this->Series->save();
		}
		
		if ($this->Article)
		{
			$this->Article->removedFromSeries($this);
			$this->Article->save();
		}
		
		if ($this->getOption('log_moderator'))
		{
			$this->app()->logger()->logModeratorAction('ams_series_part', $this, 'delete_hard');
		}
		
		$db->delete('xf_deletion_log', 'content_id = ? AND content_type = ?', [$this->part_id, 'ams_series_part']);
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_series_part';
		$structure->shortName = 'XenAddons\AMS:SeriesPart';
		$structure->primaryKey = 'part_id';
		$structure->contentType = 'ams_series_part';
		$structure->columns = [
			'part_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'series_id' => ['type' => self::UINT, 'required' => true],
			'user_id' => ['type' => self::UINT, 'required' => true],
			'article_id' => ['type' => self::UINT, 'required' => true],
			'display_order' => ['type' => self::UINT, 'default' => 1],
			'title' => ['type' => self::STR, 'required' => true, 'maxLength' => 150],
			'create_date' => ['type' => self::UINT, 'default' => \XF::$time],
			'edit_date' => ['type' => self::UINT, 'default' => \XF::$time]	
		];
		$structure->getters = [
		];
		$structure->behaviors = [
		];
		$structure->relations = [
			'User' => [
				'entity' => 'XF:User',
				'type' => self::TO_ONE,
				'conditions' => 'user_id',
				'primary' => true
			],
			'Series' => [
				'entity' => 'XenAddons\AMS:SeriesItem',
				'type' => self::TO_ONE,
				'conditions' => 'series_id',
				'primary' => true
			],
			'Article' => [
				'entity' => 'XenAddons\AMS:ArticleItem',
				'type' => self::TO_ONE,
				'conditions' => 'article_id',
				'primary' => true
			],
			'DeletionLog' => [
				'entity' => 'XF:DeletionLog',
				'type' => self::TO_ONE,
				'conditions' => [
					['content_type', '=', 'ams_series_part'],
					['content_id', '=', '$part_id']
				],
				'primary' => true
			]
		];
		$structure->options = [
			'log_moderator' => true
		];
		$structure->defaultWith = ['Series', 'User', 'Article'];

		$structure->withAliases = [
			'full' => [
				'User', 
				'Article', 
				'Article.Featured', 
				'Article.Category', 
				'Article.CoverImage',
				function()
				{
					$userId = \XF::visitor()->user_id;
					if ($userId)
					{
						return ['Article.Read|' . $userId, 'Article.Watch|' . $userId];
					}
				
					return null;
				}
			]
		];		
		
		return $structure;
	}
}