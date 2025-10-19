<?php namespace Dataverse\Core;

use Backend;
use System\Classes\PluginBase;

/**
 * Core plugin for The DataVerse.
 *
 * This lightweight helper ensures Tailor properly registers
 * all blueprint navigation (like your Wiki section) on boot.
 */
class Plugin extends PluginBase
{
    /**
     * Provides plugin metadata.
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Core',
            'description' => 'Core system utilities and Tailor fixes for The DataVerse.',
            'author'      => 'Dataverse',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Called when the plugin is first registered.
     */
    public function register()
    {
    // Register the artisan command
    $this->registerConsoleCommand(
        'dataverse.rebuildnav',
        'Dataverse\Core\Console\RebuildNav'
    );
    }

    /**
     * Called right before the request route.
     * This ensures Tailor navigation (Wiki, etc.) always registers properly.
     */
    public function boot()
    {
        // Make sure Tailor is available before calling
        if (class_exists('\Tailor\Classes\BlueprintIndexer')) {
            \Tailor\Classes\BlueprintIndexer::instance()->indexNavigation();
        }
    }

    /**
     * Frontend components (not used right now).
     */
    public function registerComponents()
    {
        return [];
    }

    /**
     * Backend permissions (not used right now).
     */
    public function registerPermissions()
    {
        return [];
    }

    /**
     * Backend navigation (not needed — Tailor handles its own menus).
     */
    public function registerNavigation()
    {
        return [
            'wiki' => [
                'label'       => 'Wiki',
                'icon'        => 'icon-book',
                'order'       => 150,
                // optional: lock it down later with your own perms
                'permissions' => [],

                'sideMenu'    => [
                    'wiki-entries' => [
                        'label'       => 'Entries',
                        'icon'        => 'icon-file-text',
                        'url'         => Backend::url('tailor/entries/wiki_entry'),
                    ],
                    'wiki-categories' => [
                        'label'       => 'Categories',
                        'icon'        => 'icon-folder',
                        'url'         => Backend::url('tailor/entries/wiki_category'),
                    ],
                    'wiki-tags' => [
                        'label'       => 'Tags',
                        'icon'        => 'icon-tags',
                        'url'         => Backend::url('tailor/entries/wiki_tag'),
                    ],
                    'wiki-sources' => [
                        'label'       => 'Sources',
                        'icon'        => 'icon-link',
                        'url'         => Backend::url('tailor/entries/wiki_source'),
                    ],
                    'wiki-revisions' => [
                        'label'       => 'Revisions',
                        'icon'        => 'icon-history',
                        'url'         => Backend::url('tailor/entries/wiki_revision'),
                    ],
                ],
            ],
        ];
    }
}