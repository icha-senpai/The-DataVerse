<?php

namespace XenAddons\AMS\Finder;

use XF\Mvc\Entity\Finder;

class ArticleRating extends Finder
{
	public function inArticle(\XenAddons\AMS\Entity\ArticleItem $article, array $limits = [])
	{
		$limits = array_replace([
			'visibility' => true
		], $limits);

		$this->where('article_id', $article->article_id);

		if ($limits['visibility'])
		{
			$this->applyVisibilityChecksInArticle($article);
		}

		return $this;
	}

	public function applyVisibilityChecksInArticle(\XenAddons\AMS\Entity\ArticleItem $article, $allowOwnPending = true)
	{
		$conditions = [];
		$viewableStates = ['visible'];

		if ($article->canViewDeletedReviews())
		{
			$viewableStates[] = 'deleted';

			$this->with('DeletionLog');
		}
		
		$visitor = \XF::visitor();
		if ($article->canViewModeratedReviews())
		{
			$viewableStates[] = 'moderated';
		}
		else if ($visitor->user_id && $allowOwnPending)
		{
			$conditions[] = [
				'rating_state' => 'moderated',
				'user_id' => $visitor->user_id
			];
		}

		$conditions[] = ['rating_state', $viewableStates];

		$this->whereOr($conditions);

		return $this;
	}

	/**
	 * @deprecated Use with('full') instead
	 *
	 * @return $this
	 */
	public function forFullView()
	{
		$this->with('full');
	
		return $this;
	}
}