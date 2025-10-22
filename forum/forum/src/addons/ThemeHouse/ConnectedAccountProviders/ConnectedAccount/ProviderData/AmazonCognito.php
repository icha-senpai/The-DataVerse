<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class AmazonCognito
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class AmazonCognito extends AbstractProviderData
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
        return 'oauth2/userInfo';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('sub');
    }
}
