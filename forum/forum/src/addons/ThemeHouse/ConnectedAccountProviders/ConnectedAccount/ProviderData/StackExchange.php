<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class StackExchange
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class StackExchange extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'me?site=stackoverflow';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        $data = array_values($this->requestFromEndpoint('items') ?? []);
        return $data[0]['account_id'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getAvatarUrl()
    {
        $data = array_values($this->requestFromEndpoint('items') ?? []);
        return $data[0]['profile_image'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getProfileLink()
    {
        $data = array_values($this->requestFromEndpoint('items') ?? []);
        return $data[0]['link'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        $data = array_values($this->requestFromEndpoint('items') ?? []);
        return $data[0]['display_name'] ?? null;
    }
}
