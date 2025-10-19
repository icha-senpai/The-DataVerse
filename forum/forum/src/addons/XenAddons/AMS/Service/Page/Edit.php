<?php

namespace XenAddons\AMS\Service\Page;

use XenAddons\AMS\Entity\ArticlePage;

class Edit extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\ArticlePage
	 */
	protected $page;
	
	/**
	 * @var \XenAddons\AMS\Service\Page\Preparer
	 */
	protected $pagePreparer;
	
	protected $oldMessage;
	
	protected $logDelay;
	protected $logEdit = true;
	protected $logHistory = true;

	public function __construct(\XF\App $app, ArticlePage $page)
	{
		parent::__construct($app);

		$this->page = $this->setUpPage($page);
	}

	protected function setUpPage(ArticlePage $page)
	{
		$this->page = $page;
		
		$this->pagePreparer = $this->service('XenAddons\AMS:Page\Preparer', $this->page);		
		
		return $page;
	}

	public function getPage()
	{
		return $this->page;
	}
	
	public function logDelay($logDelay)
	{
		$this->logDelay = $logDelay;
	}
	
	public function logEdit($logEdit)
	{
		$this->logEdit = $logEdit;
	}
	
	public function logHistory($logHistory)
	{
		$this->logHistory = $logHistory;
	}
	
	protected function setupEditHistory()
	{
		$page = $this->page;
	
		$page->edit_count++;
	
		$options = $this->app->options();
		if ($options->editLogDisplay['enabled'] && $this->logEdit)
		{
			$delay = is_null($this->logDelay) ? $options->editLogDisplay['delay'] * 60 : $this->logDelay;
			if ($page->create_date + $delay <= \XF::$time)
			{
				$page->last_edit_date = \XF::$time;
				$page->last_edit_user_id = \XF::visitor()->user_id;
			}
		}
	
		if ($options->editHistory['enabled'] && $this->logHistory)
		{
			$this->oldMessage = $page->message;
		}
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
		if (!$this->page->isChanged('message'))
		{
			$this->setupEditHistory();
		}
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
		
		$db = $this->db();
		$db->beginTransaction();
		
		$page->save(true, false);
		
		$this->pagePreparer->afterUpdate();

		if ($this->oldMessage)
		{
			/** @var \XF\Repository\EditHistory $repo */
			$repo = $this->repository('XF:EditHistory');
			$repo->insertEditHistory('ams_page', $page, $visitor, $this->oldMessage, $this->app->request()->getIp());
		}
		
		$db->commit();
		
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