<?php

namespace ThemeHouse\ConnectedAccountProviders\OAuth\Common\Http\Uri;

class Uri extends \OAuth\Common\Http\Uri\Uri
{
    /**
     * @param string $var
     * @param string $val
     */
    public function addToQuery($var, $val): void
    {
        $query = $this->getQuery();
        if (strlen($query) > 0) {
            $query .= '&';
        }

        $query .= http_build_query([$var => $val], '', '&', PHP_QUERY_RFC3986);

        $this->setQuery($query);
    }
}