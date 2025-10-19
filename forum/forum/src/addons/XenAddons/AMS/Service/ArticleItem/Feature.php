<?php

namespace XenAddons\AMS\Service\ArticleItem;

use XenAddons\AMS\Entity\ArticleItem;

class Feature extends \XF\Service\AbstractService
{
	/**
	 * @var \XenAddons\AMS\Entity\ArticleItem
	 */
	protected $article;

	public function __construct(\XF\App $app, ArticleItem $article)
	{
		parent::__construct($app);
		$this->article = $article;
	}

	public function getArticle()
	{
		return $this->article;
	}

	public function feature()
	{
		$db = $this->db();
		$db->beginTransaction();

		$affected = $db->insert('xf_xa_ams_article_feature', [
			'article_id' => $this->article->article_id,
			'feature_date' => \XF::$time
		], false, 'feature_date = VALUES(feature_date)');

		if ($affected == 1)
		{
			// insert
			$this->onNewFeature();
		}

		$db->commit();
	}

	protected function onNewFeature()
	{
		$article = $this->article;
		$category = $this->article->Category;
		
		$article->last_feature_date = \XF::$time;
		$article->save();
		
		if ($article->isVisible())
		{
			if ($category)
			{
				$category->featured_count++;
				$category->save();
			}
		}

		$this->app->logger()->logModeratorAction('ams_article', $article, 'feature');
	}

	public function unfeature()
	{
		$db = $this->db();
		$db->beginTransaction();

		$affected = $db->delete('xf_xa_ams_article_feature', 'article_id = ?', $this->article->article_id);
		if ($affected)
		{
			$this->onUnfeature();
		}

		$db->commit();
	}

	protected function onUnfeature()
	{
		if ($this->article->isVisible())
		{
			$category = $this->article->Category;
			if ($category)
			{
				$category->featured_count--;
				$category->save();
			}
		}

		$this->app->logger()->logModeratorAction('ams_article', $this->article, 'unfeature');
	}
}