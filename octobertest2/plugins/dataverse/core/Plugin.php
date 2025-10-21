<?php namespace Dataverse\Core;

use System\Classes\PluginBase;

/**
 * Dataverse Core
 *
 * Base system utilities, helpers, and service registration.
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Core',
            'description' => 'Core system utilities and base classes for The DataVerse.',
            'author'      => 'Dataverse',
            'icon'        => 'icon-leaf'
        ];
    }

    public function register(): void
    {
        // Only universal commands stay here (like version bumpers)
        $this->registerConsoleCommand('plugin.bump', \Dataverse\Core\Console\BumpVersion::class);
        $this->registerConsoleCommand('plugin.bump-smart', \Dataverse\Core\Console\BumpVersionSmart::class);
    }
}
