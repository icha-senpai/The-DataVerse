<?php namespace Dataverse\Uex;

use System\Classes\PluginBase;
use Illuminate\Support\Facades\Route;

/**
 * Dataverse UEX Systems
 *
 * Handles UEX schema imports, scanners, diff tools,
 * and provides the front-end Tabulator component.
 */
class Plugin extends PluginBase
{
    public $require = ['Dataverse.Core'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'UEX',
            'description' => 'UEX import, schema tools, and front-end components for The DataVerse.',
            'author'      => 'Dataverse',
            'icon'        => 'icon-database'
        ];
    }

    public function register(): void
    {
        // Register all UEX-related console commands
        $this->registerConsoleCommand('uex.importall',        \Dataverse\Uex\Console\ImportUexAll::class);
        $this->registerConsoleCommand('uex.schema-scan',      \Dataverse\Uex\Console\UexSchemaScan::class);
        $this->registerConsoleCommand('uex.schema-diff',      \Dataverse\Uex\Console\UexSchemaDiff::class);
        $this->registerConsoleCommand('dataverse:schemaverse', \Dataverse\Uex\Console\SchemaVerse::class);
        $this->registerConsoleCommand('uex.debug',            \Dataverse\Uex\Console\DebugUex::class);
    }

    public function registerComponents(): array
    {
        return [
            \Dataverse\Uex\Components\UexCommodities::class => 'uexCommodities'
        ];
    }

    public function boot(): void
    {
        // Register a direct route for Tabulator to call
        Route::get('/uex/data', [\Dataverse\Uex\Components\UexCommodities::class, 'onGetData']);
    }
}
