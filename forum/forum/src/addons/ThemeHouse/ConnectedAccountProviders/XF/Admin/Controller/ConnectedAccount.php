<?php

namespace ThemeHouse\ConnectedAccountProviders\XF\Admin\Controller;

use XF\Entity\ConnectedAccountProvider;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;
use XF\PrintableException;

/**
 * Class ConnectedAccount
 * @package ThemeHouse\ConnectedAccountProviders\XF\Admin\Controller
 */
class ConnectedAccount extends XFCP_ConnectedAccount
{
    /**
     * @return Redirect|View
     * @throws PrintableException
     */
    public function actionThCapSort()
    {
        $repo = $this->getConnectedAccountRepo();
        $providers = $repo->getUsableProviders();

        if ($this->isPost()) {
            $lastOrder = 0;

            foreach (json_decode($this->filter('providers', 'string')) as $providerValue) {
                $lastOrder += 10;

                /** @var ConnectedAccountProvider $provider */
                $provider = $providers[$providerValue->id];
                $provider->display_order = $lastOrder;

                $provider->save();
            }

            return $this->redirect($this->buildLink('connected-accounts'));
        } else {
            $viewParams = [
                'providers' => $providers
            ];
            return $this->view('ThemeHouse\ConnectedAccountProviders:ConnectedAccount\Sort',
                'th_cap_connected_account_provider_sort', $viewParams);
        }
    }
}
