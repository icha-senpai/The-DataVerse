<?php

namespace XenAddons\AMS\Finder;

use XF\Mvc\Entity\Finder;

class ArticlePage extends Finder
{
	public function inArticle(\XenAddons\AMS\Entity\ArticleItem $article, array $limits = [])
	{
		$limits = array_replace([
			
		], $limits);

		$this->where('article_id', $article->article_id);

		return $this;
	}
	
	public function byUser(\XF\Entity\User $user)
	{
		$this->where('user_id', $user->user_id);
	
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