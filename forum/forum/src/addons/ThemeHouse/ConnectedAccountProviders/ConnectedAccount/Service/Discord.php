<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\Exception\ExpiredTokenException;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Service\Exception\InvalidScopeException;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class Discord
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service
 */
class Discord extends AbstractService
{
    /**
     * Discord constructor.
     * @param CredentialsInterface $credentials
     * @param ClientInterface $httpClient
     * @param TokenStorageInterface $storage
     * @param array $scopes
     * @param UriInterface|null $baseApiUri
     * @throws InvalidScopeException
     */
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://discordapp.com/api/v6/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUri(array $additionalParameters = array())
    {
        $parameters = array_merge(
            $additionalParameters,
            array(
                'client_id' => $this->credentials->getConsumerId(),
                'redirect_uri' => $this->credentials->getCallbackUrl(),
                'response_type' => 'code',
            )
        );

        # $parameters['scope'] = implode(' ', $this->scopes);
        $parameters['scope'] = 'identify email';

        // Build the url
        $url = clone $this->getAuthorizationEndpoint();
        foreach ($parameters as $key => $val) {
            $url->addToQuery($key, $val);
        }

        // --- FIX: ensure PHP session lock is released before redirect ---
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://discordapp.com/api/oauth2/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://discordapp.com/api/oauth2/token');
    }

    /**
     * @param UriInterface|string $path
     * @param string $method
     * @param null $body
     * @param array $extraHeaders
     * @return string
     * @throws Exception
     * @throws ExpiredTokenException
     */
    public function request($path, $method = 'GET', $body = null, array $extraHeaders = array())
    {
        return parent::request($path, $method, $body, $extraHeaders);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_HEADER_BEARER;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);

        $token->setExtraParams($data);

        return $token;
    }
}
