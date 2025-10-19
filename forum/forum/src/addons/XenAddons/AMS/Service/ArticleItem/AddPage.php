<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class AddPage extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;
	
	/**
	 * @var \XenAddons\AMS\Entity\ArticlePage
	 */
	protected $page;
	
	/**
	 * @var \XenAddons\AMS\Service\Page\Preparer
	 */
	protected $pagePreparer;

	public function __construct(\XF\App $app, ArticleItem $article)
	{
		parent::__construct($app);

		$this->article = $article;
		
		$this->page = $this->setUpPage();
	}

	protected function setUpPage()
	{
		$article = $this->article;
		
		$page = $this->em()->create('XenAddons\AMS:ArticlePage');
		$page->article_id = $article->article_id;
		$page->user_id = \XF::visitor()->user_id;
		$page->username = \XF::visitor()->username;
		
		$this->page = $page;
		
		$this->pagePreparer = $this->service('XenAddons\AMS:Page\Preparer', $this->page);		
		
		return $page;
	}

	public function getArticle()
	{
		return $this->article;
	}
	
	public function getPage()
	{
		return $this->page;
	}
	
	public function logIp($logIp)
	{
		$this->pagePreparer->logIp($logIp);
	}
	
	public function setTitle($title)
	{
		$this->page->title = $title;
	}
	
	public function setNavTitle($navTitle)
	{
		$this->page->nav_title = $navTitle;
	}
	
	public function setMessage($message, $format = true)
	{
		return $this->pagePreparer->setMessage($message, $format);
	}
	
	public function setAttachmentHash($hash)
	{
		$this->pagePreparer->setAttachmentHash($hash);
	}	

	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}
	
	public function checkForSpam()
	{
		if ($this->page->page_state == 'visible' && \XF::visitor()->isSpamCheckRequired())
		{
			$this->pagePreparer->checkForSpam();
		}
	}
	
	protected function finalSetup()
	{
	}

	protected function _validate()
	{
		$page = $this->page;

		$page->preSave();
		$errors = $page->getErrors();

		return $errors;
	}

	protected function _save()
	{
		$page = $this->page;
		$visitor = \XF::visitor();
		
		$page->save(true, false);
		
		$this->pagePreparer->afterInsert();

		return $page;
	}
	
	public function sendNotifications()
	{
		if ($this->page->isVisible())
		{
			/** @var \XenAddons\AMS\Service\Page\Notifier $notifier */
			$notifier = $this->service('XenAddons\AMS:Page\Notifier', $this->page);
			$notifier->notifyAndEnqueue(3);
		}
	}	
}