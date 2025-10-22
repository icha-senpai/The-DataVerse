<?php

namespace ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller;

use XF;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error;
use XF\Mvc\Reply\Message;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;

/**
 * Class Login
 * @package ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller
 */
class Login extends XFCP_Login
{
    /**
     * @return Message|Redirect|View
     */
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof View) {
            return $this->thCapRedirectIfNecessary($response);
        }

        return $response;
    }

    /**
     * @param View $response
     * @return Redirect|View
     */
    protected function thCapRedirectIfNecessary(View $response)
    {
        $options = XF::options();
        if ($options->thconnectedaccountproviders_forceOauthLogin && $options->thconnectedaccountproviders_redirectSingleProvider) {
            /** @var ArrayCollection $providers */
            $providers = $response->getParam('providers');
            if ($providers->count() == 1) {
                return $this->redirect($this->buildLink(
                    'register/connected-accounts',
                    $providers->first(),
                    \XF::app()->request()->getRequestQueryParamsExcept([
                        '_xfRequestUri',
                        '_xfWithData',
                        '_xfToken',
                        '_xfResponseType'
                    ], true) + ['setup' => 1]
                ), '');
            }
        }

        return $response;
    }

    /**
     * @param XF\Mvc\ParameterBag $params
     * @return Error|Message|Redirect|View
     */
    public function actionLogin(ParameterBag $params)
    {
        if (XF::visitor()->user_id) {
            if ($this->filter('check', 'bool')) {
                return $this->redirect($this->getDynamicRedirectIfNot($this->buildLink('login')));
            }

            return $this->message(XF::phrase('you_already_logged_in', ['link' => $this->buildLink('forums')]));
        }

        if ($this->isPost()) {
            if (XF::options()->thconnectedaccountproviders_forceOauthLogin) {
                return $this->error(XF::phrase('th_cap_conventional_login_has_been_disabled_use_oauth'));
            }
        } else {
            $response = parent::actionLogin($params);

            if ($response instanceof View) {
                return $this->thCapRedirectIfNecessary($response);
            }
        }

        return parent::actionLogin($params);
    }
}
