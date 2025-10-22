<?php /** @noinspection PhpIllegalStringOffsetInspection */

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class AmazonCognito
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service
 */
class AmazonCognito extends AbstractService
{
    /**
     * AmazonCognito constructor.
     * @param CredentialsInterface $credentials
     * @param ClientInterface $httpClient
     * @param TokenStorageInterface $storage
     * @param array $scopes
     * @param UriInterface|null $baseApiUri
     * @param false $stateParameterInAutUrl
     * @param string $apiVersion
     * @throws \OAuth\OAuth2\Service\Exception\InvalidScopeException
     */
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        array $scopes = array(),
        UriInterface $baseApiUri = null,
        $stateParameterInAutUrl = false,
        $apiVersion = ""
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, $stateParameterInAutUrl,
            $apiVersion);

        $connectedAccountProvider = \XF::finder('XF:ConnectedAccountProvider')->where('provider_id', 'th_cap_amazoncognito')->fetchOne();

        $this->baseApiUri = new Uri($connectedAccountProvider->options['base_url']);
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function isValidScope($scope)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri($this->baseApiUri . '/oauth2/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri($this->baseApiUri . '/oauth2/token');
    }

    /**
     * @return int
     */
    protected function getAuthorizationMethod()
    {
        return self::AUTHORIZATION_METHOD_HEADER_BEARER;
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