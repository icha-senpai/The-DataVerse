<?php

namespace XenAddons\AMS\Entity;

use XF\Entity\AbstractCategoryTree;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null category_id
 * @property string title
 * @property string description
 * @property int article_count
 * @property int featured_count
 * @property int last_article_date
 * @property string last_article_title
 * @property int last_article_id
 * @property array field_cache
 * @property array review_field_cache
 * @property array prefix_cache
 * @property bool require_prefix
 * @property array field_cache
 * @property int thread_node_id
 * @property int thread_prefix_id
 * @property int thread_set_article_tags
 * @property int min_tags
 * @property int parent_category_id
 * @property int display_order
 * @property int lft
 * @property int rgt
 * @property int depth
 * @property array breadcrumb_data
 * @property string layout_type
 * @property string article_list_order
 * @property bool allow_poll
 * @property bool expand_category_nav
 *
 * GETTERS
 * @property \XF\Mvc\Entity\ArrayCollection prefixes
 * @property \XF\Draft draft_article
 *
 * RELATIONS
 * @property \XF\Entity\Forum ThreadForum
 * @property \XenAddons\AMS\Entity\CategoryWatch[] Watch
 * @property \XF\Entity\Draft[] DraftArticles
 * @property \XF\Entity\PermissionCacheContent[] Permissions
 */
class Category extends AbstractCategoryTree implements \XF\Entity\LinkableInterface
{
	protected $_viewableDescendants = [];

	public function canView(&$error = null)
	{
		return $this->hasPermission('view'); 
	}

	public function canViewDeletedArticles()
	{
		return $this->hasPermission('viewDeleted');
	}

	public function canViewModeratedArticles()
	{
		return $this->hasPermission('viewModerated');
	}

	public function canEditTags(ArticleItem $article = null, &$error = null)
	{
		if (!$this->app()->options()->enableTagging)
		{
			return false;
		}

		$visitor = \XF::visitor();

		// if no article, assume will be owned by this person
		if (!$article || $article->isContributor())
		{
			if ($this->hasPermission('tagOwnArticle'))
			{
				return true;
			}
		}

		return (
			$this->hasPermission('tagAnyArticle')
			|| $this->hasPermission('manageAnyTag')
		);
	}

	public function canUseInlineModeration(&$error = null)
	{
		return $this->hasPermission('inlineMod');
	}

	public function canUploadAndManageArticleAttachments()
	{
		return $this->hasPermission('uploadArticleAttach');
	}
	
	public function canUploadAndManagePageAttachments()
	{
		return $this->hasPermission('uploadArticleAttach');  // TODO maybe give this its own permission at some point
	}
	
	public function canUploadAndManageReviewImages()
	{
		return $this->hasPermission('uploadReviewAttach');
	}
	
	public function canUploadAndManageCommentImages()
	{
		return $this->hasPermission('uploadCommentAttach');
	}
	
	public function canUploadArticleVideos()
	{
		return $this->hasPermission('uploadArticleVideo');
	}
	
	public function canUploadReviewVideos()
	{
		return $this->hasPermission('uploadReviewVideo');
	}
	
	public function canUploadCommentVideos()
	{
		return $this->hasPermission('uploadCommentVideo');
	}

	public function canAddArticle(&$error = null)
	{
		if (!\XF::visitor()->user_id || !$this->hasPermission('add'))
		{
			return false;
		}
		
		$hasAllowedTypes = (
			$this->allow_articles
		);
		if (!$hasAllowedTypes)
		{
			$error = \XF::phraseDeferred('xa_ams_category_not_allow_new_articles');
			return false;
		}

		$maxArticleCount = $this->hasPermission('maxArticleCount');
		$userArticleCount = \XF::visitor()->xa_ams_article_count;
		
		if ($maxArticleCount == -1 || $maxArticleCount == 0) // unlimited NOTE: in this particular case, we want 0 to count as inlimited. 
		{
			return true;
		}
		
		if ($userArticleCount < $maxArticleCount)
		{
			return true;
		}

		return false;
	}

	public function canCreatePoll(&$error = null)
	{
		return $this->allow_poll;
	}
	
	public function canWatch(&$error = null)
	{
		return (\XF::visitor()->user_id ? true : false);
	}

	public function hasPermission($permission)
	{
		/** @var \XenAddons\AMS\XF\Entity\User $visitor */
		$visitor = \XF::visitor();
		return $visitor->hasAmsArticleCategoryPermission($this->category_id, $permission);
	}

	public function getViewableDescendants()
	{
		$userId = \XF::visitor()->user_id;
		if (!isset($this->_viewableDescendants[$userId]))
		{
			$viewable = $this->repository('XenAddons\AMS:Category')->getViewableCategories($this);
			$this->_viewableDescendants[$userId] = $viewable->toArray();
		}

		return $this->_viewableDescendants[$userId];
	}

	public function cacheViewableDescendents(array $descendents, $userId = null)
	{
		if ($userId === null)
		{
			$userId = \XF::visitor()->user_id;
		}

		$this->_viewableDescendants[$userId] = $descendents;
	}

	/**
	 * @return \XF\Draft
	 */
	public function getDraftArticle()
	{
		return \XF\Draft::createFromEntity($this, 'DraftArticles');
	}
	
	public function getMaxAllowedAttachmentsPerArticle()
	{
		return $this->hasPermission('maxAttachPerArticle');
	}

	public function getUsablePrefixes($forcePrefix = null)
	{
		$prefixes = $this->prefixes;

		if ($forcePrefix instanceof ArticlePrefix)
		{
			$forcePrefix = $forcePrefix->prefix_id;
		}

		$prefixes = $prefixes->filter(function($prefix) use ($forcePrefix)
		{
			if ($forcePrefix && $forcePrefix == $prefix->prefix_id)
			{
				return true;
			}
			return $this->isPrefixUsable($prefix);
		});

		return $prefixes->groupBy('prefix_group_id');
	}

	public function getPrefixesGrouped()
	{
		return $this->prefixes->groupBy('prefix_group_id');
	}

	/**
	 * @return \XF\Mvc\Entity\ArrayCollection
	 */
	public function getPrefixes()
	{
		if (!$this->prefix_cache)
		{
			return $this->_em->getEmptyCollection();
		}

		$prefixes = $this->finder('XenAddons\AMS:ArticlePrefix')
			->where('prefix_id', $this->prefix_cache)
			->order('materialized_order')
			->fetch();

		return $prefixes;
	}

	public function isPrefixUsable($prefix, \XF\Entity\User $user = null)
	{
		if (!$this->isPrefixValid($prefix))
		{
			return false;
		}

		if (!($prefix instanceof ArticlePrefix))
		{
			$prefix = $this->em()->find('XenAddons\AMS:ArticlePrefix', $prefix);
			if (!$prefix)
			{
				return false;
			}
		}

		return $prefix->isUsableByUser($user);
	}

	public function isPrefixValid($prefix)
	{
		if ($prefix instanceof ArticlePrefix)
		{
			$prefix = $prefix->prefix_id;
		}

		return (!$prefix || isset($this->prefix_cache[$prefix]));
	}

	public function getNewArticle()
	{
		$article = $this->_em->create('XenAddons\AMS:ArticleItem');
		$article->category_id = $this->category_id;
		
		$originalSourceDate = [
			'os_article_author' => '',
			'os_article_title' => '',
			'os_article_date' => 0,
			'os_source_name' => '',
			'os_source_url' => '',
		];
		
		$article->original_source = $originalSourceDate;

		return $article;
	}

	public function getNewArticleState(ArticleItem $article = null)
	{
		$visitor = \XF::visitor();

		if ($visitor->user_id && $this->hasPermission('approveUnapprove'))
		{
			return 'visible';
		}

		if (!$this->hasPermission('submitWithoutApproval'))  
		{
			return 'moderated';
		}
		
		return 'visible';
	}

	public function getBreadcrumbs($includeSelf = true, $linkType = 'public')
	{
		if ($linkType == 'public')
		{
			$link = 'ams/categories';
		}
		else
		{
			$link = 'xa-ams/categories';
		}
		return $this->_getBreadcrumbs($includeSelf, $linkType, $link);
	}

	public function getCategoryListExtras()
	{
		return [
			'article_count' => $this->article_count,
			'last_article_date' => $this->last_article_date,
			'last_article_title' => $this->last_article_title,
			'last_article_id' => $this->last_article_id
		];
	}

	public function articleAdded(ArticleItem $article)
	{
		$this->article_count++;

		if ($article->last_update >= $this->last_article_date)
		{
			$this->last_article_date = $article->last_update;
			$this->last_article_title = $article->title;
			$this->last_article_id = $article->article_id;
		}

		if ($article->Featured)
		{
			$this->featured_count++;
		}
	}

	public function articleDataChanged(ArticleItem $article)
	{
		if ($article->isChanged(['last_update', 'title']))
		{
			if ($article->last_update >= $this->last_article_date)
			{
				$this->last_article_date = $article->last_update;
				$this->last_article_title = $article->title;
				$this->last_article_id = $article->article_id;
			}
			else if ($article->getExistingValue('last_update') == $this->last_article_date)
			{
				$this->rebuildLastArticle();
			}
		}
	}

	public function articleRemoved(ArticleItem $article)
	{
		$this->article_count--;

		if ($article->last_update == $this->last_article_date)
		{
			$this->rebuildLastArticle();
		}

		if ($article->Featured)
		{
			$this->featured_count--;
		}
	}

	public function rebuildCounters()
	{
		$counters = $this->db()->fetchRow("
			SELECT COUNT(*) AS article_count
			FROM xf_xa_ams_article
			WHERE category_id = ?
				AND article_state = 'visible'
		", $this->category_id);

		$this->article_count = $counters['article_count'];

		$this->featured_count = $this->db()->fetchOne("
			SELECT COUNT(*)
			FROM xf_xa_ams_article_feature AS feature
				INNER JOIN xf_xa_ams_article AS article ON (article.article_id = feature.article_id)
			WHERE article.category_id = ?
				AND article.article_state = 'visible'
		", $this->category_id);

		$this->rebuildLastArticle();
	}

	public function rebuildLastArticle()
	{
		$article = $this->db()->fetchRow("
			SELECT *
			FROM xf_xa_ams_article
			WHERE category_id = ?
				AND article_state = 'visible'
			ORDER BY last_update DESC
			LIMIT 1
		", $this->category_id);
		if ($article)
		{
			$this->last_article_date = $article['last_update'];
			$this->last_article_title = $article['title'];
			$this->last_article_id = $article['article_id'];
		}
		else
		{
			$this->last_article_date = 0;
			$this->last_article_title = '';
			$this->last_article_id = 0;
		}
	}

	protected function _preSave()
	{
		if ($this->isChanged(['thread_node_id', 'thread_prefix_id']))
		{
			if (!$this->thread_node_id)
			{
				$this->thread_prefix_id = 0;
			}
			else
			{
				if (!$this->ThreadForum)
				{
					$this->thread_node_id = 0;
					$this->thread_prefix_id = 0;
				}
				else if ($this->thread_prefix_id && !$this->ThreadForum->isPrefixValid($this->thread_prefix_id))
				{
					$this->thread_prefix_id = 0;
				}
			}
		}
	}

	protected function _postDelete()
	{
		$db = $this->db();

		$db->delete('xf_xa_ams_category_field', 'category_id = ?', $this->category_id);
		$db->delete('xf_xa_ams_category_prefix', 'category_id = ?', $this->category_id);
		$db->delete('xf_xa_ams_category_review_field', 'category_id = ?', $this->category_id);
		$db->delete('xf_xa_ams_category_watch', 'category_id = ?', $this->category_id);

		if ($this->getOption('delete_articles'))
		{
			$this->app()->jobManager()->enqueueUnique('xa_amsCategoryDelete' . $this->category_id, 'XenAddons\AMS:CategoryDelete', [
				'category_id' => $this->category_id
			]);
		}
	}
	
	public function getContentUrl(bool $canonical = false, array $extraParams = [], $hash = null)
	{
		$route = ($canonical ? 'canonical:' : '') . 'ams/categories';
		return $this->app()->router('public')->buildLink($route, $this, $extraParams, $hash);
	}
	
	public function getContentPublicRoute()
	{
		return 'ams/categories';
	}
	
	public function getContentTitle(string $context = '')
	{
		return \XF::phrase('xa_ams_article_category_x', [
			'title' => $this->title
		]);
	}

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_xa_ams_category';
		$structure->shortName = 'XenAddons\AMS:Category';
		$structure->primaryKey = 'category_id';
		$structure->contentType = 'ams_category';
		$structure->columns = [
			'category_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'title' => ['type' => self::STR, 'maxLength' => 100,
				'required' => 'please_enter_valid_title'
			],
			'description' => ['type' => self::STR, 'default' => ''],
			'content_image' =>['type' => self::STR, 'default' => ''],
			'content_message' =>['type' => self::STR, 'default' => ''],
			'content_title' =>['type' => self::STR, 'default' => ''],
			'content_term' =>['type' => self::STR, 'default' => ''],
			'article_count' => ['type' => self::UINT, 'default' => 0, 'forced' => true],
			'featured_count' => ['type' => self::UINT, 'default' => 0, 'forced' => true],
			'last_article_date' => ['type' => self::UINT, 'default' => 0],
			'last_article_title' => ['type' => self::STR, 'default' => '', 'maxLength' => 150, 'censor' => true],
			'last_article_id' => ['type' => self::UINT, 'default' => 0],
			'thread_node_id' => ['type' => self::UINT, 'default' => 0],
			'thread_prefix_id' => ['type' => self::UINT, 'default' => 0],
			'thread_set_article_tags' => ['type' => self::BOOL, 'default' => false],
			'allow_comments' => ['type' => self::BOOL, 'default' => false],
			'allow_ratings' => ['type' => self::UINT, 'default' => 0],
			'review_voting' =>['type' => self::STR, 'default' => ''],
			'require_review' => ['type' => self::BOOL, 'default' => false],	
			'allow_articles' => ['type' => self::BOOL, 'default' => false],
			'allow_contributors' => ['type' => self::BOOL, 'default' => false],
			'style_id' => ['type' => self::UINT, 'default' => 0],
			'prefix_cache' => ['type' => self::JSON_ARRAY, 'default' => []],
			'default_prefix_id' => ['type' => self::UINT, 'default' => 0],
			'require_prefix' => ['type' => self::BOOL, 'default' => false],
			'field_cache' => ['type' => self::JSON_ARRAY, 'default' => []],
			'review_field_cache' => ['type' => self::JSON_ARRAY, 'default' => []],
			'allow_anon_reviews' => ['type' => self::BOOL, 'default' => false],
			'allow_author_rating' => ['type' => self::BOOL, 'default' => false],
			'allow_pros_cons' => ['type' => self::BOOL, 'default' => false],
			'min_tags' => ['type' => self::UINT, 'forced' => true, 'default' => 0, 'max' => 100],
			'default_tags' =>['type' => self::STR, 'default' => ''],
			'allow_location' => ['type' => self::BOOL, 'default' => false],
			'allow_poll' => ['type' => self::BOOL, 'default' => false],
			'allow_original_source' => ['type' => self::BOOL, 'default' => false],
			'require_original_source' => ['type' => self::BOOL, 'default' => false],
			'require_article_image' => ['type' => self::BOOL, 'default' => false],
			'layout_type' =>['type' => self::STR, 'default' => ''],
			'article_list_order' =>['type' => self::STR, 'default' => ''],
			'expand_category_nav' => ['type' => self::BOOL, 'default' => false],
		];
		$structure->getters = [
			'prefixes' => true,
			'draft_article' => true
		];
		$structure->relations = [
			'ThreadForum' => [
				'entity' => 'XF:Forum',
				'type' => self::TO_ONE,
				'conditions' => [
					['node_id', '=', '$thread_node_id']
				],
				'primary' => true,
				'with' => 'Node'
			],
			'Watch' => [
				'entity' => 'XenAddons\AMS:CategoryWatch',
				'type' => self::TO_MANY,
				'conditions' => 'category_id',
				'key' => 'user_id'
			],
			'DraftArticles' => [
				'entity'     => 'XF:Draft',
				'type'       => self::TO_MANY,
				'conditions' => [
					['draft_key', '=', 'xa-ams-category-', '$category_id']
				],
				'key' => 'user_id'
			]
		];
		$structure->options = [
			'delete_articles' => true
		];

		static::addCategoryTreeStructureElements($structure);

		return $structure;
	}
}