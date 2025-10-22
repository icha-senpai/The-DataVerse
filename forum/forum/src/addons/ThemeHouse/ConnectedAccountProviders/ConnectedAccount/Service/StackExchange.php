<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Token\Exception\ExpiredTokenException;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Service\Exception\InvalidScopeException;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class StackExchange
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service
 */
class StackExchange extends AbstractService
{
    protected $appKey = '';
    /**
     * StackExchange constructor.
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
            $this->baseApiUri = new Uri('https://api.stackexchange.com/2.2/');
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

        $parameters['scope'] = implode(' ', $this->scopes);

        // Build the url
        $url = clone $this->getAuthorizationEndpoint();
        foreach ($parameters as $key => $val) {
            $url->addToQuery($key, $val);
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function requestAccessToken($code, $state = null)
    {
        if (null !== $state) {
            $this->validateAuthorizationState($state);
        }

        $bodyParams = array(
            'code'          => $code,
            'client_id'     => $this->credentials->getConsumerId(),
            'client_secret' => $this->credentials->getConsumerSecret(),
            'redirect_uri'  => $this->credentials->getCallbackUrl(),
            'grant_type'    => 'authorization_code',
        );

        $responseBody = $this->httpClient->retrieveResponse(
            $this->getAccessTokenEndpoint(),
            $bodyParams,
            $this->getExtraOAuthHeaders()
        );

        $token = $this->parseAccessTokenResponse($responseBody);
        $this->storage->storeAccessToken($this->service(), $token);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://stackoverflow.com/oauth');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://stackoverflow.com/oauth/access_token');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data) || empty($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        $token->setLifeTime($data['expires']);

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);
        unset($data['expires']);

        $token->setExtraParams($data);

        return $token;
    }

    public function setAppKey($appKey)
    {
        $this->appKey = $appKey;
    }

    /**
     * Sends an authenticated API request to the path provided.
     * If the path provided is not an absolute URI, the base API Uri (must be passed into constructor) will be used.
     *
     * @param string|UriInterface $path
     * @param string              $method       HTTP method
     * @param array               $body         Request body if applicable.
     * @param array               $extraHeaders Extra headers if applicable. These will override service-specific
     *                                          any defaults.
     *
     * @return string
     *
     * @throws ExpiredTokenException
     * @throws Exception
     */
    public function request($path, $method = 'GET', $body = null, array $extraHeaders = array())
    {
        $uri = $this->determineRequestUriFromPath($path, $this->baseApiUri);
        $token = $this->storage->retrieveAccessToken($this->service());

        if ($token->getEndOfLife() !== TokenInterface::EOL_NEVER_EXPIRES
            && $token->getEndOfLife() !== TokenInterface::EOL_UNKNOWN
            && time() > $token->getEndOfLife()
        ) {
            throw new ExpiredTokenException(
                sprintf(
                    'Token expired on %s at %s',
                    date('m/d/Y', $token->getEndOfLife()),
                    date('h:i:s A', $token->getEndOfLife())
                )
            );
        }

        // add the token where it may be needed
        if (static::AUTHORIZATION_METHOD_HEADER_OAUTH === $this->getAuthorizationMethod()) {
            $extraHeaders = array_merge(array('Authorization' => 'OAuth ' . $token->getAccessToken()), $extraHeaders);
        } elseif (static::AUTHORIZATION_METHOD_QUERY_STRING === $this->getAuthorizationMethod()) {
            $uri->addToQuery('access_token', $token->getAccessToken());
        } elseif (static::AUTHORIZATION_METHOD_QUERY_STRING_V2 === $this->getAuthorizationMethod()) {
            $uri->addToQuery('oauth2_access_token', $token->getAccessToken());
        } elseif (static::AUTHORIZATION_METHOD_QUERY_STRING_V3 === $this->getAuthorizationMethod()) {
            $uri->addToQuery('apikey', $token->getAccessToken());
        } elseif (static::AUTHORIZATION_METHOD_QUERY_STRING_V4 === $this->getAuthorizationMethod()) {
            $uri->addToQuery('auth', $token->getAccessToken());
        } elseif (static::AUTHORIZATION_METHOD_HEADER_BEARER === $this->getAuthorizationMethod()) {
            $extraHeaders = array_merge(array('Authorization' => 'Bearer ' . $token->getAccessToken()), $extraHeaders);
        }

        $uri->addToQuery('key', $this->appKey);

        $extraHeaders = array_merge($this->getExtraApiHeaders(), $extraHeaders);

        return $this->httpClient->retrieveResponse($uri, $body, $extraHeaders, $method);
    }
}
