<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Dataverse\Core\Classes\UexApiClient;
use Dataverse\Core\Models\{
    Commodity, Price,
    StarSystem, Planet, City,
    Outpost, SpaceStation, Poi, Terminal
};

class ImportUexAll extends Command
{
    protected $name = 'uex:import-all';
    protected $description = 'Imports all UEX 2.0 API data (commodities, locations, prices, etc.) into the database';
    protected int $delay = 7;

    public function handle()
    {
        $api = new UexApiClient();

        $this->banner("Starting full UEX import");

        $this->importSimple('star_systems', StarSystem::class, $api);
        $this->importSimple('planets', Planet::class, $api);
        $this->importSimple('cities', City::class, $api);
        $this->importSimple('outposts', Outpost::class, $api);
        $this->importSimple('space_stations', SpaceStation::class, $api);
        $this->importSimple('poi', Poi::class, $api);
        $this->importSimple('terminals', Terminal::class, $api);
        $this->importSimple('commodities', Commodity::class, $api);

        $this->importPrices($api);

        $this->banner("✅ Import complete");
    }

    /** Generic importer for endpoints that map 1:1 to tables */
    protected function importSimple(string $endpoint, string $model, UexApiClient $api): void
    {
        $this->info("Fetching {$endpoint} ...");
        $data = $api->fetch($endpoint);
        if (empty($data)) {
            $this->warn("No data returned for {$endpoint}");
            return;
        }

        foreach ($data as $row) {
            $id = $row['id'] ?? null;
            if (!$id) continue;
            $model::updateOrCreate(['id' => $id], $row);
        }

        $this->line(" → Imported " . count($data) . " records for {$endpoint}");
    }

    /** Fetch commodity prices for all commodities */
    protected function importPrices(UexApiClient $api): void
    {
        $this->info('Fetching commodity prices (respecting 10/min rate limit)...');
        $total = 0;

        foreach (Commodity::all() as $commodity) {
            $encoded = urlencode($commodity->name);
            $endpoint = "commodities_prices?commodity_name={$encoded}";
            $prices = $api->fetch($endpoint);

            if (empty($prices)) {
                $this->warn(" × No price data for {$commodity->name}");
                continue;
            }

            foreach ($prices as $p) {
                Price::updateOrCreate(
                    [
                        'commodity_id' => $commodity->id,
                        'terminal_id'  => $p['id_terminal'] ?? null,
                    ],
                    [
                        'price_buy' => $p['price_buy'] ?? null,
                        'price_sell'=> $p['price_sell'] ?? null,
                        'fetched_at'=> Carbon::now(),
                    ]
                );
            }

            $count = count($prices);
            $total += $count;
            $this->line(" ↳ {$commodity->name}: {$count} entries");
        }

        $this->info("Stored {$total} price rows total.");
    }

    protected function banner(string $text): void
    {
        $this->line(str_repeat('-', 40));
        $this->info($text);
        $this->line(str_repeat('-', 40));
    }
}
