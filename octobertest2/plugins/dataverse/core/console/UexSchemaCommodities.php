<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Dataverse\Core\Classes\UexApiClient;
use Symfony\Component\Console\Input\InputOption;

/**
 * Scans the UEX commodity-related API endpoints and generates migrations/models
 * for all returned data structures.
 *
 * Does NOT loop over individual commodities or terminals — this version
 * only handles top-level UEX endpoints like:
 *  - commodities_prices_all
 *  - commodities_raw_prices_all
 *  - commodities_status
 */
class UexSchemaCommodities extends Command
{
    protected $name = 'uex:schema-commodities';
    protected $description = 'Scans UEX commodity endpoints, extracts schema fields, and optionally generates migrations/models.';

    protected UexApiClient $api;
    protected string $logFile;

    public function handle()
    {
        $this->api = new UexApiClient();
        $this->logFile = storage_path('logs/uex_schema_commodities.json');

        $this->info("\n=============================================");
        $this->info("🔍 UEX Commodity Schema Scanner (Core Mode)");
        $this->info("=============================================\n");

        $sleep    = (int) $this->option('sleep');
        $generate = (bool) $this->option('generate');

        $this->line("→ Delay between requests: {$sleep}s");
        if ($generate) $this->warn("→ Auto-generation of migrations/models is ENABLED.\n");

        $summary = [];

        $endpoints = [
            'commodities_prices_all'     => 'All refined commodities prices',
            'commodities_raw_prices_all' => 'All raw material prices',
            'commodities_status'         => 'Status legend (buy/sell levels)',
        ];

        foreach ($endpoints as $endpoint => $desc) {
            $this->line("\n➡️ {$desc}");
            $data    = $this->api->fetch($endpoint);
            $records = $data['data'] ?? $data ?? [];

            if (empty($records)) {
                $this->warn("  No data returned from {$endpoint}");
                continue;
            }

            $first = (is_array($records) && isset($records[0])) ? $records[0] : $records;
            $keys  = $this->extractKeysRecursive($first);

            $summary[$endpoint] = [
                'count'  => is_countable($records) ? count($records) : 1,
                'fields' => $keys,
            ];

            $this->info("  Found " . count($keys) . " fields:");
            foreach ($keys as $key) {
                $this->line("    - {$key}");
            }

            if ($generate) {
                $this->generateMigrationAndModel($endpoint, $keys);
            }

            sleep($sleep);
        }

        file_put_contents($this->logFile, json_encode($summary, JSON_PRETTY_PRINT));
        $this->info("\n✅ Scan complete. Results saved to: {$this->logFile}\n");
    }

    /** Recursively extracts dotted keys from nested arrays/objects */
    protected function extractKeysRecursive($data, string $prefix = ''): array
    {
        $keys = [];
        if (!is_array($data)) return $keys;

        foreach ($data as $k => $v) {
            $full = $prefix ? "{$prefix}.{$k}" : (string)$k;
            $keys[] = $full;
            if (is_array($v)) {
                $keys = array_merge($keys, $this->extractKeysRecursive($v, $full));
            }
        }
        return array_values(array_unique($keys));
    }

    /** Generates a migration + model for the endpoint using defensive key handling */
    protected function generateMigrationAndModel(string $endpoint, array $keys): void
    {
        $table     = 'uex_' . Str::snake(str_replace(['/', '?', '=', '&'], '_', $endpoint));
        $modelName = Str::studly(Str::camel(str_replace('uex_', '', $table)));

        $updatesDir = base_path('plugins/dataverse/core/updates');
        $modelsDir  = base_path('plugins/dataverse/core/models');
        if (!is_dir($updatesDir)) mkdir($updatesDir, 0775, true);
        if (!is_dir($modelsDir))  mkdir($modelsDir, 0775, true);

        $migrationFile = "{$updatesDir}/create_{$table}_table.php";
        $modelFile     = "{$modelsDir}/{$modelName}.php";

        // Flatten keys defensively
        $flatKeys = [];
        $flatten = function ($input) use (&$flatten, &$flatKeys) {
            if (is_array($input)) {
                foreach ($input as $k => $v) {
                    if (is_string($k) && $k !== '') $flatKeys[] = $k;
                    $flatten($v);
                }
            } elseif (is_string($input) && $input !== '') {
                $flatKeys[] = $input;
            }
        };
        $flatten($keys);
        $flatKeys = array_values(array_unique(array_map('strval', array_filter($flatKeys))));

        // Infer column types
        $columns = [];
        foreach ($flatKeys as $col) {
            if (preg_match('/_id$/', $col)) {
                $columns[] = "            \$t->unsignedInteger('{$col}')->nullable();";
            } elseif (preg_match('/date|time|timestamp/i', $col)) {
                $columns[] = "            \$t->timestamp('{$col}')->nullable();";
            } elseif (preg_match('/price|amount|value|percent|ratio|quantity|avg|min|max/i', $col)) {
                $columns[] = "            \$t->decimal('{$col}', 12, 4)->nullable();";
            } elseif (preg_match('/^is_|^has_|flag|bool/i', $col)) {
                $columns[] = "            \$t->boolean('{$col}')->nullable();";
            } elseif (preg_match('/^url_|^uri_|_url$|_uri$/i', $col)) {
                $columns[] = "            \$t->text('{$col}')->nullable();";
            } else {
                $columns[] = "            \$t->string('{$col}', 255)->nullable();";
            }
        }

        // Write migration
        $migration = <<<PHP
<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('{$table}', function (Blueprint \$t) {
            \$t->increments('id');
{$this->indentLines($columns)}
            \$t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
        file_put_contents($migrationFile, $migration);
        $this->info("  ✨ Migration generated: {$migrationFile}");

        // Write model
        $fillable = implode("', '", $flatKeys);
        $model = <<<PHP
<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class {$modelName} extends Model
{
    use Validation;

    protected \$table = '{$table}';
    protected \$guarded = ['*'];
    protected \$fillable = ['{$fillable}'];
    public \$rules = [];
}
PHP;
        file_put_contents($modelFile, $model);
        $this->info("  🧩 Model generated: {$modelFile}");
    }

    /** pretty-print helper for migration columns */
    protected function indentLines(array $lines): string
    {
        return implode("\n", $lines);
    }

    protected function getOptions()
    {
        return [
            ['sleep', null, InputOption::VALUE_OPTIONAL, 'Seconds to wait between requests', 6],
            ['generate', null, InputOption::VALUE_NONE, 'Generate migrations and models for discovered schemas'],
        ];
    }
}
