<?php

namespace ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller;

use XF;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;

/**
 * Class Account
 * @package ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller
 */
class Account extends XFCP_Account
{
    /**
     * @return XF\Mvc\Reply\AbstractReply|View
     */
    public function actionConnectedAccount()
    {
        if (!XF::options()->thconnectedaccountproviders_enableConAccSect) {
            return $this->notFound();
        }

        return parent::actionConnectedAccount();
    }

    /**
     * @param ParameterBag $params
     * @return XF\Mvc\Reply\AbstractReply|Redirect
     */
    public function actionConnectedAccountDisassociate(ParameterBag $params)
    {
        if (!XF::options()->thconnectedaccountproviders_enableConAccSect) {
            return $this->notFound();
        }

        return parent::actionConnectedAccountDisassociate($params);
    }

    /**
     * @return XF\Mvc\Reply\AbstractReply|Redirect|View
     */
    public function actionSecurity()
    {
        if (!XF::visitor()->is_admin && XF::options()->thconnectedaccountproviders_forceOauthLogin) {
            return $this->notFound();
        }

        return parent::actionSecurity();
    }

    /**
     * @return XF\Mvc\Reply\AbstractReply|Redirect|View
     */
    public function actionEmail()
    {
        if (XF::options()->thconnectedaccountproviders_updateEmail) {
            return $this->noPermission();
        }

        return parent::actionEmail();
    }
}
