<?php

namespace XenAddons\AMS\Spam\Cleaner;

use XF\Spam\Cleaner\AbstractHandler;

class Article extends AbstractHandler
{
	public function canCleanUp(array $options = [])
	{
		return !empty($options['action_threads']);
	}

	public function cleanUp(array &$log, &$error = null)
	{
		$app = \XF::app();

		$articleItemsFinder = $app->finder('XenAddons\AMS:ArticleItem');
		$articleItems = $articleItemsFinder
			->where('user_id', $this->user->user_id)
			->fetch();

		if ($articleItems->count())
		{
			$articleIds = $articleItems->pluckNamed('article_id');
			$submitter = $app->container('spam.contentSubmitter');
			$submitter->submitSpam('ams_article', $articleIds);

			$deleteType = $app->options()->spamMessageAction == 'delete' ? 'hard' : 'soft';

			$log['ams_article'] = [
				'deleteType' => $deleteType,
				'articleIds' => []
			];

			foreach ($articleItems AS $articleId => $articleItem)
			{
				$log['ams_article']['articleIds'][] = $articleId;

				/** @var \XenAddons\AMS\Entity\ArticleItem $articleItem */
				$articleItem->setOption('log_moderator', false);
				if ($deleteType == 'soft')
				{
					$articleItem->article_state = 'deleted';
					$articleItem->save();
				}
				else
				{
					$articleItem->delete();
				}
			}
		}

		return true;
	}

	public function restore(array $log, &$error = null)
	{
		$articleItemsFinder = \XF::app()->finder('XenAddons\AMS:ArticleItem');

		if ($log['deleteType'] == 'soft')
		{
			$articleItems = $articleItemsFinder->where('article_id', $log['articleIds'])->fetch();
			foreach ($articleItems AS $articleItem)
			{
				/** @var \XenAddons\AMS\Entity\ArticleItem $articleItem */
				$articleItem->setOption('log_moderator', false);
				$articleItem->article_state = 'visible';
				$articleItem->save();
			}
		}

		return true;
	}
}