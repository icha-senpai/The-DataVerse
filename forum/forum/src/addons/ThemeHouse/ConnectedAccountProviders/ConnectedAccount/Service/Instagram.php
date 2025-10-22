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
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Service\Exception\InvalidAuthorizationStateException;
use OAuth\OAuth2\Service\Exception\InvalidScopeException;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class Instagram
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service
 */
class Instagram extends AbstractService
{
    const SCOPE_USER_PROFILE = 'user_profile';

    /**
     * Twitch constructor.
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
            $this->baseApiUri = new Uri('https://graph.instagram.com');
        }
    }

    /**
     * @param array $additionalParameters
     * @return UriInterface
     */
    public function getAuthorizationUri(array $additionalParameters = array())
    {
        return parent::getAuthorizationUri(['app_id' => $this->credentials->getConsumerId()]);
    }

    /**
     * @return Uri|UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://api.instagram.com/oauth/authorize');
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
     * @param $code
     * @param null $state
     * @return TokenInterface|StdOAuth2Token
     * @throws TokenResponseException
     * @throws InvalidAuthorizationStateException
     */
    public function requestAccessToken($code, $state = null)
    {
        if (null !== $state) {
            $this->validateAuthorizationState($state);
        }

        $bodyParams = array(
            'code' => $code,
            'app_id' => $this->credentials->getConsumerId(),
            'app_secret' => $this->credentials->getConsumerSecret(),
            'redirect_uri' => $this->credentials->getCallbackUrl(),
            'grant_type' => 'authorization_code',
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
     * @return Uri|UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.instagram.com/oauth/access_token');
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
