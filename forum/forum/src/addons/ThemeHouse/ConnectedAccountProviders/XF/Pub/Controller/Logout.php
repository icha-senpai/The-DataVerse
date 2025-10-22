<?php

namespace ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller;

use XF;
use XF\Mvc\Reply\Redirect;
use XF\Repository\ConnectedAccount;

/**
 * Class Logout
 * @package ThemeHouse\ConnectedAccountProviders\XF\Pub\Controller
 */
class Logout extends XFCP_Logout
{
    /**
     * @return Redirect
     */
    public function actionIndex()
    {
        $response = parent::actionIndex();

        $options = XF::options();
        if ($response instanceof Redirect
            && $options->thconnectedaccountproviders_forceOauthLogin
            && $options->thconnectedaccountproviders_redirectSingleProvider
            && $options->thconnectedaccountproviders_redirectAfterLogout) {
            /** @var ConnectedAccount $conAccRepo */
            $conAccRepo = $this->repository('XF:ConnectedAccount');
            $providers = $conAccRepo->getUsableProviders(false);
            if ($providers->count() == 1) {
                $boardUrl = rtrim($options->boardUrl, '/');
                $redirectUrl = $boardUrl . '/' . ltrim(str_replace($boardUrl, '', $this->getDynamicRedirect()), '/');
                $url = str_replace('{url}', $redirectUrl, $options->thconnectedaccountproviders_redirectAfterLogout);
                return $this->redirect($url);
            }
        }

        return $response;
    }
}
