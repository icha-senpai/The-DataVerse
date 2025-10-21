<?php namespace Dataverse\Core;

use Backend;
use System\Classes\PluginBase;
use Illuminate\Support\Facades\Log;

/**
 * Core plugin for The DataVerse.
 *
 * Houses core system utilities, UEX import commands,
 * and Tailor blueprint registration fixes.
 */
class Plugin extends PluginBase
{
    /**
     * Plugin metadata displayed in the backend.
     */
    public function pluginDetails(): array
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
     * Used to register console commands and services.
     */
    public function register(): void
    {
        // Register all custom console commands
        $this->registerConsoleCommand(
            'uex.importall',
            \Dataverse\Core\Console\ImportUexAll::class
        );

        $this->registerConsoleCommand(
            'dataverse.rebuildnav',
            \Dataverse\Core\Console\RebuildNav::class
        );
        $this->registerConsoleCommand(
            'plugin.bump',
            \Dataverse\Core\Console\BumpVersion::class
        );
        $this->registerConsoleCommand(
            'plugin.bump-smart',
             \Dataverse\Core\Console\BumpVersionSmart::class
        );
    
        $this->registerConsoleCommand(
            'uex.schema-scan',
            \Dataverse\Core\Console\UexSchemaScan::class
        );
    
        $this->registerConsoleCommand(
            'uex.schema-diff',
            \Dataverse\Core\Console\UexSchemaDiff::class
        );
    
        $this->registerConsoleCommand(
            'dataverse:schemaverse',
            \Dataverse\Core\Console\SchemaVerse::class
        );
    
        $this->registerConsoleCommand(
            'uex.debug',
            \Dataverse\Core\Console\DebugUex::class
        );
    }

    /**
     * Called right before the request route.
     * Ensures Tailor blueprints (e.g., Wiki) always appear in navigation.
     */
    public function boot(): void
    {
        if (class_exists('\Tailor\Classes\BlueprintIndexer')) {
            try {
                \Tailor\Classes\BlueprintIndexer::instance()->indexNavigation();
                Log::info('[Dataverse\Core] Tailor navigation re-indexed successfully.');
            } catch (\Throwable $e) {
                Log::error('[Dataverse\Core] Tailor index failed: '.$e->getMessage());
            }
        } else {
            Log::warning('[Dataverse\Core] Tailor not installed — skipping blueprint re-index.');
        }
    }

    /**
     * Frontend components (none yet).
     */
    public function registerComponents(): array
    {
        return [];
    }

    /**
     * Backend permissions (optional for future use).
     */
    public function registerPermissions(): array
    {
        return [];
    }

    /**
     * Backend navigation — preloads Wiki navigation to match Tailor structure.
     */
    public function registerNavigation(): array
    {
        return [
            'wiki' => [
                'label'       => 'Wiki',
                'icon'        => 'icon-book',
                'order'       => 150,
                'permissions' => [],
                'sideMenu'    => [
                    'entries' => [
                        'label' => 'Entries',
                        'icon'  => 'icon-file-text',
                        'url'   => Backend::url('tailor/entries/wiki_entry'),
                    ],
                    'categories' => [
                        'label' => 'Categories',
                        'icon'  => 'icon-folder',
                        'url'   => Backend::url('tailor/entries/wiki_category'),
                    ],
                    'tags' => [
                        'label' => 'Tags',
                        'icon'  => 'icon-tags',
                        'url'   => Backend::url('tailor/entries/wiki_tag'),
                    ],
                    'sources' => [
                        'label' => 'Sources',
                        'icon'  => 'icon-link',
                        'url'   => Backend::url('tailor/entries/wiki_source'),
                    ],
                    'revisions' => [
                        'label' => 'Revisions',
                        'icon'  => 'icon-history',
                        'url'   => Backend::url('tailor/entries/wiki_revision'),
                    ],
                ],
            ],
        ];
    }
}
