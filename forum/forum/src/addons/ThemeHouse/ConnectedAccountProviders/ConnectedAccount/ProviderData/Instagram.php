<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Instagram
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Instagram extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'me?fields=id,username';
    }

    /**
     * @return string
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('id');
    }

    /**
     * @return string
     */
    public function getProfileLink()
    {
        $username = $this->getUsername();
        return $username ? 'https://www.instagram.com/' . $username : null;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('username');
    }
}
