<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class BattleNet
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class BattleNet extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'userinfo';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('id');
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('battletag');
    }
}
