<?php

namespace XenAddons\AMS\Service\Page;

use XenAddons\AMS\Entity\ArticlePage;

class Deleter extends \XF\Service\AbstractService
{
	/**
	 * @var ArticlePage
	 */
	protected $page;

	protected $user;

	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, ArticlePage $page)
	{
		parent::__construct($app);
		$this->setPage($page);
	}

	public function setPage(ArticlePage $page)
	{
		$this->page = $page;
	}
	
	public function setSendAlert($alert, $reason = null)
	{
		$this->alert = (bool)$alert;
		if ($reason !== null)
		{
			$this->alertReason = $reason;
		}
	}

	public function getPage()
	{
		return $this->page;
	}

	public function setUser(\XF\Entity\User $user = null)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function delete($type, $reason = '')
	{
		$user = $this->user ?: \XF::visitor();
		
		$result = null;
		
		$wasVisible = $this->page->page_state == 'visible';
		
		if ($type == 'soft')
		{
			$result = $this->page->softDelete($reason, $user);
		}
		else
		{
			$result = $this->page->delete();
		}
		
		if ($result && $wasVisible && $this->alert && $this->page->user_id != $user->user_id)
		{
			/** @var \XenAddons\AMS\Repository\ArticlePage $pageRepo */
			$pageRepo = $this->repository('XenAddons\AMS:ArticlePage');
			$pageRepo->sendModeratorActionAlert($this->page, 'delete', $this->alertReason);
		}
		
		return $result;
	}
}