<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Twitch
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Twitch extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'users';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        $data = $this->requestUserData();
        return !empty($data['id']) ? $data['id'] : null;
    }

    /**
     * @return mixed
     */
    protected function requestUserData()
    {
        $user = $this->requestFromEndpoint('data');
        return !empty($user[0]) ? $user[0] : null;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        $data = $this->requestUserData();
        return !empty($data['email']) ? $data['email'] : null;
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        $data = $this->requestUserData();
        return !empty($data['profile_image_url']) ? $data['profile_image_url'] : null;
    }

    /**
     * @return string
     */
    public function getProfileLink()
    {
        $username = $this->getUsername();
        return $username ? 'https://twitch.tv/' . $username : null;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        $data = $this->requestUserData();
        return !empty($data['display_name']) ? $data['display_name'] : null;
    }
}
