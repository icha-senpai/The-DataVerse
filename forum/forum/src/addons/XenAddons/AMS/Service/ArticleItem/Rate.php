<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class Rate extends \XF\Service\AbstractService
{
	use \XF\Service\ValidateAndSavableTrait;

	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

	/**
	 * @var \XenAddons\AMS\Entity\ArticleRating
	 */
	protected $rating;
	
	/**
	 * @var \XenAddons\AMS\Service\Review\Preparer
	 */
	protected $reviewPreparer;

	protected $reviewRequired = false;
	protected $reviewMinLength = 0;

	protected $sendAlert = true;
	
	protected $isPreRegAction = false;
	
	public function __construct(\XF\App $app, ArticleItem $article)
	{
		parent::__construct($app);

		$this->article = $article;
		$this->rating = $this->setupRating();

		$this->reviewRequired = $article->Category->require_review;
		$this->reviewMinLength = $this->app->options()->xaAmsMinimumReviewLength;
	}

	protected function setupRating()
	{
		$article = $this->article;
		
		$rating = $this->em()->create('XenAddons\AMS:ArticleRating');
		$rating->article_id = $article->article_id;
		$rating->user_id = \XF::visitor()->user_id;
		$rating->username = \XF::visitor()->username;
		
		$rating->rating_state = $article->getNewRatingState();
		
		$this->rating = $rating;
		
		$this->reviewPreparer = $this->service('XenAddons\AMS:Review\Preparer', $this->rating);		
		
		return $rating;
	}

	public function getArticle()
	{
		return $this->article;
	}

	public function getRating()
	{
		return $this->rating;
	}

	public function logIp($logIp)
	{
		$this->reviewPreparer->logIp($logIp);
	}	
	
	public function setRating($rating, $pros = '', $cons = '')
	{
		$this->rating->rating = $rating;
		$this->rating->pros = $pros;
		$this->rating->cons = $cons;
	}
	
	public function setMessage($message, $format = true)
	{
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
	
	public function setIsPreRegAction(bool $isPreRegAction)
	{
		$this->isPreRegAction = $isPreRegAction;
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
	
		/** @var \XF\CustomField\Set $fieldSet */
		$fieldSet = $rating->custom_fields;
		$fieldDefinition = $fieldSet->getDefinitionSet()
			->filterEditable($fieldSet, 'user')
			->filterOnly($rating->Article->Category->review_field_cache);
	
		$customFieldsShown = array_keys($fieldDefinition->getFieldDefinitions());
	
		if ($customFieldsShown)
		{
			$fieldSet->bulkSet($customFields, $customFieldsShown);
		}
	}

	public function checkForSpam()
	{
		$rating = $this->rating;

		if (
			!\XF::visitor()->isSpamCheckRequired()
			|| $rating->rating_state != 'visible'
			|| !strlen($this->rating->message)
			|| $this->rating->getErrors()
		)
		{
			return;
		}

		/** @var \XF\Entity\User $user */
		$user = $rating->User ?: $this->repository('XF:User')->getGuestUser($rating->username);

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
		$article = $this->article;
		$rating = $this->rating;
		
		if (!$rating->user_id && !$this->isPreRegAction)
		{
			/** @var \XF\Validator\Username $validator */
			$validator = $this->app->validator('Username');
			$rating->username = $validator->coerceValue($rating->username);
			if (!$validator->isValid($rating->username, $error))
			{
				return [$validator->getPrintableErrorValue($error)];
			}
		}
		else if ($this->isPreRegAction && !$rating->username)
		{
			// need to force a value here to avoid a presave error
			$rating->username = 'preRegAction-' . \XF::$time;
		}

		$rating->preSave();
		$errors = $rating->getErrors();

		if ($this->isPreRegAction)
		{
			// ignore this as we'll resolve it later
			unset($errors['user_id']);
		}
		
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
		if ($this->isPreRegAction)
		{
			throw new \LogicException("Pre-reg action ratings cannot be saved");
		}
		
		$article = $this->article;
		$rating = $this->rating;
		
		// check for the existance of previous ratings
		$existing = $this->article->Ratings[$rating->user_id];
		if ($existing)
		{
			// since there previous ratings, we need to fetch and delete all previous ratings only (ratings that are not reviews) posted by the viewing user on this article!
		
			$ratings = $this->repository('XenAddons\AMS:ArticleRating')->getRatingsForArticleByUser($rating->article_id, $rating->user_id);
			foreach ($ratings AS $existingRating)
			{
				$existingRating->delete();
			}
		
			// run the rebuildCounters on the article after performing this to make sure the rating/review caches are correct!
			$article->rebuildCounters();
			$article->save();
		}

		$rating->save(true, false);
		
		$this->reviewPreparer->afterInsert();
		
		return $rating;
	}
	
	public function sendNotifications()
	{
		if ($this->rating->isVisible())
		{
			/** @var \XenAddons\AMS\Service\Review\Notifier $notifier */
			$notifier = $this->service('XenAddons\AMS:Review\Notifier', $this->rating);
			$notifier->setMentionedUserIds($this->reviewPreparer->getMentionedUserIds());
			$notifier->notifyAndEnqueue(3);
		}
	}
}