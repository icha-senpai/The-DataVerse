<?php

namespace XenAddons\AMS\Service\Review;

use XenAddons\AMS\Entity\ArticleRating;

class Edit extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\ArticleRating
	 */
	protected $rating;
	
	/**
	 * @var \XenAddons\AMS\Service\Review\Preparer
	 */
	protected $reviewPreparer;
	
	protected $oldMessage;

	protected $reviewRequired = false;
	protected $reviewMinLength = 0;

	protected $logDelay;
	protected $logEdit = true;
	protected $logHistory = true;
	
	protected $alert = false;
	protected $alertReason = '';

	public function __construct(\XF\App $app, ArticleRating $rating)
	{
		parent::__construct($app);

		$this->rating = $this->setUpRating($rating);

		$this->reviewRequired = $rating->Article->Category->require_review;
		$this->reviewMinLength = $this->app->options()->xaAmsMinimumReviewLength;
	}

	protected function setUpRating(ArticleRating $rating)
	{
		$this->rating = $rating;
		
		$this->reviewPreparer = $this->service('XenAddons\AMS:Review\Preparer', $this->rating);		
		
		return $rating;
	}

	public function getRating()
	{
		return $this->rating;
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
		$rating = $this->rating;
	
		$rating->edit_count++;
	
		$options = $this->app->options();
		if ($options->editLogDisplay['enabled'] && $this->logEdit)
		{
			$delay = is_null($this->logDelay) ? $options->editLogDisplay['delay'] * 60 : $this->logDelay;
			if ($rating->rating_date + $delay <= \XF::$time)
			{
				$rating->last_edit_date = \XF::$time;
				$rating->last_edit_user_id = \XF::visitor()->user_id;
			}
		}
	
		if ($options->editHistory['enabled'] && $this->logHistory)
		{
			$this->oldMessage = $rating->message;
		}
	}
	
	public function setRating($rating, $pros = '', $cons = '')
	{
		$this->rating->rating = $rating;
		$this->rating->pros = $pros;
		$this->rating->cons = $cons;
	}
	
	public function setMessage($message, $format = true)
	{
		if (!$this->rating->isChanged('message'))
		{
			$this->setupEditHistory();
		}
		return $this->reviewPreparer->setMessage($message, $format);
	}
	
	public function setAttachmentHash($hash)
	{
		$this->reviewPreparer->setAttachmentHash($hash);
	}	

	public function setIsAnonymous($value = true)
	{
		$this->rating->is_anonymous = (bool)$value;
	}

	public function setReviewRequirements($reviewRequired = null, $minLength = null)
	{
		if ($reviewRequired !== null)
		{
			$this->reviewRequired = (bool)$reviewRequired;
		}
		if ($minLength !== null)
		{
			$minLength = max(0, intval($minLength));
			$this->reviewMinLength = $minLength;
		}
	}
	
	public function setCustomFields(array $customFields)
	{
		$rating = $this->rating;
	
		$editMode = $rating->getFieldEditMode();
	
		/** @var \XF\CustomField\Set $fieldSet */
		$fieldSet = $rating->custom_fields;
		$fieldDefinition = $fieldSet->getDefinitionSet()
			->filterEditable($fieldSet, $editMode)
			->filterOnly($rating->Article->Category->review_field_cache);
	
		$customFieldsShown = array_keys($fieldDefinition->getFieldDefinitions());
	
		if ($customFieldsShown)
		{
			$fieldSet->bulkSet($customFields, $customFieldsShown, $editMode);
		}
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
		$rating = $this->rating;

		if (
			!\XF::visitor()->isSpamCheckRequired()
			|| !strlen($this->rating->message)
			|| $this->rating->getErrors()
		)
		{
			return;
		}

		/** @var \XF\Entity\User $user */
		$user = $rating->User;

		$message = $rating->message;

		$checker = $this->app->spam()->contentChecker();
		$checker->check($user, $message, [
			'permalink' => $this->app->router('public')->buildLink('canonical:ams', $rating->Article),
			'content_type' => 'ams_rating'
		]);

		$decision = $checker->getFinalDecision();
		switch ($decision)
		{
			case 'moderated':
				$rating->rating_state = 'moderated';
				break;
				
			case 'denied':
				$checker->logSpamTrigger('ams_rating', $rating->rating_id);
				$rating->error(\XF::phrase('your_content_cannot_be_submitted_try_later'));
				break;
		}
	}

	protected function _validate()
	{
		$rating = $this->rating;

		$rating->preSave();
		$errors = $rating->getErrors();

		if ($this->reviewRequired && !$rating->is_review)
		{
			$errors['message'] = \XF::phrase('xa_ams_please_provide_review_with_your_rating');
		}

		if ($rating->is_review && utf8_strlen($rating->message) < $this->reviewMinLength)
		{
			$errors['message'] = \XF::phrase(
				'xa_ams_your_review_must_be_at_least_x_characters',
				['min' => $this->reviewMinLength]
			);
		}

		if (!$rating->rating)
		{
			$errors['rating'] = \XF::phrase('xa_ams_please_select_star_rating');
		}

		return $errors;
	}

	protected function _save()
	{
		$rating = $this->rating;
		$visitor = \XF::visitor();
		
		$db = $this->db();
		$db->beginTransaction();
		
		$rating->save(true, false);
		
		$this->reviewPreparer->afterUpdate();

		if ($this->oldMessage)
		{
			/** @var \XF\Repository\EditHistory $repo */
			$repo = $this->repository('XF:EditHistory');
			$repo->insertEditHistory('ams_rating', $rating, $visitor, $this->oldMessage, $this->app->request()->getIp());
		}
		
		if ($rating->rating_state == 'visible' && $this->alert && $rating->user_id != $visitor->user_id)
		{
			/** @var \XenAddons\AMS\Repository\ArticleRating $ratingRepo */
			$ratingRepo = $this->repository('XenAddons\AMS:ArticleRating');
			$ratingRepo->sendModeratorActionAlert($rating, 'edit', $this->alertReason);
		}
		
		$db->commit();

		return $rating;
	}
}