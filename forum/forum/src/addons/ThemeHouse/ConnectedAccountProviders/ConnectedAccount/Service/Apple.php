<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;
use ThemeHouse\ConnectedAccountProviders\OAuth\Common\Http\Uri\Uri;

class Apple extends AbstractService
{
    const SCOPE_NAME = 'name';
    const SCOPE_EMAIL = 'email';

    protected function parseAccessTokenResponse($responseBody, array $extraData = [])
    {
        $data = json_decode($responseBody, true);
        $data = array_merge($data, $extraData);

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['id_token']);
        unset($data['id_token']);

        $token->setExtraParams($data);

        return $token;
    }

    public function getAuthorizationEndpoint()
    {
        return new Uri('https://appleid.apple.com/auth/authorize');
    }

    public function getAccessTokenEndpoint()
    {
        return new Uri('https://appleid.apple.com/auth/token');
    }

    public function getAuthorizationUri(array $additionalParameters = array())
    {
        $additionalParameters['response_mode'] = 'form_post';

        return parent::getAuthorizationUri($additionalParameters);
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

        $userData = \XF::app()->request()->filter('user', 'string');
        $userData = json_decode($userData, false) ?: [];

        $token = $this->parseAccessTokenResponse($responseBody, [
            'user' => $userData,
        ]);
        $this->storage->storeAccessToken($this->service(), $token);

        return $token;
    }

    /**
     * @return \XF\Mvc\Entity\Repository|\ThemeHouse\ConnectedAccountProviders\Repository\Apple
     */
    protected function getAppleRepo()
    {
        return \XF::app()->repository('ThemeHouse\ConnectedAccountProviders:Apple');
    }
}