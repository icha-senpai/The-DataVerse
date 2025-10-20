<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Fully automated version bumper and migrator.
 *
 * 1. Bumps version.yaml to the next patch version.
 * 2. Detects any new migration files in /updates and adds them to version.yaml.
 * 3. Immediately runs the new migrations via artisan.
 *
 * Usage:
 *   php artisan plugin:bump-smart Dataverse.Core
 */
class BumpVersionSmart extends Command
{
    protected $signature = 'plugin:bump-smart {plugin : Plugin code, e.g. Dataverse.Core}';
    protected $description = 'Bumps version.yaml, adds new migrations, and runs them automatically.';

    public function handle(): int
    {
        $pluginCode = $this->argument('plugin');
        $pluginPath = base_path('plugins/' . strtolower(str_replace('.', '/', $pluginCode)));
        $versionFile = $pluginPath . '/updates/version.yaml';
        $updatesPath = $pluginPath . '/updates';

        if (!File::exists($versionFile)) {
            $this->error("version.yaml not found: {$versionFile}");
            return Command::FAILURE;
        }

        if (!File::exists($updatesPath)) {
            $this->error("updates folder not found: {$updatesPath}");
            return Command::FAILURE;
        }

        $yaml = Yaml::parseFile($versionFile);
        $versions = array_keys($yaml);
        $currentVersion = end($versions);

        if (!preg_match('/^\d+\.\d+\.\d+$/', $currentVersion)) {
            $this->error("Invalid version format: {$currentVersion}");
            return Command::FAILURE;
        }

        [$major, $minor, $patch] = explode('.', $currentVersion);
        $newVersion = "{$major}.{$minor}." . ($patch + 1);

        $existingFiles = collect($yaml)
            ->flatten()
            ->filter(fn ($v) => is_string($v) && str_ends_with($v, '.php'))
            ->toArray();

        $allFiles = collect(File::files($updatesPath))
            ->map(fn ($f) => $f->getFilename())
            ->filter(fn ($name) => str_ends_with($name, '.php'))
            ->values()
            ->toArray();

        $newFiles = array_values(array_diff($allFiles, $existingFiles));

        $yaml[$newVersion] = array_merge(
            ["Auto-bumped from {$currentVersion} to {$newVersion}"],
            $newFiles
        );

        File::put($versionFile, Yaml::dump($yaml, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

        $this->info("✅ Bumped {$pluginCode} from {$currentVersion} → {$newVersion}");
        if (!empty($newFiles)) {
            $this->info("Detected new migration files:");
            foreach ($newFiles as $file) {
                $this->line(" → {$file}");
            }
        } else {
            $this->warn("No new migration files detected.");
        }

        // Run the migrations immediately
        $this->line(str_repeat('-', 40));
        $this->info("Running migrations for {$pluginCode}...");
        $this->line(str_repeat('-', 40));

        $exitCode = \Artisan::call('october:migrate', ['--quiet' => true]);
        if ($exitCode === 0) {
            $this->info("🎉 Migrations completed successfully!");
        } else {
            $this->error("⚠️ Migration command exited with code {$exitCode}");
        }

        $this->line("All done → version.yaml updated and migrations applied.");
        return Command::SUCCESS;
    }
}
