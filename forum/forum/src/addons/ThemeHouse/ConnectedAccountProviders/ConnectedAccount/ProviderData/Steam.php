<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use XF;
use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Steam
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Steam extends AbstractProviderData
{
    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('personaname');
    }

    /**
     * @param null $key
     * @param string $method
     * @param null $endpoint
     * @return mixed|null
     * @throws GuzzleException
     */
    public function requestFromEndpoint($key = null, $method = 'GET', $endpoint = null)
    {
        $endpoint = $endpoint ?: $this->getDefaultEndpoint();

        if ($value = $this->requestFromCache($endpoint, $key)) {
            return $value;
        }

        $storageState = $this->storageState;
        $data = $storageState->retrieveProviderData();

        if ($data && $endpoint == $this->getDefaultEndpoint()) {
            if ($key === null) {
                $value = $data;
            } else {
                $value = isset($data[$key]) ? $data[$key] : null;
            }
            $this->storeInCache($endpoint, $value, $key);
            return $value;
        }

        $provider = $storageState->getProvider();
        $handler = $provider->handler;

        try {
            $config = $handler->getOAuthConfig($provider);
            $config['storageType'] = $storageState->getStorageType();

            $response = XF::app()->http()->client()->request('GET',
                'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/', [
                    'query' => [
                        'key' => $config['key'],
                        'format' => 'json',
                        'steamids' => $this->getProviderKey()
                    ]
                ]);

            $userdata = json_decode($response->getBody()->getContents(), true)['response']['players'];
            $data = array_shift($userdata);

            $this->storeInCache($endpoint, $data);
            if ($endpoint == $this->getDefaultEndpoint()) {
                $storageState->storeProviderData($data);
            }
            return $this->requestFromCache($endpoint, $key);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'users';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        $storageState = $this->storageState;
        return $storageState->getProviderToken()->getAccessToken();
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function getAvatarUrl()
    {
        return $this->requestFromEndpoint('avatarfull');
    }

    /**
     * @return mixed|null
     * @throws GuzzleException
     */
    public function getProfileLink()
    {
        return $this->requestFromEndpoint('profileurl');
    }
}
