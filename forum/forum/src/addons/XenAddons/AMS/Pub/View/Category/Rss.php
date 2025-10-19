<?php

namespace XenAddons\AMS\Pub\View\Category;

class Rss extends \XF\Mvc\View
{
	public function renderRss()
	{
		$app = \XF::app();
		$router = $app->router('public');
		$options = $app->options();
		$category = $this->params['category'];

		$indexUrl = $router->buildLink('canonical:index');
		if ($category)
		{
			$feedLink = $router->buildLink('canonical:ams/categories/index.rss', $category);
		}
		else
		{
			$feedLink = $router->buildLink('canonical:ams/index.rss', '-');
		}

		if ($category)
		{
			$title = $category->title;
			$description = $category->description;
		}
		else
		{
			$title = 'Articles Feed';
			$description = 'Articles feed from ' . $options->boardTitle . ' - ' . $options->boardDescription;
		}

		$title = $title ?: $indexUrl;
		$description = $description ?: $title; // required in RSS 2.0 spec

		$feed = new \Laminas\Feed\Writer\Feed();

		$feed->setEncoding('utf-8')
			->setTitle($title)
			->setDescription($description)
			->setLink($indexUrl)
			->setFeedLink($feedLink, 'rss')
			->setDateModified(\XF::$time)
			->setLastBuildDate(\XF::$time)
			->setGenerator($options->boardTitle);

		$parser = $app->bbCode()->parser();
		$rules = $app->bbCode()->rules('post:rss');

		$bbCodeCleaner = $app->bbCode()->renderer('bbCodeClean');
		$bbCodeRenderer = $app->bbCode()->renderer('html');

		$formatter = $app->stringFormatter();
		$maxLength = $options->discussionRssContentLength;

		/** @var \XenAddons\AMS\Entity\ArticleItem $article */
		foreach ($this->params['articles'] AS $article)
		{
			$articleCategory = $article->Category;
			$entry = $feed->createEntry();

			$title = (empty($article->title) ? \XF::phrase('title:') . ' ' . $article->title : $article->title);
			$entry->setTitle($title)
				->setLink($router->buildLink('canonical:ams', $article))
				->setDateCreated($article->publish_date)
				->setDateModified($article->last_update);

			if ($articleCategory && !$category)
			{
				$entry->addCategory([
					'term' => $articleCategory->title,
					'scheme' => $router->buildLink('canonical:category', $articleCategory)
				]);
			}

			if ($maxLength && $article && $article->message)
			{
				$snippet = $bbCodeCleaner->render($formatter->wholeWordTrim($article->message, $maxLength), $parser, $rules);

				if ($snippet != $article->message)
				{
					$snippet .= "\n\n[URL='" . $router->buildLink('canonical:ams', $article) . "']$article->title[/URL]";
				}

				$renderOptions = $article->getBbCodeRenderOptions('post:rss', 'html');
				$renderOptions['noProxy'] = true;

				$content = trim($bbCodeRenderer->render($snippet, $parser, $rules, $renderOptions));
				if (strlen($content))
				{
					$entry->setContent($content);
				}
			}

			$entry->addAuthor([
				'name' => $article->username,
				'email' => 'invalid@example.com',
				'uri' => $router->buildLink('canonical:members', $article)
			]);
			if ($article->comment_count)
			{
				$entry->setCommentCount($article->comment_count);
			}

			$feed->addEntry($entry);
		}

		return $feed->export('rss', true);
	}
}