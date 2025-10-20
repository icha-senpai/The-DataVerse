<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Dataverse\Core\Classes\UexApiClient;
use Dataverse\Core\Models\{
    Commodity, Price, StarSystem, Planet, City,
    Outpost, SpaceStation, Poi, Terminal, Vehicle
};

class ImportUexAll extends Command
{
    protected $name = 'uex:import-all';
    protected $description = 'Imports all UEX 2.0 API data (commodities, locations, vehicles, etc.) into the database.';

    protected UexApiClient $api;

    public function handle()
    {
        $this->api = new UexApiClient();

        $this->banner("Starting full UEX import");

        $this->importStarSystems();
        $this->importPlanets();
        $this->importCities();
        $this->importOutposts();
        $this->importSpaceStations();
        $this->importPoi();
        $this->importTerminals();
        $this->importCommodities();
        $this->importVehicles();
        $this->importPrices();

        $this->banner("✅ All UEX data imported successfully!");
    }

    /* -----------------------------------------------------------
       Import Methods
    ----------------------------------------------------------- */

    protected function importStarSystems()
    {
        $this->importGeneric('star_systems', StarSystem::class);
    }

    protected function importPlanets()
    {
        $this->importGeneric('planets', Planet::class);
    }

    protected function importCities()
    {
        $this->importGeneric('cities', City::class);
    }

    protected function importOutposts()
    {
        $this->importGeneric('outposts', Outpost::class);
    }

    protected function importSpaceStations()
    {
        $this->importGeneric('space_stations', SpaceStation::class);
    }

    protected function importPoi()
    {
        $this->importGeneric('poi', Poi::class);
    }

    protected function importTerminals()
    {
        $this->importGeneric('terminals', Terminal::class);
    }

    protected function importCommodities()
    {
        $this->importGeneric('commodities', Commodity::class);
    }

    protected function importVehicles()
    {
        $this->importGeneric('vehicles', Vehicle::class);
    }

    protected function importPrices()
    {
        $this->section("Fetching commodity prices (rate-limited)");

        $commodities = Commodity::all();
        foreach ($commodities as $commodity) {
            $encoded = urlencode($commodity->name);
            $endpoint = "commodities_prices?commodity_name={$encoded}";
            $data = $this->api->fetch($endpoint);

            $records = $data['data'] ?? $data ?? [];
            foreach ($records as $row) {
                Price::updateOrCreate(
                    [
                        'commodity_id' => $commodity->id,
                        'terminal_id'  => $row['id_terminal'] ?? null,
                    ],
                    [
                        'price_buy'  => $row['price_buy'] ?? null,
                        'price_sell' => $row['price_sell'] ?? null,
                        'fetched_at' => Carbon::now(),
                    ]
                );
            }

            $this->line("   ↳ Imported prices for {$commodity->name}");
            sleep(1);
        }
    }

    /* -----------------------------------------------------------
       Helper Methods
    ----------------------------------------------------------- */

    /**
     * Imports generic endpoint → model mapping with safe unwrap + logging.
     */
    protected function importGeneric(string $endpoint, string $modelClass)
    {
        $this->section("Fetching {$endpoint} ...");

        $response = $this->api->fetch($endpoint);
        $records = $response['data'] ?? $response ?? [];

        if (empty($records)) {
            $this->warn(" → No data returned for {$endpoint}");
            Log::warning("[UEX Import] No data for {$endpoint}");
            return;
        }

        $count = 0;
        foreach ($records as $row) {
            if (!isset($row['id'])) continue;

            try {
                $modelClass::updateOrCreate(['id' => $row['id']], $row);
                $count++;
            } catch (\Exception $e) {
                Log::error("[UEX Import] Failed {$endpoint} ID {$row['id']}: {$e->getMessage()}");
            }
        }

        $this->info(" → Imported {$count} records for {$endpoint}");
        Log::info("[UEX Import] Imported {$count} records for {$endpoint}");
    }

    /**
     * Pretty console banners
     */
    protected function banner(string $text)
    {
        $this->line("\n----------------------------------------");
        $this->info($text);
        $this->line("----------------------------------------");
    }

    protected function section(string $text)
    {
        $this->line("\n" . $text);
    }
}
