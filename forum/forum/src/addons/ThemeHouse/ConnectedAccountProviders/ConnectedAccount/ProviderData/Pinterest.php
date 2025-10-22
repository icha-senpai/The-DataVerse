<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Pinterest
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Pinterest extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'v1/me';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('data')['id'];
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return trim($this->requestFromEndpoint('data')['first_name'] . ' ' . $this->requestFromEndpoint('data')['last_name']);
    }

    /**
     * @return string
     */
    public function getProfileLink()
    {
        return $this->requestFromEndpoint('data')['url'];
    }
}
