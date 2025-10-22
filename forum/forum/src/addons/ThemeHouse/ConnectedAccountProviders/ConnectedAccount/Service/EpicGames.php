<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class EpicGames
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service
 */
class EpicGames extends AbstractService
{
    /**
     *
     */
    const SCOPE_BASIC_PROFILE = "basic_profile";

    /**
     * @return int
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_HEADER_BEARER;
    }

    /**
     * @param string $responseBody
     * @return TokenInterface|void
     * @throws TokenResponseException
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
        $token->setLifeTime($data['expires_in']);
        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }
        unset($data['access_token']);
        unset($data['expires_in']);
        $token->setExtraParams($data);
        return $token;
    }

    /**
     * @return string[]
     */
    protected function getExtraOAuthHeaders()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->credentials->getConsumerId() . ':' . $this->credentials->getConsumerSecret())
        ];
    }

    /**
     * @return Uri|UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://www.epicgames.com/id/authorize');
    }

    /**
     * @return Uri|UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.epicgames.dev/epic/oauth/v1/token');
    }
}