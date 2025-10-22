<?php

namespace ThemeHouse\ConnectedAccountProviders\XF\Entity;

/**
 * Class ConnectedAccountProvider
 * @package ThemeHouse\ConnectedAccountProviders\XF\Entity
 */
class ConnectedAccountProvider extends XFCP_ConnectedAccountProvider
{
    /**
     * @return bool
     */
    public function isThCapProvider()
    {
        return mb_strpos($this->provider_id, 'th_cap_') === 0;
    }
}
