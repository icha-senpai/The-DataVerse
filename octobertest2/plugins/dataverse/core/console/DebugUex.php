<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Dataverse\Core\Classes\UexApiClient;

class DebugUex extends Command
{
    protected $name = 'uex:debug';
    protected $description = 'Dump raw UEX API payload for debugging.';

    public function handle()
    {
        $api = new UexApiClient;
        $data = $api->fetch('commodities_status');

        // Pretty-print JSON so it’s readable
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo PHP_EOL;
    }
}
