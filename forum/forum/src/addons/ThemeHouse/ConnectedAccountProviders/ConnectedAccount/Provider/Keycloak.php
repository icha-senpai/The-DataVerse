<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider;

use XF\ConnectedAccount\Http\HttpResponseException;
use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\Entity\ConnectedAccountProvider;

/**
 * Class Keycloak
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider
 */
class Keycloak extends AbstractProvider
{
    /**
     * @return string
     */
    public function getOAuthServiceName()
    {
        return 'ThemeHouse\ConnectedAccountProviders:Service\Keycloak';
    }

    /**
     * @return string
     */
    public function getProviderDataClass()
    {
        return 'ThemeHouse\ConnectedAccountProviders:ProviderData\Keycloak';
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'base_url' => '',
            'realm_name' => '',
            'client_id' => '',
            'scopes' => "openid"
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
            'secret' => [
                'base_url' => $provider->options['base_url'],
                'realm_name' => $provider->options['realm_name'],
            ],
            'scopes' => explode("\n", $provider->options['scopes']),
            'redirect' => $redirectUri ?: $this->getRedirectUri($provider),
        ];
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
