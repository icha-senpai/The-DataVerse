<?php

namespace ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;
use XF\Entity\ConnectedAccountProvider;

/**
 * Class Imgur
 * @package ThemeHouse\ConnectedAccountProviders\ConnectedAccount\ProviderData
 */
class Miniorange extends AbstractProviderData
{
    protected function getSubdomain(): string
    {
        /** @var ConnectedAccountProvider $client */
        $client = \XF::em()->find('XF:ConnectedAccountProvider', 'th_cap_miniorange');
        return $client->options['subdomain'];
    }

    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'https://' . $this->getSubdomain() . '/moas/rest/oauth/getuserinfo';
    }

    public function getData()
    {
        return $this->requestFromEndpoint();
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('guid');
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('uid');
    }

    public function getEmail()
    {
        return $this->requestFromEndpoint('email');
    }

    public function getDob()
    {
        $dob = $this->requestFromEndpoint('dob');
        if ($dob) {
            return $this->prepareBirthday(date('Y-m-d', strtotime($dob)), 'y-m-d');
        }

        return null;
    }
}
