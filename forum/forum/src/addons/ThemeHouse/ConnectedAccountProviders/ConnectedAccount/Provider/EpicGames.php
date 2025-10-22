<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider;

use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\Entity\ConnectedAccountProvider;

/**
 * Class Steam
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider
 */
class EpicGames extends AbstractProvider
{
    /**
     * @return string
     */
    public function getOAuthServiceName()
    {
        return 'ThemeHouse\ConnectedAccountProviders:Service\EpicGames';
    }

    /**
     * @return string
     */
    public function getProviderDataClass()
    {
        return 'ThemeHouse\ConnectedAccountProviders:ProviderData\EpicGames';
    }

    /**
     * @return string[]
     */
    public function getDefaultOptions()
    {
        return [
            'client_id' => '',
            'client_secret' => ''
        ];
    }

    /**
     * @param ConnectedAccountProvider $provider
     * @param null $redirectUri
     * @return array
     */
    public function getOAuthConfig(ConnectedAccountProvider $provider, $redirectUri = null)
    {
        return [
            'key' => $provider->options['client_id'],
            'secret' => $provider->options['client_secret'],
            'scopes' => ['basic_profile'],
            'redirect' => $redirectUri ?: $this->getRedirectUri($provider)
        ];
    }
}
