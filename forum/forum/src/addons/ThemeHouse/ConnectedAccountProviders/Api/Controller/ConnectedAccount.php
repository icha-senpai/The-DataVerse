<?php

namespace ThemeHouse\ConnectedAccountProviders\Api\Controller;

use XF;
use XF\Api\Controller\AbstractController;
use XF\Api\Mvc\Reply\ApiResult;
use XF\Entity\ConnectedAccountProvider;
use XF\Entity\User;
use XF\Entity\UserConnectedAccount;
use XF\Mvc\Entity\Entity;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception;
use XF\PrintableException;

/**
 * Class ConnectedAccount
 * @package ThemeHouse\ConnectedAccountProviders\Api\Controller
 */
class ConnectedAccount extends AbstractController
{
    /**
     * @api-desc Gets information about the specified user.
     *
     * @api-in bool $with_posts If specified, the response will include a page of profile posts.
     * @api-in int $page The page of comments to include
     *
     * @api-out User $user
     * @api-see self::getProfilePostsForUserPaginated()
     * @param ParameterBag $params
     * @return ApiResult
     * @throws Exception
     */
    public function actionGet(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params['user_id']);
        $this->assertApiScope('user:read');

        return $this->apiResult($user->Profile->connected_accounts);
    }

    /**
     * @param int $id
     * @param mixed $with
     * @param bool $basicProfileOnly
     *
     * @return User
     *
     * @throws Exception
     */
    protected function assertViewableUser($id, $with = 'api', $basicProfileOnly = true)
    {
        /** @var User $user */
        $user = $this->assertRecordExists('XF:User', $id, $with);

        if (XF::isApiCheckingPermissions()) {
            $canView = $basicProfileOnly ? $user->canViewBasicProfile($error) : $user->canViewFullProfile($error);
            if (!$canView) {
                throw $this->exception($this->noPermission($error));
            }
        }

        return $user;
    }

    /**
     * @api-desc Updates an existing user.
     *
     * @api-see \XF\Api\ControllerPlugin\User::userSaveProcessAdmin()
     *
     * @api-out true $success
     * @api-out User $user
     * @param ParameterBag $params
     * @return ApiResult
     * @throws Exception
     * @throws PrintableException
     */
    public function actionPost(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params['user_id']);
        $this->assertApiScope('user:write');

        $providerKey = $this->filter('provider_key', 'str');
        $providerId = $this->filter('provider_id', 'str');
        $extraData = $this->filter('extra_data', 'array', []);
        $this->assertProviderExists($providerId);

        /** @var \ThemeHouse\ConnectedAccountProviders\XF\Repository\ConnectedAccount $repo */
        $repo = $this->repository('XF:ConnectedAccount');
        $repo->associateThConnectedAccountWithUser($user, $providerKey, $providerId, $extraData);

        return $this->apiSuccess([
            'user' => $user->toApiResult(Entity::VERBOSITY_VERBOSE),
            'connectedAccounts' => $user->Profile->connected_accounts
        ]);
    }

    /**
     * @param string $id
     * @param array|string|null $with
     * @param null|string $phraseKey
     *
     * @return Entity|ConnectedAccountProvider
     * @throws Exception
     */
    protected function assertProviderExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists('XF:ConnectedAccountProvider', $id, $with, $phraseKey);
    }

    /**
     * @api-desc Deletes the specified user
     *
     * @api-in str $rename_to If specified, the user will be renamed before deletion
     *
     * @api-out true $success
     * @param ParameterBag $params
     * @return ApiResult|XF\Mvc\Reply\Error
     * @throws Exception
     * @throws PrintableException
     */
    public function actionDelete(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params['user_id']);
        $this->assertApiScope('user:write');


        $providerId = $this->filter('provider_id', 'str');

        $provider = $this->assertProviderExists($providerId);
        $handler = $provider->getHandler();

        $connectedAccounts = $user->ConnectedAccounts;
        /** @var UserConnectedAccount $connectedAccount */
        $connectedAccount = $connectedAccounts[$providerId] ?? null;

        $connectedAccount->delete();

        $storageState = $handler->getStorageState($provider, $user);
        $storageState->clearToken();

        /** @var XF\Entity\UserProfile $profile */
        $profile = $user->getRelationOrDefault('Profile');
        $profileConnectedAccounts = $profile->connected_accounts;
        unset($profileConnectedAccounts[$provider->provider_id]);
        $profile->connected_accounts = $profileConnectedAccounts;

        $user->save();

        return $this->apiSuccess([
            'user' => $user->toApiResult(Entity::VERBOSITY_VERBOSE),
            'connectedAccounts' => $user->Profile->connected_accounts
        ]);
    }

    /**
     * @param $action
     * @param ParameterBag $params
     * @throws Exception
     */
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertSuperUserKey();
    }
}
