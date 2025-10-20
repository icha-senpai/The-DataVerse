<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Dataverse\Core\Classes\UexApiClient;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UexSchemaDiff extends Command
{
    protected $signature = 'uex:schema-diff {--generate : Generate migrations for missing fields with type inference}';
    protected $description = 'Compares UEX API fields with your DB schema and auto-generates typed migrations for missing columns.';

    protected array $endpointToTable = [
        'star_systems'   => 'uex_star_systems',
        'planets'        => 'uex_planets',
        'cities'         => 'uex_cities',
        'outposts'       => 'uex_outposts',
        'space_stations' => 'uex_space_stations',
        'poi'            => 'uex_poi',
        'terminals'      => 'uex_terminals',
        'commodities'    => 'uex_commodities',
        'vehicles'       => 'uex_vehicles',
    ];

    public function handle()
    {
        $api = new UexApiClient();

        $this->line(str_repeat('-', 70));
        $this->info('UEX API → Database Schema Diff (with type inference)');
        $this->line(str_repeat('-', 70));

        foreach ($this->endpointToTable as $endpoint => $table) {
            $this->line("\n🔍 Comparing {$endpoint} → {$table}");

            if (!Schema::hasTable($table)) {
                $this->error(" ✖ Table {$table} does not exist.");
                continue;
            }

            $data = $api->fetch($endpoint);
            if (empty($data)) {
                $this->warn(" ⚠ No data returned from API — skipping comparison.");
                continue;
            }

            $sample = $data[0];
            $apiFields = array_keys($sample);
            $dbFields  = Schema::getColumnListing($table);

            sort($apiFields);
            sort($dbFields);

            $missingInDb = array_values(array_diff($apiFields, $dbFields));
            $extraInDb   = array_values(array_diff($dbFields, $apiFields));
            $matching    = array_values(array_intersect($apiFields, $dbFields));

            $this->info(" ✅ Matching: " . count($matching));
            if ($missingInDb) {
                $this->warn(" 🟡 Missing in DB (" . count($missingInDb) . "): " . implode(', ', $missingInDb));
            }
            if ($extraInDb) {
                $this->error(" 🔴 Extra in DB (" . count($extraInDb) . "): " . implode(', ', $extraInDb));
            }

            Log::info("[UEX SchemaDiff] {$endpoint}", [
                'matching' => $matching,
                'missing_in_db' => $missingInDb,
                'extra_in_db' => $extraInDb,
            ]);

            if ($this->option('generate') && !empty($missingInDb)) {
                $this->generateMigration($table, $missingInDb, $sample);
            }
        }

        $this->line(str_repeat('-', 70));
        $this->info('Schema diff complete. See storage/logs/system.log for full details.');
        $this->line(str_repeat('-', 70));
    }

    /**
     * Generates a migration file with inferred column types.
     */
    protected function generateMigration(string $table, array $missingFields, array $sample): void
    {
        $timestamp = date('Y_m_d_His');
        $fileName  = "{$timestamp}_add_missing_fields_" . Str::slug($table, '_') . ".php";
        $path      = base_path("plugins/dataverse/core/updates/{$fileName}");

        $this->info(" 🧱 Generating migration with inferred types: {$fileName}");

        $columns = "";
        foreach ($missingFields as $field) {
            $type = $this->inferColumnType($field, $sample[$field] ?? null);
            $columns .= "            \$table->{$type}('{$field}')->nullable();\n";
        }

        $content = <<<PHP
<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::table('{$table}', function (Blueprint \$table) {
{$columns}        });
    }

    public function down()
    {
        Schema::table('{$table}', function (Blueprint \$table) {
            // \$table->dropColumn([...]);
        });
    }
};
PHP;

        File::put($path, $content);
        $this->info(" ✅ Migration created: plugins/dataverse/core/updates/{$fileName}");
    }

    /**
     * Infer the Laravel column type based on value and name.
     */
    protected function inferColumnType(string $field, $value): string
    {
        $lower = strtolower($field);

        // Infer based on name prefix
        if (str_starts_with($lower, 'is_')) return 'boolean';
        if (str_starts_with($lower, 'has_')) return 'boolean';
        if (str_ends_with($lower, '_id')) return 'unsignedInteger';

        // Infer based on value type
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'float';
        if (is_bool($value)) return 'boolean';

        // Numeric strings (like "42" or "120.5")
        if (is_string($value) && is_numeric($value)) {
            return (str_contains($value, '.') ? 'float' : 'integer');
        }

        // JSON-looking arrays or lists
        if (is_array($value)) return 'json';

        // Default fallback
        return 'string';
    }
}
