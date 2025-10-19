<?php

namespace XenAddons\AMS\XF\Entity;

use XF\Mvc\Entity\Structure;

class User extends XFCP_User
{
	public function canViewAmsArticles(&$error = null)
	{
		return $this->hasPermission('xa_ams', 'view');
	}
	
	public function canViewAmsSeries(&$error = null)
	{
		return $this->hasPermission('xa_ams', 'viewSeries');
	}
	
	public function canViewAmsComments(&$error = null)
	{
		return $this->hasPermission('xa_ams', 'viewComments');
	}
	
	public function canViewAmsArticlesQueue(&$error = null)
	{
		if (!\XF::visitor()->is_moderator)
		{
			return false;
		}
		
		return ($this->hasPermission('xa_ams', 'viewDraft') || $this->hasPermission('xa_ams', 'viewAwaiting'));
	}

	public function canAddAmsArticle(&$error = null)
	{
		if (!\XF::visitor()->user_id || !$this->hasPermission('xa_ams','add'))
		{
			return false;
		}
		
		$maxArticleCount = $this->hasPermission('xa_ams','maxArticleCount');
		$userArticleCount = \XF::visitor()->xa_ams_article_count;
		
		if ($maxArticleCount == -1 || $maxArticleCount == 0) // unlimited NOTE: in this particular case, we want 0 to count as unlimited.
		{
			return true;
		}
		
		if ($userArticleCount < $maxArticleCount)
		{
			return true;
		}
		
		return false;
	}
	
	public function canCreateAmsSeries(&$error = null)
	{
		if (!\XF::visitor()->user_id || !$this->hasPermission('xa_ams','createSeries'))
		{
			return false;
		}
		
		$maxSeriesCount = $this->hasPermission('xa_ams','maxSeriesCount');
		$userSeriesCount = \XF::visitor()->xa_ams_series_count;
		
		if ($maxSeriesCount == -1 || $maxSeriesCount == 0) // unlimited NOTE: in this particular case, we want 0 to count as unlimited.
		{
			return true;
		}
		
		if ($userSeriesCount < $maxSeriesCount)
		{
			return true;
		}
		
		return false;		
	}
	
	public function hasAmsArticlePermission($permission)
	{
		return $this->hasPermission('xa_ams', $permission);
	}
	
	public function hasAmsSeriesPermission($permission)
	{
		return $this->hasPermission('xa_ams', $permission);
	}

	public function hasAmsArticleCategoryPermission($contentId, $permission)
	{
		return $this->PermissionSet->hasContentPermission('ams_category', $contentId, $permission); 
	}

	public function cacheAmsArticleCategoryPermissions(array $categoryIds = null)
	{
		if (is_array($categoryIds))
		{
			\XF::permissionCache()->cacheContentPermsByIds($this->permission_combination_id, 'ams_category', $categoryIds);
		}
		else
		{
			\XF::permissionCache()->cacheAllContentPerms($this->permission_combination_id, 'ams_category');
		}
	}

	public static function getStructure(Structure $structure)
	{
		$structure = parent::getStructure($structure);

		$structure->columns['xa_ams_article_count'] = ['type' => self::UINT, 'default' => 0, 'forced' => true, 'changeLog' => false];
		$structure->columns['xa_ams_series_count'] = ['type' => self::UINT, 'default' => 0, 'forced' => true, 'changeLog' => false];
		
		return $structure;
	}
}