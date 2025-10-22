<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use Jose\Component\Signature\JWS;
use XF\ConnectedAccount\ProviderData\AbstractProviderData;

class Apple extends AbstractProviderData
{
    public function getDefaultEndpoint()
    {
        return 'thconnectedaccounts_appleFakeEndpoint';
    }

    public function getProviderKey()
    {
        return $this->requestFromEndpoint('sub');
    }

    public function getUsername()
    {
        return $this->requestFromEndpoint('name');
    }

    public function getEmail()
    {
        return $this->requestFromEndpoint('email');
    }

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
            /** @var \ThemeHouse\ConnectedAccountProviders\ConnectedAccount\Service\Apple $oAuth */
            $oAuth = $handler->getOAuth($config);
            $token = $oAuth->getStorage()->retrieveAccessToken($oAuth->service());
            $tokenString = $token->getAccessToken();
            $extra = $token->getExtraParams();

            /** @var array $data */
            $data = $this->getAppleRepo()->decodeIdToken($tokenString);
            $data['name'] = '';

            try {
                if (!empty($extra['user']) && property_exists($extra['user'], 'name')) {
                    $data['name'] = $extra['user']->name->firstName . ' ' . $extra['user']->name->lastName;
                }
            } catch (\Exception $e) {}

            $this->storeInCache($endpoint, $data);
            if ($endpoint == $this->getDefaultEndpoint()) {
                $storageState->storeProviderData($data);
            }

            return $this->requestFromCache($endpoint, $key);
        } catch(\Exception $e) {
            return null;
        }
    }

    /**
     * @return \XF\Mvc\Entity\Repository|\ThemeHouse\ConnectedAccountProviders\Repository\Apple
     */
    protected function getAppleRepo()
    {
        return \XF::app()->repository('ThemeHouse\ConnectedAccountProviders:Apple');
    }
}