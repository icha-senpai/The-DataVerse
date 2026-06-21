<?php namespace Dataverse\Api;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = ['Dataverse.Core'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'API',
            'description' => 'UEX data endpoints for Dataverse theme integrations.',
            'author'      => 'Dataverse',
            'icon'        => 'icon-exchange'
        ];
    }
}
