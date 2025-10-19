<?php

namespace XenAddons\AMS\PreRegAction\ArticleItem;

use XF\Entity\PreRegAction;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\PreRegAction\AbstractHandler;

class Rate extends AbstractHandler
{
	public function getContainerContentType(): string
	{
		return 'ams_article';
	}

	public function getDefaultActionData(): array
	{
		return [
			'rating' => 0,
			'pros' => '',
			'cons' => '',
			'message' => '',
			'custom_fields' => [],
			'is_anonymous' => false
		];
	}

	protected function canCompleteAction(PreRegAction $action, Entity $containerContent, User $newUser): bool
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $containerContent */
		return $containerContent->canRate();
	}

	protected function executeAction(PreRegAction $action, Entity $containerContent, User $newUser)
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $containerContent */

		$rater = $this->setupArticleRate($action, $containerContent);
		$rater->checkForSpam();

		if (!$rater->validate())
		{
			return null;
		}

		$rating = $rater->save();

		\XF::repository('XenAddons\AMS:ArticleWatch')->autoWatchAmsArticleItem($containerContent, $newUser, false);

		$rater->sendNotifications();

		return $rating;
	}

	protected function setupArticleRate(
		PreRegAction $action,
		\XenAddons\AMS\Entity\ArticleItem $article
	): \XenAddons\AMS\Service\ArticleItem\Rate
	{
		/** @var \XenAddons\AMS\Service\ArticleItem\Rate $rater */
		$rater = \XF::app()->service('XenAddons\AMS:ArticleItem\Rate', $article);
		
		$rater->setMessage($action->action_data['message']);
		$rater->setRating($action->action_data['rating'], $action->action_data['pros'], $action->action_data['cons']);
		$rater->setCustomFields($action->action_data['custom_fields']);
		
		if ($article->Category->allow_anon_reviews && $action->action_data['is_anonymous'])
		{
			$rater->setIsAnonymous();
		}

		return $rater;
	}

	protected function sendSuccessAlert(
		PreRegAction $action,
		Entity $containerContent,
		User $newUser,
		Entity $executeContent
	)
	{
		if (!($executeContent instanceof \XenAddons\AMS\Entity\ArticleRating))
		{
			return;
		}

		/** @var \XenAddons\AMS\Entity\ArticleRating $rating */
		$rating = $executeContent;

		/** @var \XF\Repository\UserAlert $alertRepo */
		$alertRepo = \XF::repository('XF:UserAlert');

		$alertRepo->alertFromUser(
			$newUser, null,
			'ams_rating', $rating->rating_id,
			'pre_reg'
		);
	}

	protected function getStructuredContentData(PreRegAction $preRegAction, Entity $containerContent): array
	{
		/** @var \XenAddons\AMS\Entity\ArticleItem $containerContent */

		return [
			'title' => \XF::phrase('xa_ams_review_for_x', ['title' => $containerContent->title]),
			'title_link' => $containerContent->getContentUrl(),
			'rating' => $preRegAction->action_data['rating'],
			'text' => $preRegAction->action_data['message']
		];
	}

	protected function getApprovalQueueTemplate(): string
	{
		return 'public:pre_reg_action_approval_queue_ams_article_rate';
	}
}