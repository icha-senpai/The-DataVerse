<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Dataverse\Core\Classes\UexApiClient;
use Dataverse\Core\Models\{
    Commodity,
    Price,
    StarSystem,
    Planet,
    City,
    Outpost,
    SpaceStation,
    Poi,
    Terminal
};

/**
 * Console command: uex:import-all
 *
 * Fetches all data from the UEX 2.0 API and stores it in local tables.
 * Respects the 10 requests per minute API limit (7-second delay between calls).
 * Logs all actions and results to storage/logs/system.log.
 */
class ImportUexAll extends Command
{
    protected $signature = 'uex:import-all';
    protected $description = 'Imports all UEX 2.0 API data (commodities, locations, prices, etc.) into the database';
    protected int $delay = 7;

    public function handle(): int
    {
        $api = new UexApiClient();
        $start = now();

        $this->banner('Starting full UEX import');
        Log::info("[UEX Import] Starting full UEX import at {$start}");

        try {
            $this->importSimple('star_systems', StarSystem::class, $api);
            $this->importSimple('planets', Planet::class, $api);
            $this->importSimple('cities', City::class, $api);
            $this->importSimple('outposts', Outpost::class, $api);
            $this->importSimple('space_stations', SpaceStation::class, $api);
            $this->importSimple('poi', Poi::class, $api);
            $this->importSimple('terminals', Terminal::class, $api);
            $this->importSimple('commodities', Commodity::class, $api);

            $this->importPrices($api);

            $end = now();
            $this->banner('✅ All UEX data imported successfully!');
            Log::info("[UEX Import] Completed successfully at {$end}");
        } catch (\Throwable $e) {
            Log::error('[UEX Import] Exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->error('Import failed: '.$e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function importSimple(string $endpoint, string $model, UexApiClient $api): void
    {
        $this->info("Fetching {$endpoint} ...");
        Log::info("[UEX Import] Fetching {$endpoint}");

        $data = $api->fetch($endpoint);
        if (empty($data)) {
            $this->warn("No data returned for {$endpoint}");
            Log::warning("[UEX Import] No data returned for {$endpoint}");
            return;
        }

        $count = 0;
        foreach ($data as $row) {
            $id = $row['id'] ?? null;
            if (!$id) continue;

            $model::updateOrCreate(['id' => $id], $row);
            $count++;
        }

        $this->line(" → Imported {$count} records for {$endpoint}");
        Log::info("[UEX Import] Imported {$count} records for {$endpoint}");
    }

    protected function importPrices(UexApiClient $api): void
    {
        $this->info('Fetching commodity prices (respecting rate limit)...');
        Log::info('[UEX Import] Fetching commodity prices...');

        $total = 0;
        foreach (Commodity::all() as $commodity) {
            $encoded = urlencode($commodity->name);
            $endpoint = "commodities_prices?commodity_name={$encoded}";
            $prices = $api->fetch($endpoint);

            if (empty($prices)) {
                $this->warn(" × No price data for {$commodity->name}");
                Log::warning("[UEX Import] No price data for {$commodity->name}");
                continue;
            }

            foreach ($prices as $p) {
                Price::updateOrCreate(
                    [
                        'commodity_id' => $commodity->id,
                        'terminal_id'  => $p['id_terminal'] ?? null,
                    ],
                    [
                        'price_buy'  => $p['price_buy'] ?? null,
                        'price_sell' => $p['price_sell'] ?? null,
                        'fetched_at' => Carbon::now(),
                    ]
                );
            }

            $count = count($prices);
            $total += $count;
            $this->line(" ↳ {$commodity->name}: {$count} entries");
            Log::info("[UEX Import] Imported {$count} price entries for {$commodity->name}");
            sleep($this->delay);
        }

        $this->info("Stored {$total} price rows total.");
        Log::info("[UEX Import] Stored {$total} price rows total.");
    }

    protected function banner(string $text): void
    {
        $this->line(str_repeat('-', 40));
        $this->info($text);
        $this->line(str_repeat('-', 40));
    }
}
