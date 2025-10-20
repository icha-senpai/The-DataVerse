<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

/**
 * Auto-bumps the plugin version in version.yaml.
 * Example:
 *   php artisan plugin:bump Dataverse.Core
 */
class BumpVersion extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'plugin:bump {plugin : Plugin code, e.g. Dataverse.Core}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically bumps the version.yaml for a given plugin (e.g., from 1.0.1 to 1.0.2).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pluginCode = $this->argument('plugin');
        $path = base_path('plugins/' . strtolower(str_replace('.', '/', $pluginCode)) . '/updates/version.yaml');

        if (!File::exists($path)) {
            $this->error("version.yaml not found at: {$path}");
            return Command::FAILURE;
        }

        $yaml = Yaml::parseFile($path);
        $versions = array_keys($yaml);
        $currentVersion = end($versions);

        if (!preg_match('/^\d+\.\d+\.\d+$/', $currentVersion)) {
            $this->error("Invalid version format: {$currentVersion}");
            return Command::FAILURE;
        }

        [$major, $minor, $patch] = explode('.', $currentVersion);
        $newVersion = "{$major}.{$minor}." . ($patch + 1);

        $yaml[$newVersion] = [
            "Auto-bumped version from {$currentVersion} to {$newVersion}"
        ];

        File::put($path, Yaml::dump($yaml, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
        $this->info("✅ Bumped {$pluginCode} from {$currentVersion} → {$newVersion}");

        return Command::SUCCESS;
    }
}
