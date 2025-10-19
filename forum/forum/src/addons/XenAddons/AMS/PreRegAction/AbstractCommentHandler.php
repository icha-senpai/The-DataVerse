<?php

namespace XenAddons\AMS\PreRegAction;

use XF\Entity\PreRegAction;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;

abstract class AbstractCommentHandler extends \XF\PreRegAction\AbstractHandler
{
	public function getDefaultActionData(): array
	{
		return [
			'message' => ''
		];
	}

	protected function canCompleteAction(PreRegAction $action, Entity $containerContent, User $newUser): bool
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $containerContent */
		return $containerContent->canAddComment();
	}

	protected function executeAction(PreRegAction $action, Entity $containerContent, User $newUser)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $containerContent */

		$creator = $this->setupCommentCreate($action, $containerContent);
		$creator->checkForSpam();

		if (!$creator->validate())
		{
			return null;
		}

		$comment = $creator->save();

		\XF::repository('XenAddons\AMS:ArticleWatch')->autoWatchAmsArticleItem($containerContent, $newUser, false);

		$creator->sendNotifications();

		return $comment;
	}

	protected function setupCommentCreate(
		PreRegAction $action,
		\XF\Mvc\Entity\Entity $containerContent
	): \XenAddons\AMS\Service\Comment\Creator
	{
		$creator = \XF::app()->service('XenAddons\AMS:Comment\Creator', $containerContent);
		$creator->setMessage($action->action_data['message']);
		$creator->logIp($action->ip_address);

		return $creator;
	}

	protected function sendSuccessAlert(
		PreRegAction $action,
		Entity $containerContent,
		User $newUser,
		Entity $executeContent
	)
	{
		if (!($executeContent instanceof \XenAddons\AMS\Entity\Comment))
		{
			return;
		}

		/** @var \XenAddons\AMS\Entity\Comment $comment */
		$comment = $executeContent;

		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = \XF::repository('XF:UserAlert');

		$alertRepo->alertFromUser(
			$newUser, null,
			'ams_comment', $comment->comment_id,
			'pre_reg',
			[],
			['autoRead' => false]
		);
	}

	protected function getStructuredContentData(PreRegAction $preRegAction, Entity $containerContent): array
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $containerContent */

		$phrase = 'xa_ams_comment_on_article_x';

		return [
			'title' => \XF::phrase($phrase, [
				'title' => $containerContent->title
			]),
			'title_link' => $containerContent->getContentUrl(),
			'bb_code' => $preRegAction->action_data['message']
		];
	}
}