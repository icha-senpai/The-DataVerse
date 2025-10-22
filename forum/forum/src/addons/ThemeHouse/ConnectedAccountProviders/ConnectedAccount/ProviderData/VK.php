<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class DeviantArt
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class VK extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'getProfiles?v=5.103';
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        $data = $this->requestFromEndpoint('response')[0];
        return trim($data['first_name'] . ' ' . $data['last_name']);
    }

    /**
     * @return mixed|null
     */
    public function getProfileLink()
    {
        return 'https://vk.com/id' . $this->getProviderKey();
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('response')[0]['id'];
    }
}
