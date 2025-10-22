<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Keycloak
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Keycloak extends AbstractProviderData
{
    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('preferred_username');
    }

    public function getEmail()
    {
        return $this->requestFromEndpoint('email');
    }

    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'userinfo';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('sub');
    }
}
