<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider;

use XF\ConnectedAccount\Http\HttpResponseException;
use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\Entity\ConnectedAccountProvider;

/**
 * Class Dropbox
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider
 */
class StackExchange extends AbstractProvider
{
    /**
     * @return string
     */
    public function getOAuthServiceName()
    {
        return 'ThemeHouse\ConnectedAccountProviders:Service\StackExchange';
    }

    /**
     * @return string
     */
    public function getProviderDataClass()
    {
        return 'ThemeHouse\ConnectedAccountProviders:ProviderData\StackExchange';
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'client_id' => '',
            'client_secret' => '',
            'app_key' => '',
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
            'app_key' => $provider->options['app_key'],
            'scopes' => [],
            'redirect' => $redirectUri ?: $this->getRedirectUri($provider)
        ];
    }

    public function getOAuth(array $config)
    {
        $provider = parent::getOAuth($config);

        // necessary for API requests
        $provider->setAppKey($config['app_key']);

        return $provider;
    }

    /**
     * @param HttpResponseException $e
     * @param null $error
     */
    public function parseProviderError(HttpResponseException $e, &$error = null)
    {
        $response = json_decode($e->getResponseContent(), true);
        if (is_array($response) && isset($response['error']['message'])) {
            $e->setMessage($response['error']['message']);
        }
        parent::parseProviderError($e, $error);
    }
}
