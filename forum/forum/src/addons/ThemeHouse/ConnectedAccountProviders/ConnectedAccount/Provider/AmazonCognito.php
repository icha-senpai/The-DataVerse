<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider;

use XF\ConnectedAccount\Http\HttpResponseException;
use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\Entity\ConnectedAccountProvider;

/**
 * Class AmazonCognito
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Provider
 */
class AmazonCognito extends AbstractProvider
{
    /**
     * @return string
     */
    public function getOAuthServiceName()
    {
        return 'ThemeHouse\ConnectedAccountProviders:Service\AmazonCognito';
    }

    /**
     * @return string
     */
    public function getProviderDataClass()
    {
        return 'ThemeHouse\ConnectedAccountProviders:ProviderData\AmazonCognito';
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'base_url' => '',
            'client_id' => '',
            'client_secret' => '',
            'scopes' => '',
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
            'scopes' => explode("\n", $provider->options['scopes']),
            'redirect' => $redirectUri ?: $this->getRedirectUri($provider)
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

    public function getRedirectUri(ConnectedAccountProvider $provider)
    {
        return 'http://localhost/connected_account.php';
    }
}
