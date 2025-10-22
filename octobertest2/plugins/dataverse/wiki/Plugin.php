<?php namespace Dataverse\Wiki;

use Backend;
use System\Classes\PluginBase;

/**
 * Dataverse Wiki Bridge
 *
 * Handles Tailor backend navigation integration
 * and ensures wiki blueprints are indexed in Tailor.
 */
class Plugin extends PluginBase
{
    public $require = ['Dataverse.Core'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'Wiki',
            'description' => 'Backend wiki integration and Tailor navigation hook for The DataVerse.',
            'author'      => 'Dataverse',
            'icon'        => 'icon-book'
        ];
    }

    public function boot(): void
    {
        if (class_exists('\Tailor\Classes\BlueprintIndexer')) {
            try {
                \Tailor\Classes\BlueprintIndexer::instance()->indexNavigation();
            } catch (\Throwable $e) {
                // Only log actual errors
                report($e);
            }
        }
    }

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
