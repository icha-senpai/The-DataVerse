<?php

namespace XenAddons\AMS\Service\Review;

use XenAddons\AMS\Entity\ArticleRating;

class AuthorReplyDelete extends \XF\Service\AbstractService
{
	/**
	 * @var ArticleRating
	 */
	protected $rating;

	public function __construct(\XF\App $app, ArticleRating $rating)
	{
		parent::__construct($app);
		$this->rating = $rating;
	}

	public function getRating()
	{
		return $this->rating;
	}

	public function delete()
	{
		if ($this->rating->author_response === '')
		{
			return false;
		}

		$this->rating->author_response = '';
		$this->rating->save();

		return true;
	}
}