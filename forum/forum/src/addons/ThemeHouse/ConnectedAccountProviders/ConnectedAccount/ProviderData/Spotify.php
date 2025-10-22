<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class DeviantArt
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Spotify extends AbstractProviderData
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
        return $this->requestFromEndpoint('id');
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('display_name');
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        return $this->requestFromEndpoint('images')[0]['url'];
    }

    /**
     * @return mixed|null
     */
    public function getProfileLink()
    {
        return $this->requestFromEndpoint('external_urls')['spotify'];
    }
}
