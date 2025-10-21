<?php namespace Dataverse\Nav;

use System\Classes\PluginBase;

/**
 * Dataverse Navigation Manager
 *
 * Contains nav rebuilders, utilities, and console tools
 * for regenerating OctoberCMS navigation structures.
 */
class Plugin extends PluginBase
{
    public $require = ['Dataverse.Core'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'Navigation',
            'description' => 'Navigation rebuilders and utilities for The DataVerse.',
            'author'      => 'Dataverse',
            'icon'        => 'icon-sitemap'
        ];
    }

    public function register(): void
    {
        // Register nav-related console commands
        $this->registerConsoleCommand(
            'dataverse.rebuildnav',
            \Dataverse\Nav\Console\RebuildNav::class
        );
    }
}
