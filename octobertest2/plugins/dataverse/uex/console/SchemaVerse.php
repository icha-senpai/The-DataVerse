<?php namespace Dataverse\Uex\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Dataverse\Uex\Classes\UexApiClient;
use Symfony\Component\Console\Input\InputOption;

/**
 * 🌌 SchemaVerse — Universal UEX Data Mapper
 *
 * Now with auto-pagination: will fetch all pages from an endpoint if metadata or next links exist.
 */
class SchemaVerse extends Command
{
    protected $name = 'dataverse:schemaverse';
    protected $description = '🌌 Maps UEX API schemas, auto-generates/merges migrations & models safely.';

    protected UexApiClient $api;
    protected string $logFile;

    public function handle()
    {
        $this->api = new UexApiClient();
        $this->logFile = storage_path('logs/schemaverse_summary.json');

        $this->info("\n=============================================");
        $this->info("🌌  SchemaVerse — UEX Data Dimension Mapper (Merge-Aware)");
        $this->info("=============================================\n");

        $sleep    = (int) $this->option('sleep');
        $generate = (bool) $this->option('generate');
        $dryRun   = (bool) $this->option('dry-run');
        $saveJson = (bool) $this->option('save-json');
        $merge    = (bool) $this->option('merge');
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
                'commodities_status' => 'Commodity status data',
            ];
        }

        $this->line("→ Delay between requests: {$sleep}s");
        $this->line("→ Dry run: " . ($dryRun ? 'YES' : 'no'));
        $this->line("→ Merge mode: " . ($merge ? 'YES' : 'no'));
        if ($generate && !$dryRun)
            $this->warn("→ Generation ENABLED (with merge=" . ($merge ? 'YES' : 'no') . ")\n");

        $summary = [];

        foreach ($endpoints as $endpoint => $desc) {
            $this->line("\n🛰️  Scanning: {$desc}");

            // Auto-pagination fetch
            $raw = $this->fetchAllPages($endpoint, $sleep);
            $records = $this->unwrapDeep($raw);

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
            $schema = $this->extractSchemaRecursive($sample);

            $summary[$endpoint] = [
                'count'  => is_countable($records) ? count($records) : 1,
                'schema' => $schema,
            ];

            $schemaPath = storage_path("logs/schema_struct_{$endpoint}.json");
            file_put_contents($schemaPath, json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("  💾 Structured schema saved: {$schemaPath}");

            if ($dryRun) {
                $this->warn("  ⚠️ Dry-run active: skipping generation/merge");
                continue;
            }

            // Flatten nested schema for database columns
            $flatKeys = $this->flattenForMigration($schema);

            if ($generate && !$merge) {
                $this->generateMigrationAndModel($endpoint, $flatKeys);
            }

            if ($generate && $merge) {
                $this->mergeMigrationAndModel($endpoint, $flatKeys);
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
            $chunk = $this->unwrapDeep($data);

            if (empty($chunk)) break;

            if (is_array($chunk) && array_is_list($chunk)) {
                $results = array_merge($results, $chunk);
            } else {
                $results[] = $chunk;
                break;
            }

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
    protected function unwrapDeep($payload)
    {
        $seen = [];
        $depth = 0;
        while (is_array($payload)) {
            $keys = array_keys($payload);
            $hash = md5(json_encode($keys));
            if (in_array($hash, $seen) || $depth > 15) break;
            $seen[] = $hash;
            $depth++;

            if (count($keys) === 1 && isset($payload['data']) && is_array($payload['data'])) {
                $payload = $payload['data'];
                continue;
            }
            if (count($keys) === 1 && isset($payload['Data']) && is_array($payload['Data'])) {
                $payload = $payload['Data'];
                continue;
            }
            if (count($keys) === 1 && isset($payload['payload']) && is_array($payload['payload'])) {
                $payload = $payload['payload'];
                continue;
            }
            if (count($keys) === 1 && is_array(reset($payload)) && array_is_list(reset($payload))) {
                $payload = reset($payload);
                continue;
            }

            break;
        }

        if (is_array($payload) && !array_is_list($payload)) {
            $payload = [$payload];
        }

        return $payload;
    }

    /* =========================
       STRUCTURED SCHEMA
       ========================= */

    protected function extractSchemaRecursive($data)
    {
        if (!is_array($data)) {
            return gettype($data);
        }

        $schema = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_keys($value) === range(0, count($value) - 1)) {
                    $first = $value[0] ?? null;
                    $schema[$key] = [
                        'type' => 'array',
                        'items' => $this->extractSchemaRecursive($first)
                    ];
                } else {
                    $schema[$key] = [
                        'type' => 'object',
                        'properties' => $this->extractSchemaRecursive($value)
                    ];
                }
            } else {
                $schema[$key] = gettype($value);
            }
        }

        return $schema;
    }

    protected function flattenForMigration($schema, string $prefix = ''): array
    {
        $fields = [];
        if (is_array($schema)) {
            foreach ($schema as $k => $v) {
                $name = $prefix ? "{$prefix}_{$k}" : $k;
                if (is_array($v) && isset($v['type'])) {
                    if ($v['type'] === 'object' && isset($v['properties'])) {
                        $fields = array_merge($fields, $this->flattenForMigration($v['properties'], $name));
                    } elseif ($v['type'] === 'array' && isset($v['items'])) {
                        $fields = array_merge($fields, $this->flattenForMigration($v['items'], "{$name}_item"));
                    } else {
                        $fields[] = $name;
                    }
                } else {
                    $fields[] = $name;
                }
            }
        }
        return array_unique($fields);
    }

    /* =========================
       TYPE INFERENCE + MIGRATION + MERGE
       ========================= */

    protected function inferColumnLine(string $col): string
    {
        if (preg_match('/_id$/', $col)) {
            return "            \$t->unsignedInteger('{$col}')->nullable();";
        } elseif (preg_match('/date|time|timestamp/i', $col)) {
            return "            \$t->timestamp('{$col}')->nullable();";
        } elseif (preg_match('/price|amount|value|percent|ratio|quantity|avg|min|max|scu|mass|width|height|length|fuel/i', $col)) {
            return "            \$t->decimal('{$col}', 12, 4)->nullable();";
        } elseif (preg_match('/^is_|^has_|flag|bool/i', $col)) {
            return "            \$t->boolean('{$col}')->nullable();";
        } elseif (preg_match('/^url_|^uri_|_url$|_uri$/i', $col)) {
            return "            \$t->text('{$col}')->nullable();";
        } else {
            return "            \$t->string('{$col}', 255)->nullable();";
        }
    }

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

        $columns = array_map(fn($k) => $this->inferColumnLine($k), $keys);

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

        $fillable = implode("', '", $keys);
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

    protected function mergeMigrationAndModel(string $endpoint, array $keys): void
    {
        $table     = 'uex_' . Str::snake(str_replace(['/', '?', '=', '&'], '_', $endpoint));
        $modelName = Str::studly(Str::camel(str_replace('uex_', '', $table)));
        $modelsDir = base_path('plugins/dataverse/core/models');
        $updatesDir = base_path('plugins/dataverse/core/updates');

        if (!is_dir($modelsDir)) mkdir($modelsDir, 0775, true);
        if (!is_dir($updatesDir)) mkdir($updatesDir, 0775, true);

        $modelFile = "{$modelsDir}/{$modelName}.php";

        if (!file_exists($modelFile)) {
            $this->warn("  ℹ️ Model {$modelName} not found — generating fresh.");
            $this->generateMigrationAndModel($endpoint, $keys);
            return;
        }

        $existing = file_get_contents($modelFile) ?: '';
        $existingFillable = $this->extractFillable($existing);

        $newFields = $keys;
        $missingFields = array_values(array_diff($newFields, $existingFillable));

        if (!empty($missingFields)) {
            $this->line("  ➕ Appending " . count($missingFields) . " new fillables to {$modelName}...");
            $updatedContent = $this->injectFillable($existing, array_merge($existingFillable, $missingFields));
            file_put_contents($modelFile, $updatedContent);
            $this->info("  🧬 Model updated: {$modelFile}");
        } else {
            $this->line("  ✅ Model fillables already cover API fields.");
        }

        if (!Schema::hasTable($table)) {
            $this->warn("  ⚠️ Table {$table} does not exist — generating fresh migration.");
            $this->generateMigrationAndModel($endpoint, $keys);
            return;
        }

        $missingCols = [];
        foreach ($newFields as $col) {
            if (!Schema::hasColumn($table, $col)) {
                $missingCols[] = $col;
            }
        }

        if (empty($missingCols)) {
            $this->line("  ✅ No new DB columns needed for {$table}.");
            return;
        }

        $timestamp = date('Y_m_d_His');
        $patchFile = "{$updatesDir}/add_columns_{$table}_{$timestamp}.php";

        $columnLines = array_map(fn($c) => $this->inferColumnLine($c), $missingCols);
        $dropLines   = array_map(fn($c) => "            \$t->dropColumn('{$c}');", $missingCols);

        $patch = <<<PHP
<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('{$table}')) {
            Schema::table('{$table}', function (Blueprint \$t) {
{$this->indentLines($columnLines)}
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('{$table}')) {
            Schema::table('{$table}', function (Blueprint \$t) {
{$this->indentLines($dropLines)}
            });
        }
    }
};
PHP;

        file_put_contents($patchFile, $patch);
        $this->info("  🧱 Add-columns migration generated: {$patchFile}");
        $this->warn("  👉 Run: php artisan plugin:bump-smart Dataverse.Core");
    }

    protected function extractFillable(string $content): array
    {
        $re = '/protected\s+\$fillable\s*=\s*\[(.*?)\];/s';
        if (!preg_match($re, $content, $m)) return [];
        $inside = $m[1];
        preg_match_all("/['\"]([^'\"]+)['\"]/", $inside, $mm);
        return array_values(array_unique($mm[1] ?? []));
    }

    protected function injectFillable(string $content, array $allFields): string
    {
        sort($allFields);
        $formatted = "'" . implode("', '", $allFields) . "'";
        $re = '/protected\s+\$fillable\s*=\s*\[(.*?)\];/s';
        if (preg_match($re, $content)) {
            return preg_replace($re, "protected \$fillable = [{$formatted}];", $content);
        }
        return preg_replace('/class\s+[^\{]+\{/', "\$0\n    protected \$fillable = [{$formatted}];\n", $content, 1);
    }

    protected function indentLines(array $lines): string
    {
        return implode("\n", $lines);
    }

    protected function getOptions()
    {
        return [
            ['sleep', null, InputOption::VALUE_OPTIONAL, 'Seconds to wait between requests', 6],
            ['generate', null, InputOption::VALUE_NONE, 'Generate migrations and models'],
            ['merge', null, InputOption::VALUE_NONE, 'Merge into existing model/table (append-only)'],
            ['dry-run', null, InputOption::VALUE_NONE, 'Extract schema only, do not write files'],
            ['save-json', null, InputOption::VALUE_NONE, 'Save raw API JSON responses for inspection'],
            ['endpoint', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of API endpoints to scan'],
        ];
    }
}
