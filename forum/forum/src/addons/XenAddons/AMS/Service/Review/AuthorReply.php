<?php

namespace XenAddons\AMS\Service\Review;

use XenAddons\AMS\Entity\ArticleRating;

class AuthorReply extends \XF\Service\AbstractService
{
	/**
	 * @var ArticleRating
	 */
	protected $rating;

	protected $sendAlert = true;

	public function __construct(\XF\App $app, ArticleRating $rating)
	{
		parent::__construct($app);
		$this->rating = $rating;
	}

	public function getRating()
	{
		return $this->rating;
	}

	public function reply($message, &$error = null)
	{
		if (!$message)
		{
			$error = \XF::phrase('please_enter_valid_message');
			return false;
		}

		$hasExistingResponse = ($this->rating->author_response ? true : false);

		$visitor = \XF::visitor();
		$this->rating->author_response_contributor_user_id = $visitor->user_id;
		$this->rating->author_response_contributor_username = $visitor->username;
		
		$this->rating->author_response = $message;
		$this->rating->save();

		if (!$hasExistingResponse && $this->sendAlert)
		{
			$this->repository('XenAddons\AMS:ArticleRating')->sendAuthorReplyAlert($this->rating);
		}

		return true;
	}
}