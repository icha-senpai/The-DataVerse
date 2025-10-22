<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Imgur
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Imgur extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'account/me';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('data')['id'];
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('data')['url'];
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        return $this->requestFromEndpoint('data')['avatar'];
    }

    /**
     * @return mixed|null
     */
    public function getProfileLink()
    {
        $url = $this->requestFromEndpoint('data')['url'];
        return $url ? 'https://imgur.com/user/' . $url : null;
    }
}
