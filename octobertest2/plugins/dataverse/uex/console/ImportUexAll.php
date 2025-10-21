<?php namespace Dataverse\Uex\Console;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Dataverse\Uex\Classes\UexApiClient;
use Dataverse\Uex\Models\{
    Commodity, Price,
    StarSystem, Planet, City,
    Outpost, SpaceStation, Poi, Terminal
};

/**
 * Smart importer for all UEX 2.0 API data (systems, locations, commodities, vehicles, prices, and status).
 * - Uses Bearer auth from .env
 * - Respects rate limits
 * - Only updates changed data (via timestamp/hash)
 */
class ImportUexAll extends Command
{
    protected $name = 'uex:import-all';
    protected $description = 'Smart import of all UEX 2.0 API data, only updating changed rows.';

    protected UexApiClient $api;

    public function handle()
    {
        $this->info("\n----------------------------------------");
        $this->info("🚀 Starting smart UEX import (delta mode)");
        $this->info("----------------------------------------");

        $this->api = new UexApiClient();

        try {
            $this->importVehicles();
            $this->importStarSystems();
            $this->importPlanets();
            $this->importCities();
            $this->importOutposts();
            $this->importSpaceStations();
            $this->importPois();
            $this->importTerminals();
            $this->importCommodities();
            $this->importPrices();
            $this->importCommodityStatus();
        } catch (\Throwable $e) {
            $this->error("❌ Import failed: " . $e->getMessage());
            Log::error('[UEX ImportAll] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 1;
        }

        $this->info("\n✅ Smart import completed successfully!");
        return 0;
    }

    /* -----------------------------------------------------
     * SMART UPDATE HELPERS
     * ----------------------------------------------------- */

    protected function shouldUpdate($model, array $data): bool
    {
        // 1. Compare modification timestamps if available
        if (isset($data['date_modified']) && $model->date_modified ?? null) {
            return strtotime($data['date_modified']) > strtotime($model->date_modified);
        }

        // 2. Compare hash if no timestamp field
        $newHash = hash('sha256', json_encode($data));
        if (!isset($model->last_hash) || $model->last_hash !== $newHash) {
            $model->last_hash = $newHash;
            return true;
        }

        return false;
    }

    protected function syncModel($class, $data)
    {
        $inserted = $updated = $skipped = 0;

        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            if (!$id) continue;

            $model = $class::find($id);

            if (!$model) {
                $item['last_hash'] = hash('sha256', json_encode($item));
                $class::create($item);
                $inserted++;
            } else {
                if ($this->shouldUpdate($model, $item)) {
                    $model->fill($item)->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        }

        return [$inserted, $updated, $skipped];
    }

    /* -----------------------------------------------------
     * INDIVIDUAL IMPORT SECTIONS
     * ----------------------------------------------------- */

    protected function importVehicles()
    {
        $this->info('Fetching vehicles...');
        $data = $this->unwrap($this->api->fetch('vehicles'));
        [$inserted, $updated, $skipped] = $this->syncModel(\Dataverse\Uex\Models\Vehicle::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importStarSystems()
    {
        $this->info('Fetching star_systems...');
        $data = $this->unwrap($this->api->fetch('star_systems'));
        [$inserted, $updated, $skipped] = $this->syncModel(StarSystem::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importPlanets()
    {
        $this->info('Fetching planets...');
        $data = $this->unwrap($this->api->fetch('planets'));
        [$inserted, $updated, $skipped] = $this->syncModel(Planet::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importCities()
    {
        $this->info('Fetching cities...');
        $data = $this->unwrap($this->api->fetch('cities'));
        [$inserted, $updated, $skipped] = $this->syncModel(City::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importOutposts()
    {
        $this->info('Fetching outposts...');
        $data = $this->unwrap($this->api->fetch('outposts'));
        [$inserted, $updated, $skipped] = $this->syncModel(Outpost::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importSpaceStations()
    {
        $this->info('Fetching space_stations...');
        $data = $this->unwrap($this->api->fetch('space_stations'));
        [$inserted, $updated, $skipped] = $this->syncModel(SpaceStation::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importPois()
    {
        $this->info('Fetching points of interest...');
        $data = $this->unwrap($this->api->fetch('poi'));
        [$inserted, $updated, $skipped] = $this->syncModel(Poi::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importTerminals()
    {
        $this->info('Fetching terminals...');
        $data = $this->unwrap($this->api->fetch('terminals'));
        [$inserted, $updated, $skipped] = $this->syncModel(Terminal::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importCommodities()
    {
        $this->info('Fetching commodities...');
        $data = $this->unwrap($this->api->fetch('commodities'));
        [$inserted, $updated, $skipped] = $this->syncModel(Commodity::class, $data);
        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importPrices()
    {
        $this->info('Fetching commodity prices (bulk)...');
        $data = $this->unwrap($this->api->fetch('commodities_prices_all'));
        $inserted = $updated = $skipped = 0;

        foreach ($data as $p) {
            $record = Price::where([
                'commodity_id' => $p['id_commodity'] ?? null,
                'terminal_id'  => $p['id_terminal'] ?? null
            ])->first();

            if (!$record) {
                Price::create([
                    'commodity_id' => $p['id_commodity'] ?? null,
                    'terminal_id'  => $p['id_terminal'] ?? null,
                    'price_buy'    => $p['price_buy'] ?? null,
                    'price_sell'   => $p['price_sell'] ?? null,
                    'location'     => $p['terminal_name'] ?? null,
                    'fetched_at'   => Carbon::now(),
                    'last_hash'    => hash('sha256', json_encode($p))
                ]);
                $inserted++;
            } else {
                $newHash = hash('sha256', json_encode($p));
                if ($record->last_hash !== $newHash) {
                    $record->fill([
                        'price_buy'  => $p['price_buy'] ?? null,
                        'price_sell' => $p['price_sell'] ?? null,
                        'location'   => $p['terminal_name'] ?? null,
                        'fetched_at' => Carbon::now(),
                        'last_hash'  => $newHash
                    ])->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        }

        $this->line(" → +{$inserted} new, ✎{$updated} updated, ⏸{$skipped} skipped");
    }

    protected function importCommodityStatus()
    {
        $this->info('Fetching commodity status legend...');

        $response = $this->api->fetch('commodities_status');
        $data = $response['data'] ?? $response ?? [];

        if (!isset($data['buy']) || !isset($data['sell'])) {
            $this->warn('  ⚠️ No valid status data received.');
            Log::warning('[UEX ImportAll] Unexpected commodities_status payload', ['payload' => $response]);
            return;
        }

        $this->line('  🧮 Syncing BUY table...');
        [$insertedBuy, $updatedBuy, $skippedBuy] = $this->syncStatusTable('uex_commodities_status_buy', $data['buy']);

        $this->line('  🧮 Syncing SELL table...');
        [$insertedSell, $updatedSell, $skippedSell] = $this->syncStatusTable('uex_commodities_status_sell', $data['sell']);

        $this->line(
            " → BUY: +{$insertedBuy}, ✎{$updatedBuy}, ⏸{$skippedBuy} | " .
            "SELL: +{$insertedSell}, ✎{$updatedSell}, ⏸{$skippedSell}"
        );
    }

    protected function syncStatusTable(string $table, array $rows): array
    {
        $inserted = $updated = $skipped = 0;

        foreach ($rows as $r) {
            $code = $r['code'] ?? null;
            if ($code === null) continue;

            $existing = DB::table($table)->where('code', $code)->first();
            $newHash  = hash('sha256', json_encode($r));

            if (!$existing) {
                DB::table($table)->insert([
                    'code'             => $r['code'] ?? null,
                    'name'             => $r['name'] ?? null,
                    'name_short'       => $r['name_short'] ?? null,
                    'name_abbr'        => $r['name_abbr'] ?? null,
                    'percentage'       => $r['percentage'] ?? null,
                    'percentage_start' => $r['percentage_start'] ?? null,
                    'percentage_end'   => $r['percentage_end'] ?? null,
                    'colors'           => $r['colors'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                    'last_hash'        => $newHash,
                ]);
                $inserted++;
            } else {
                if (!isset($existing->last_hash) || $existing->last_hash !== $newHash) {
                    DB::table($table)
                        ->where('code', $code)
                        ->update([
                            'name'             => $r['name'] ?? null,
                            'name_short'       => $r['name_short'] ?? null,
                            'name_abbr'        => $r['name_abbr'] ?? null,
                            'percentage'       => $r['percentage'] ?? null,
                            'percentage_start' => $r['percentage_start'] ?? null,
                            'percentage_end'   => $r['percentage_end'] ?? null,
                            'colors'           => $r['colors'] ?? null,
                            'updated_at'       => now(),
                            'last_hash'        => $newHash,
                        ]);
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        }

        return [$inserted, $updated, $skipped];
    }

    /* -----------------------------------------------------
     * HELPER
     * ----------------------------------------------------- */

    protected function unwrap($response)
    {
        if (is_array($response)) {
            if (isset($response['data']) && is_array($response['data'])) {
                return $response['data'];
            }
            if (isset($response[0])) {
                return $response;
            }
        }
        return [];
    }
}
