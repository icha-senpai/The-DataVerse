<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Service\Exception\InvalidAuthorizationStateException;
use OAuth\OAuth2\Service\Exception\InvalidScopeException;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class BattleNet
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service
 */
class BattleNet extends AbstractService
{

    /** -----------------------------------------------------------------------
     * Defined scopes.
     *
     * @link https://dev.battle.net/docs
     */
    const SCOPE_WOW_PROFILE = "wow.profile";

    const SCOPE_SC2_PROFILE = "sc2.profile";

    const SCOPE_D3_PROFILE = 'd3.profile';

    const SCOPE_OPENID = 'openid';

    /** -----------------------------------------------------------------------
     * Defined API URIs.
     *
     * @link https://dev.battle.net/docs
     */
    const API_URI_US = 'https://us.battle.net/oauth/';

    const API_URI_EU = 'https://eu.battle.net/oauth/';

    const API_URI_KR = 'https://kr.battle.net/oauth/';

    const API_URI_TW = 'https://tw.battle.net/oauth/';

    const API_URI_CN = 'https://www.battlenet.com.cn/oauth/';

    const API_URI_SEA = 'https://sea.battle.net/oauth/';

    /**
     * BattleNet constructor.
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
        parent::__construct(
            $credentials,
            $httpClient,
            $storage,
            $scopes,
            $baseApiUri
        );

        if ($baseApiUri === null) {
            $this->baseApiUri = new Uri(self::API_URI_US);
        }
    }

    /**
     * {@inheritdoc}
     * @throws InvalidAuthorizationStateException
     */
    public function requestAccessToken($code, $state = null)
    {
        if (null !== $state) {
            $this->validateAuthorizationState($state);
        }

        $bodyParams = array(
            'code' => $code,
            'client_id' => $this->credentials->getConsumerId(),
            'client_secret' => $this->credentials->getConsumerSecret(),
            'redirect_uri' => $this->credentials->getCallbackUrl(),
            'grant_type' => 'authorization_code'
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
        return new Uri($this->baseApiUri . 'token');
    }

    /**
     * @param string $responseBody
     * @return TokenInterface|StdOAuth2Token
     * @throws TokenResponseException
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if ($data === null || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            $err = $data['error'];
            throw new TokenResponseException(
                "Error in retrieving token: \"$err\""
            );
        }

        $token = new StdOAuth2Token(
            $data['access_token'],
            null,
            $data['expires_in']
        );

        unset($data['access_token']);
        unset($data['expires_in']);

        $token->setExtraParams($data);

        return $token;
    }

    /**
     * @return Uri|UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri($this->baseApiUri . 'authorize');
    }

    /**
     * @return int
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
    }
}
