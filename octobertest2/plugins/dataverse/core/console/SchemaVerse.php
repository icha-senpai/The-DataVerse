<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Dataverse\Core\Classes\UexApiClient;
use Symfony\Component\Console\Input\InputOption;

/**
 * 🌌 SchemaVerse — Universal UEX Data Mapper
 *
 * Now with auto-pagination: will fetch all pages from an endpoint if metadata or next links exist.
 */
class SchemaVerse extends Command
{
    protected $name = 'dataverse:schemaverse';
    protected $description = '🌌 Maps and explores any UEX API endpoint schema (Dataverse SchemaVerse)';

    protected UexApiClient $api;
    protected string $logFile;

    public function handle()
    {
        $this->api = new UexApiClient();
        $this->logFile = storage_path('logs/schemaverse_summary.json');

        $this->info("\n=============================================");
        $this->info("🌌  SchemaVerse — UEX Data Dimension Mapper");
        $this->info("=============================================\n");

        $sleep    = (int) $this->option('sleep');
        $generate = (bool) $this->option('generate');
        $dryRun   = (bool) $this->option('dry-run');
        $saveJson = (bool) $this->option('save-json');
        $inputEPs = $this->option('endpoint');

        // Manual endpoint input
        if ($inputEPs) {
            $endpoints = [];
            foreach (explode(',', $inputEPs) as $ep) {
                $ep = trim($ep);
                $endpoints[$ep] = "Manual endpoint: {$ep}";
            }
        } else {
            $endpoints = [
                'commodities_prices_all'     => 'All refined commodities prices',
                'commodities_raw_prices_all' => 'All raw material prices',
                'commodities_status'         => 'Commodity status data',
                'vehicles'                   => 'Vehicle data',
            ];
        }

        $this->line("→ Delay between requests: {$sleep}s");
        $this->line("→ Dry run: " . ($dryRun ? 'YES' : 'no'));
        if ($generate) $this->warn("→ Auto-generation of migrations/models is ENABLED.\n");

        $summary = [];

        foreach ($endpoints as $endpoint => $desc) {
            $this->line("\n🛰️  Scanning: {$desc}");

            // Auto-pagination fetch
            $raw = $this->fetchAllPages($endpoint, $sleep);
            $records = $this->unwrap($raw);

            if ($saveJson) {
                $path = storage_path("logs/schemaverse_raw_{$endpoint}.json");
                file_put_contents($path, json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->info("  💾 Raw JSON saved: {$path}");
            }

            if (empty($records)) {
                $this->warn("  ⚠️  No usable data returned from {$endpoint}");
                continue;
            }

            $sample = is_array($records) && isset($records[0]) ? $records[0] : $records;
            $keys = $this->extractKeysRecursive($sample);

            $summary[$endpoint] = [
                'count'  => is_countable($records) ? count($records) : 1,
                'fields' => $keys,
            ];

            $this->info("  ✅ Found " . count($keys) . " unique fields:");
            foreach ($keys as $key) {
                $this->line("     • {$key}");
            }

            if ($dryRun) {
                $this->warn("  ⚠️ Dry-run active: skipping migration/model generation");
                continue;
            }

            if ($generate) {
                $this->generateMigrationAndModel($endpoint, $keys);
            }

            sleep($sleep);
        }

        file_put_contents($this->logFile, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("\n🌠 Scan complete. Results saved to: {$this->logFile}\n");
    }

    /**
     * Automatically detects and fetches paginated results across all pages.
     */
    protected function fetchAllPages(string $endpoint, int $delay): array
    {
        $page = 1;
        $results = [];

        while (true) {
            $this->line("   🔁 Fetching page {$page}...");
            $data = $this->api->fetch("{$endpoint}?page={$page}");
            $chunk = $this->unwrap($data);

            if (empty($chunk)) break;

            // Merge arrays only if same structure
            if (is_array($chunk) && array_is_list($chunk)) {
                $results = array_merge($results, $chunk);
            } else {
                $results[] = $chunk;
                break; // stop if single-object response
            }

            // detect pagination meta or next link
            $hasNext = false;
            if (isset($data['meta']['total_pages']) && isset($data['meta']['current_page'])) {
                $hasNext = $data['meta']['current_page'] < $data['meta']['total_pages'];
            } elseif (isset($data['links']['next']) && !empty($data['links']['next'])) {
                $hasNext = true;
            }

            if (!$hasNext) break;
            $page++;
            sleep($delay);
        }

        return $results;
    }

    /**
     * Recursively unwrap nested "data" keys until an array of records is reached.
     */
    protected function unwrap($payload)
    {
        $depth = 0;
        while (is_array($payload) && array_key_exists('data', $payload)) {
            $payload = $payload['data'];
            $depth++;
            if ($depth > 10) break; // safety stop
        }

        if (is_array($payload) && count($payload) === 1 && is_array(reset($payload))) {
            $payload = reset($payload);
        }

        return $payload;
    }

    /**
     * Recursively extract flattened dotted keys from arrays/objects.
     */
    protected function extractKeysRecursive($data, string $prefix = ''): array
    {
        $keys = [];
        if (!is_array($data)) return $keys;

        foreach ($data as $k => $v) {
            $full = $prefix ? "{$prefix}.{$k}" : (string)$k;
            $keys[] = $full;
            if (is_array($v) && !empty($v)) {
                $keys = array_merge($keys, $this->extractKeysRecursive($v, $full));
            }
        }

        // Normalize numeric paths
        $keys = array_map(fn($k) => preg_replace('/\.\d+(\.|$)/', '.', $k), $keys);
        $keys = array_unique(array_map(fn($k) => trim($k, '.'), $keys));

        return array_values($keys);
    }

    /**
     * Generate migration + model files for the given endpoint.
     */
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

        $columns = [];
        foreach ($keys as $col) {
            $col = str_replace('.', '_', $col);
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

        $fillable = implode("', '", array_map(fn($k) => str_replace('.', '_', $k), $keys));
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

    protected function indentLines(array $lines): string
    {
        return implode("\n", $lines);
    }

    protected function getOptions()
    {
        return [
            ['sleep', null, InputOption::VALUE_OPTIONAL, 'Seconds to wait between requests', 6],
            ['generate', null, InputOption::VALUE_NONE, 'Generate migrations and models for discovered schemas'],
            ['dry-run', null, InputOption::VALUE_NONE, 'Extract schema only, do not generate anything'],
            ['save-json', null, InputOption::VALUE_NONE, 'Save raw API JSON responses for inspection'],
            ['endpoint', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of API endpoints to scan'],
        ];
    }
}
