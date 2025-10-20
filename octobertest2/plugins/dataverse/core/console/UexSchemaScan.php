<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Dataverse\Core\Classes\UexApiClient;
use Illuminate\Support\Facades\Log;

class UexSchemaScan extends Command
{
    protected $name = 'uex:schema-scan';
    protected $description = 'Scans all UEX endpoints and lists the available field names for each category.';

    protected array $endpoints = [
        'star_systems',
        'planets',
        'cities',
        'outposts',
        'space_stations',
        'poi',
        'terminals',
        'commodities',
        'vehicles',
    ];

    public function handle()
    {
        $api = new UexApiClient();

        $this->line(str_repeat('-', 50));
        $this->info('UEX API Schema Scanner');
        $this->line(str_repeat('-', 50));

        foreach ($this->endpoints as $endpoint) {
            $this->line("\n🔍 Scanning {$endpoint}...");
            $data = $api->fetch($endpoint);

            if (empty($data)) {
                $this->warn(" → No data returned or empty array.");
                continue;
            }

            $first = $data[0] ?? [];
            $keys = array_keys($first);

            if (empty($keys)) {
                $this->warn(" → Data has no visible keys.");
                continue;
            }

            sort($keys);
            $this->info(" → Found " . count($keys) . " fields:");
            foreach ($keys as $key) {
                $this->line("    - {$key}");
            }

            Log::info("[UEX Schema] {$endpoint}: " . implode(', ', $keys));
        }

        $this->line(str_repeat('-', 50));
        $this->info('Schema scan complete. Field names logged to storage/logs/system.log');
        $this->line(str_repeat('-', 50));
    }
}
