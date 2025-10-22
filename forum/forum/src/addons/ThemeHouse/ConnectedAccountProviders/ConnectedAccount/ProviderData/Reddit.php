<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Reddit
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Reddit extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'api/v1/me';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('id');
    }

    /**
     * @return null
     */
    public function getAvatarUrl()
    {
        return $this->requestFromEndpoint('icon_img');
    }

    /**
     * @return string
     */
    public function getProfileLink()
    {
        $username = $this->getUsername();
        return $username ? 'https://www.reddit.com/user/' . $username : null;
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('name');
    }
}
