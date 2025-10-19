<?php

namespace XenAddons\AMS\Service\Series;

use XenAddons\AMS\Entity\SeriesItem;

class AddSeriesPart extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\SeriesItem
	 */
	protected $series;
	
	/**
	 * @var \XenAddons\AMS\Entity\ArticlePage
	 */
	protected $seriesPart;
	
	/**
	 * @var \XenAddons\AMS\Service\SeriesPart\Preparer
	 */
	protected $seriePartPreparer;

	public function __construct(\XF\App $app, SeriesItem $series)
	{
		parent::__construct($app);

		$this->series = $series;
		
		$this->seriesPart = $this->setUpSeriesPart();
	}

	protected function setUpSeriesPart()
	{
		$series = $this->series;
		
		$seriesPart = $this->em()->create('XenAddons\AMS:SeriesPart');
		$seriesPart->series_id = $series->series_id;
		$seriesPart->user_id = \XF::visitor()->user_id;
		
		$this->seriesPart = $seriesPart;
		
		$this->seriesPartPreparer = $this->service('XenAddons\AMS:SeriesPart\Preparer', $this->seriesPart);		
		
		return $seriesPart;
	}

	public function getSeries()
	{
		return $this->series;
	}
	
	public function getSeriesPart()
	{
		return $this->seriesPart;
	}
	
	public function setTitle($title)
	{
		$this->seriesPart->title = $title;
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
		$this->seriesPartPreparer->checkForSpam();
	
	}
	
	protected function finalSetup()
	{
	}

	protected function _validate()
	{
		$visitor = \XF::visitor();
		
		$seriesPart = $this->seriesPart;
		$series = $this->seriesPart->Series;
		$article = $this->seriesPart->Article;
		
		$seriesPart->preSave();
		$errors = $seriesPart->getErrors();

		if (!$series)
		{
			$errors['series_404'] = \XF::phrase('xa_ams_requested_series_not_found');
			return $errors;
		}
		
		if (!$article)
		{
			$errors['article_404'] = \XF::phrase('xa_ams_requested_article_not_found');
			return $errors;
		}
		
		if ($article->isInSeries())
		{
			$errors['article_in_series'] = \XF::phrase('xa_ams_requested_article_aleady_associated_with_series');
			return $errors;			
		}
		
		if (
			$visitor->user_id == $seriesPart->Series->user_id
			&& $visitor->user_id == $seriesPart->Article->user_id 
		)
		{
			// do nothing as a series owner can add their own aritlces to a series
		}
		else 
		{
			// check to see if the viewing user has the moderator permission to add any article to any series. 
			if ($seriesPart->Series->canAddArticleToAnySeries())
			{
				// do nothing as Viewing User has permission to add any article to any series
			}
			else 
			{
				// Check if is a community series and if the viewing user is the article author. 
				if (
					$seriesPart->Series->isCommunitySeries()
					&& $seriesPart->Article->user_id == $visitor->user_id
				)
				{
					// do nothing as the Series is a Community Series, the Viewing User is the Article Author and the Viewing User has permission to add their own articles to any community series 
				}
				else 
				{	// If it gets to this point, we need to throw an error as The Viewing User is not the Author of the Article and does not have permission to add any article to any series
					$errors['author_no_match'] = \XF::phrase('xa_ams_you_do_not_have_permission_to_add_this_article_to_this_series');					
				}
			}	
		}

		return $errors;
	}

	protected function _save()
	{
		$seriesPart = $this->seriesPart;
		$visitor = \XF::visitor();
		
		$seriesPart->save(true, false);
		
		$this->seriesPartPreparer->afterUpdate();

		return $seriesPart;
	}
	
	public function sendNotifications()
	{
		if ($this->seriesPart->Article->isVisible())
		{
			/** @var \XenAddons\AMS\Service\SeriesPart\Notifier $notifier */
			$notifier = $this->service('XenAddons\AMS:SeriesPart\Notifier', $this->seriesPart);
			$notifier->notifyAndEnqueue(3);
		}
	}	
}