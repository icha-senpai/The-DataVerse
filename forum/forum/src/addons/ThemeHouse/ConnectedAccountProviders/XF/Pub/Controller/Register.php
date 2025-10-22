<?php

namespace ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller;

use Throwable;
use XF;
use XF\Entity\User;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error;
use XF\Mvc\Reply\Exception;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;
use XF\Util\File;

/**
 * Class Register
 * @package ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller
 */
class Register extends XFCP_Register
{
    /**
     * @param ParameterBag $params
     * @return Error|Redirect|View
     * @throws Exception
     */
    public function actionConnectedAccount(ParameterBag $params)
    {
        $response = parent::actionConnectedAccount($params);

        if ($response instanceof Error) {
            $errors = $response->getErrors();
            if ($errors[0] && $errors[0]->getName() == 'this_accounts_email_is_already_associated_with_another_member') {
                $provider = $this->assertProviderExists($params['provider_id']);
                $handler = $provider->getHandler();

                if (in_array($provider->provider_id, XF::options()->thconnectedaccountproviders_bypass)) {
                    $storageState = $handler->getStorageState($provider, XF::visitor());
                    $providerData = $handler->getProviderData($storageState);
                    $connectedRepo = $this->getConnectedAccountRepo();
                    $userConnected = $connectedRepo->getUserConnectedAccountFromProviderData($providerData);

                    if (!$userConnected) {
                        /** @var User $emailUser */
                        /** @noinspection PhpUndefinedFieldInspection */
                        $emailUser = $this->em()->findOne('XF:User', ['email' => $providerData->email]);
                        if ($emailUser) {
                            $connectedRepo->associateConnectedAccountWithUser($emailUser, $providerData);
                            $response = parent::actionConnectedAccount($params);
                        }
                    }
                }
            }
        }

        $visitor = XF::visitor();
        if ($response instanceof Redirect && $visitor->user_id && !$this->filter('setup', 'bool')) {
            $provider = $this->assertProviderExists($params['provider_id']);
            $handler = $provider->getHandler();
            $storageState = $handler->getStorageState($provider, XF::visitor());
            $providerData = $handler->getProviderData($storageState);
            $connectedRepo = $this->getConnectedAccountRepo();
            $connectedRepo->syncProfileFieldsForProvider($visitor, $providerData);
        }

        return $response;
    }

    protected function getConnectedRegisterResponse(array $viewParams)
    {
        if (\XF::options()->thconnectedaccountproviders_autoCreateAccounts) {
            try {
                /** @var XF\ConnectedAccount\ProviderData\AbstractProviderData $providerData */
                $providerData = $viewParams['providerData'];

                $registration = $this->setupConnectedRegistration([
                    'username' => $providerData->username,
                    'email' => $providerData->email
                ], $providerData);
                $registration->validate();

                $user = $registration->save();
                $connectedRepo = $this->getConnectedAccountRepo();
                $connectedRepo->associateConnectedAccountWithUser($user, $providerData);
                $this->finalizeRegistration($user);
                return $this->redirect($viewParams['redirect']);
            } catch (Throwable $e) {
                \XF::logException($e);
                // Default to normal response on error, we want them to get logged in in all cases
            }
        }

        return parent::getConnectedRegisterResponse($viewParams);
    }

    /**
     * @return Redirect|View
     */
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof View) {
            $options = XF::options();
            if ($options->thconnectedaccountproviders_forceOauthRegistration && $options->thconnectedaccountproviders_redirectSingleProvider) {
                /** @var ArrayCollection $providers */
                $providers = $response->getParam('providers');
                if ($providers->count() == 1) {
                    return $this->redirect($this->buildLink('register/connected-accounts', $providers->first(),
                        ['setup' => 1]), '');
                }
            }
        }

        return $response;
    }

    /**
     * @return Error|Redirect
     * @throws Exception
     */
    public function actionRegister()
    {
        $this->assertPostOnly();
        $this->assertRegistrationActive();

        if (XF::options()->thconnectedaccountproviders_forceOauthRegistration) {
            return $this->error(XF::phrase('th_cap_conventional_registration_has_been_disabled_use_oauth'));
        }

        return parent::actionRegister();
    }
}
