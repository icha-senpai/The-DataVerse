<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Service\Exception\InvalidScopeException;
use OAuth\OAuth2\Token\StdOAuth2Token;
use XF\Entity\ConnectedAccountProvider;

/**
 * Class Imgur
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service
 *
 * FROM https://github.com/Lusitanian/PHPoAuthLib/pull/444
 */
class Miniorange extends AbstractService
{
    const SCOPE_PROFILE = 'profile';
    const SCOPE_EMAIL = 'email';

    /**
     * Defined scopes
     *
     * @link http://www.reddit.com/dev/api/oauth
     * @param CredentialsInterface $credentials
     * @param ClientInterface $httpClient
     * @param TokenStorageInterface $storage
     * @param array $scopes
     * @param UriInterface|null $baseApiUri
     * @throws InvalidScopeException
     */
    public function __construct(
        CredentialsInterface  $credentials,
        ClientInterface       $httpClient,
        TokenStorageInterface $storage,
                              $scopes = array(),
        UriInterface          $baseApiUri = null
    )
    {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, true);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://login.xecurify.com/moas/');
        }
    }

    protected ?ConnectedAccountProvider $connectedAccountProvider = null;
    protected function getConnectedAccountProvider(): ConnectedAccountProvider
    {
        if(!$this->connectedAccountProvider) {
            $this->connectedAccountProvider = \XF::em()->find('XF:ConnectedAccountProvider', 'th_cap_miniorange');
        }
        return $this->connectedAccountProvider;
    }

    protected function getCustomerId(): string
    {
        return $this->getConnectedAccountProvider()->options['customer_id'];
    }

    protected function getSubdomain(): string
    {
        return $this->getConnectedAccountProvider()->options['subdomain'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://' . $this->getSubdomain() . '/moas/broker/login/oauth/' . $this->getCustomerId());
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://' . $this->getSubdomain() . '/moas/rest/oauth/token');
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
}