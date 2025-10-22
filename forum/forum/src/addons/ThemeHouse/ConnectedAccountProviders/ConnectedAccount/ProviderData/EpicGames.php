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
class EpicGames extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'https://api.epicgames.dev/epic/oauth/v1/userInfo';
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->requestFromEndpoint('preferred_username');
    }

    /**
     * @return string
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('sub');
    }
}
