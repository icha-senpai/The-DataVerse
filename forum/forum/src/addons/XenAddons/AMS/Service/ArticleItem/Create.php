<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\Category;

class Create extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\Category
	 */
	protected $category;

	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

	/**
	 * @var \XenAddons\AMS\Service\ArticleItem\Preparer
	 */
	protected $articlePreparer;

	/**
	 * @var \XF\Service\Tag\Changer
	 */
	protected $tagChanger;
	
	protected $associatedThreadTags;

	/** 
	 * @var  \XF\Service\Poll\Creator|null 
	 */
	protected $pollCreator;
	
	/**
	 * @var \XF\Service\Thread\Creator|null
	 */
	protected $threadCreator;

	protected $performValidations = true;

	public function __construct(\XF\App $app, Category $category)
	{
		parent::__construct($app);
		$this->category = $category;
		$this->setupDefaults();
	}

	protected function setupDefaults()
	{
		$article = $this->category->getNewArticle();

		$this->article = $article;

		$this->articlePreparer = $this->service('XenAddons\AMS:ArticleItem\Preparer', $this->article);

		$this->tagChanger = $this->service('XF:Tag\Changer', 'ams_article', $this->category);

		$visitor = \XF::visitor();
		$this->article->user_id = $visitor->user_id;
		$this->article->username = $visitor->username;
		
		$originalSourceDate = [
			'os_article_author' => '',
			'os_article_title' => '',
			'os_article_date' => 0,
			'os_source_name' => '',
			'os_source_url' => '',
		];

		$this->article->original_source = $originalSourceDate;
		
		$this->article->article_state = $this->category->getNewArticleState();
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function getArticle()
	{
		return $this->article;
	}

	public function setPerformValidations($perform)
	{
		$this->performValidations = (bool)$perform;
	}

	public function getPerformValidations()
	{
		return $this->performValidations;
	}

	public function setIsAutomated()
	{
		$this->logIp(false);
		$this->setPerformValidations(false);
	}
	
	public function setTitle($title)
	{
		$this->article->set('title', $title,
			['forceConstraint' => $this->performValidations ? false : true]
		);
	}

	public function setContent($title, $article, $format = true)
	{
		$this->setTitle($title);

		return $this->articlePreparer->setMessage($article, $format, $this->performValidations);
	}

	public function setPrefix($prefixId)
	{
		$this->article->prefix_id = $prefixId;
	}
	
	public function setArticleState($articleState)
	{
		$this->article->article_state = $articleState;
	}
	
	public function setAuthorRating($authorRating)
	{
		$this->article->author_rating = $authorRating;
	}	

	public function setTags($tags)
	{
		if ($this->tagChanger->canEdit())
		{
			$this->tagChanger->setEditableTags($tags);
		}
	}
	
	public function setAssociatedThreadTags($tags)
	{
		$this->associatedThreadTags = $tags;
	}

	public function setArticleAttachmentHash($hash)
	{
		$this->articlePreparer->setAttachmentHash($hash);
	}

	public function setCustomFields(array $customFields)
	{
		$article = $this->article;

		/** @var \XF\CustomField\Set $fieldSet */
		$fieldSet = $article->custom_fields;
		$fieldDefinition = $fieldSet->getDefinitionSet()
			->filterEditable($fieldSet, 'user')
			->filterOnly($this->category->field_cache);

		$customFieldsShown = array_keys($fieldDefinition->getFieldDefinitions());

		if ($customFieldsShown)
		{
			$fieldSet->bulkSet($customFields, $customFieldsShown);
		}
	}

	public function setPollCreator(\XF\Service\Poll\Creator $creator = null)
	{
		$this->pollCreator = $creator;
	}
	
	public function getPollCreator()
	{
		return $this->pollCreator;
	}
	
	public function logIp($logIp)
	{
		$this->articlePreparer->logIp($logIp);
	}

	public function checkForSpam()
	{
		if ($this->article->article_state == 'visible' && \XF::visitor()->isSpamCheckRequired())
		{
			$this->articlePreparer->checkForSpam();
		}
	}

	protected function finalSetup()
	{
	}

	protected function _validate()
	{
		$this->finalSetup();

		$article = $this->article;

		if (!$article->user_id)
		{
			/** @var \XF\Validator\Username $validator */
			$validator = $this->app->validator('Username');
			$article->username = $validator->coerceValue($article->username);

			if ($this->performValidations && !$validator->isValid($article->username, $error))
			{
				return [
					$validator->getPrintableErrorValue($error)
				];
			}
		}

		$article->preSave();
		$errors = $article->getErrors();

		if ($this->performValidations)
		{
			if (!$this->articlePreparer->validateFiles($coverImageError))
			{
				$errors[] = $coverImageError;
			}
			
			if (!$article->prefix_id
				&& $this->category->require_prefix
				&& $this->category->getUsablePrefixes()
			)
			{
				$errors[] = \XF::phraseDeferred('please_select_a_prefix');
			}

			if ($this->tagChanger->canEdit())
			{
				$tagErrors = $this->tagChanger->getErrors();
				if ($tagErrors)
				{
					$errors = array_merge($errors, $tagErrors);
				}
			}
			
			if ($this->category->require_original_source)
			{
				if ($article->original_source['os_article_author'] == '')
				{
					$errors[] = \XF::phraseDeferred('xa_ams_original_source_article_author_required');
				}
				
				if ($article->original_source['os_article_title'] == '')
				{
					$errors[] = \XF::phraseDeferred('xa_ams_original_source_article_title_required');
				}
				
				if ($article->original_source['os_article_date'] == '' || $article->original_source['os_article_date'] == 0)
				{
					$errors[] = \XF::phraseDeferred('xa_ams_original_source_article_date_required');
				}
				
				if ($article->original_source['os_source_name'] == '')
				{
					$errors[] = \XF::phraseDeferred('xa_ams_original_source_name_required');
				}
				
				if ($article->original_source['os_source_url'] == '')
				{
					$errors[] = \XF::phraseDeferred('xa_ams_original_source_url_required');
				}
			}
			
			if (isset($article->original_source['os_source_url']) && $article->original_source['os_source_url'])
			{
				$phraseKey = null;
			
				/** @var \XF\Validator\Url $urlValidator */
				$urlValidator = \XF::app()->validator('Url');
				$value = $urlValidator->coerceValue($article->original_source['os_source_url']);
			
			
				if (!$urlValidator->isValid($value, $phraseKey))
				{
					$errors[] = \XF::phraseDeferred('please_enter_valid_url');
				}
			}
		}
		
		if ($this->pollCreator)
		{
			if (!$this->pollCreator->validate($pollErrors))
			{
				$errors = array_merge($errors, $pollErrors);
			}
		}

		return $errors;
	}

	protected function _save()
	{
		$category = $this->category;
		$article = $this->article;

		$db = $this->db();
		$db->beginTransaction();

		$article->save(true, false);

		$this->articlePreparer->afterInsert();

		if ($this->tagChanger->canEdit())
		{
			$this->tagChanger
				->setContentId($article->article_id, true)
				->save($this->performValidations);
		}
		
		if ($this->pollCreator)
		{
			$this->pollCreator->save();
		}
		
		if ($category->thread_node_id && $category->ThreadForum)
		{
			$creator = $this->setupArticleThreadCreation($category->ThreadForum);
			if ($creator && $creator->validate())
			{
				$thread = $creator->save();
				$article->fastUpdate('discussion_thread_id', $thread->thread_id);
				$this->threadCreator = $creator;

				$this->afterArticleThreadCreated($thread);
			}
		}

		$db->commit();

		return $article;
	}

	protected function setupArticleThreadCreation(\XF\Entity\Forum $forum)
	{
		/** @var \XF\Service\Thread\Creator $creator */
		$creator = $this->service('XF:Thread\Creator', $forum);
		$creator->setIsAutomated();

		$creator->setContent($this->article->getExpectedThreadTitle(), $this->getThreadMessage(), false);
		$creator->setPrefix($this->category->thread_prefix_id);
		
		if ($this->category->thread_set_article_tags) // fail safe double check
		{
			$creator->setTags($this->associatedThreadTags);
		}
		
		$creator->setDiscussionTypeAndDataRaw('ams_article');
		
		$thread = $creator->getThread();
		$thread->discussion_state = $this->article->article_state == 'draft' ? 'moderated' : $this->article->article_state;

		return $creator;
	}

	protected function getThreadMessage()
	{
		$article = $this->article;
		$category = $article->Category;
		
		$snippet = $this->app->bbCode()->render(
			$this->app->stringFormatter()->wholeWordTrim($article->message, 500),
			'bbCodeClean',
			'post',
			null
		);
		
		$phrase = \XF::phrase('xa_ams_article_thread_create', [
			'title' => $article->title_,
			'term' => $category->content_term ?: \XF::phrase('xa_ams_article'),
			'term_lower' => $category->content_term ? strtolower($category->content_term) : strtolower(\XF::phrase('xa_ams_article')),
			'username' => $article->User ? $article->User->username : $article->username,
			'snippet' => $snippet,
			'article_link' => $this->app->router('public')->buildLink('canonical:ams', $this->article)
		]);

		return $phrase->render('raw');
	}

	protected function afterArticleThreadCreated(\XF\Entity\Thread $thread)
	{
		$this->repository('XF:Thread')->markThreadReadByVisitor($thread);
		$this->repository('XF:ThreadWatch')->autoWatchThread($thread, \XF::visitor(), true);
	}

	public function sendNotifications()
	{
		if ($this->article->isVisible())
		{
			/** @var \XenAddons\AMS\Service\ArticleItem\Notify $notifier */
			$notifier = $this->service('XenAddons\AMS:ArticleItem\Notify', $this->article, 'article');
			$notifier->setMentionedUserIds($this->articlePreparer->getMentionedUserIds());
			$notifier->notifyAndEnqueue(3);
		}

		if ($this->threadCreator)
		{
			$this->threadCreator->sendNotifications();
		}
	}
}