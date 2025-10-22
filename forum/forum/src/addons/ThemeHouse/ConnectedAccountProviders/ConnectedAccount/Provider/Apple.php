<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider;

use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\ConnectedAccount\Storage\StorageState;
use XF\Entity\ConnectedAccountProvider;
use XF\Mvc\Controller;

class Apple extends AbstractProvider
{
    public function getOAuthServiceName()
    {
        return 'ThemeHouse\ConnectedAccountProviders:Service\Apple';
    }

    public function getProviderDataClass()
    {
        return 'ThemeHouse\ConnectedAccountProviders:ProviderData\Apple';
    }

    public function getDefaultOptions()
    {
        return [
            'team_id' => '',
            'client_id' => '',
            'private_key' => '',
            'private_key_id' => '',
        ];
    }

    public function getOAuthConfig(ConnectedAccountProvider $provider, $redirectUri = null)
    {
        return [
            'key' => $provider->options['client_id'],
            'secret' => $this->getAppleRepo()->generatePrivateKey($provider),
            'scopes' => ['name', 'email'],
            'redirect' => $redirectUri ?: $this->getRedirectUri($provider)
        ];
    }

    public function getAppleIDConfiguration(ConnectedAccountProvider $provider, $redirectUri = null)
    {
        $config = $this->getOAuthConfig($provider);
        return [
            'clientId' => $provider->options['client_id'],
            'scope' => implode(' ', $config['scopes']),
        ];
    }

    /**
     * @return \XF\Mvc\Entity\Repository|\ThemeHouse\ConnectedAccountProviders\Repository\Apple
     */
    protected function getAppleRepo()
    {
        return \XF::app()->repository('ThemeHouse\ConnectedAccountProviders:Apple');
    }
}