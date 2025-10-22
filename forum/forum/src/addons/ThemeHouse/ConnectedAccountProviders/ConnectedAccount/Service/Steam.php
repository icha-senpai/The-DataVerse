<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service;

use GuzzleHttp\Exception\GuzzleException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;
use XF;

class Steam extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUri(array $additionalParameters = array())
    {
        $realm = join('/', array_slice(explode('/', $this->credentials->getCallbackUrl()), 0, 3));

        $parameters = array_merge(
            $additionalParameters,
            array(
                'openid.ns' => 'http://specs.openid.net/auth/2.0',
                'openid.mode' => 'checkid_setup',
                'openid.return_to' => $this->credentials->getCallbackUrl(),
                'openid.realm' => $realm,
                'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
                'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
            )
        );

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
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://steamcommunity.com/openid/login');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://steamcommunity.com/openid/login');
    }

    /**
     * @param string $code
     * @param null $state
     * @return int|null|TokenInterface|string
     * @throws GuzzleException
     */
    public function requestAccessToken($code, $state = null)
    {
        $request = XF::app()->request();
        $claimedId = $request->filter('openid_claimed_id', 'str');


        $token = new StdOAuth2Token();

        if (preg_match("#^https://steamcommunity.com/openid/id/([0-9]{17,25})#", $claimedId, $matches)) {
            $steamID64 = is_numeric($matches[1]) ? $matches[1] : 0;
        } else {
            return $token;
        }

        $client = XF::app()->http()->client();

        $data = [
            'openid.assoc_handle' => $request->filter('openid_assoc_handle', 'str'),
            'openid.signed' => $request->filter('openid_signed', 'str'),
            'openid.sig' => $request->filter('openid_sig', 'str'),
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'check_authentication'
        ];

        foreach (explode(',', $request->filter('openid_signed', 'str')) as $item) {
            $value = $request->filter('openid_' . $item, 'str');
            $data['openid.' . $item] = $value;
        }

        $response = $client->request('POST', 'https://steamcommunity.com/openid/login', [
            'query' => $data,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ]);

        $responseContent = $response->getBody()->getContents();
        if (preg_match("#is_valid\s*:\s*true#i", $responseContent) !== 1) {
            return $token;
        }

        $token->setAccessToken($steamID64);
        $this->storage->storeAccessToken($this->service(), $token);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
    }
}