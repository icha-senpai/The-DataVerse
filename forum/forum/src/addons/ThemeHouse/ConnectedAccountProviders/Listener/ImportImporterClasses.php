<?php

namespace ThemeHouse\ConnectedAccountProviders\Listener;

use XF\Container;
use XF\SubContainer\Import;

class ImportImporterClasses
{
    public static function importImporterClasses(Import $container, Container $parentContainer, array &$importers)
    {
        $importers[] = 'ThemeHouse\ConnectedAccountProviders:ConnectedAccountProvider';
    }
}
