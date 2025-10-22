<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class DeviantArt
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Vimeo extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'me';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        $uri = $this->requestFromEndpoint('uri');
        $uriParts = explode('/', $uri);
        return array_pop($uriParts);
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('name');
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        return $this->requestFromEndpoint('pictures')['sizes'][5]['link'];
    }

    /**
     * @return mixed|null
     */
    public function getProfileLink()
    {
        return $this->requestFromEndpoint('link');
    }
}
