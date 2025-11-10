<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ClearAllCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-all
                            {--optimize : Re-optimize after clearing}
                            {--force : Force clear without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all application caches (config, route, view, cache, filament, blade-icons, bootstrap)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 Starting cache clearing process...');
        $this->newLine();

        // Confirm in production
        if ($this->laravel->environment('production') && !$this->option('force')) {
            if (!$this->confirm('You are in PRODUCTION. Are you sure you want to clear all caches?')) {
                $this->warn('❌ Cache clearing cancelled.');
                return self::FAILURE;
            }
        }

        $clearCommands = [
            'cache:clear' => '💾 Application cache',
            'config:clear' => '⚙️  Configuration cache',
            'route:clear' => '🛣️  Route cache',
            'view:clear' => '👁️  View cache',
            'filament:clear-cached-components' => '🎨 Filament components cache',
        ];

        // Clear all caches
        foreach ($clearCommands as $command => $description) {
            $this->clearCache($command, $description);
        }

        // Clear blade icons cache if exists
        if ($this->commandExists('icons:clear')) {
            $this->clearCache('icons:clear', '🎭 Blade icons cache');
        }

        // Clear bootstrap cache files
        $this->clearBootstrapCache();

        // Clear Laravel Settings cache
        $this->clearLaravelSettingsCache();

        $this->newLine();
        $this->info('✅ All caches cleared successfully!');

        // Re-optimize if requested
        if ($this->option('optimize')) {
            $this->newLine();
            $this->info('🔄 Re-optimizing application...');
            $this->optimizeApplication();
        }

        $this->newLine();
        $this->info('🎉 Done!');

        return self::SUCCESS;
    }

    /**
     * Clear a specific cache with progress indicator
     */
    protected function clearCache(string $command, string $description): void
    {
        try {
            $this->components->task($description, function () use ($command) {
                Artisan::call($command, [], $this->getOutput());
                return true;
            });
        } catch (\Exception $e) {
            $this->components->warn("Failed to run: {$command}");
            $this->error("Error: {$e->getMessage()}");
        }
    }

    /**
     * Clear bootstrap cache directory
     */
    protected function clearBootstrapCache(): void
    {
        $this->components->task('🗂️  Bootstrap cache files', function () {
            $bootstrapCachePath = base_path('bootstrap/cache');

            if (!File::exists($bootstrapCachePath)) {
                return true;
            }

            $files = File::files($bootstrapCachePath);
            $deleted = 0;

            foreach ($files as $file) {
                // Don't delete .gitignore
                if ($file->getFilename() === '.gitignore') {
                    continue;
                }

                try {
                    File::delete($file->getPathname());
                    $deleted++;
                } catch (\Exception $e) {
                    // Continue on error
                }
            }

            return $deleted > 0;
        });
    }

    /**
     * Clear Laravel Settings cache
     */
    protected function clearLaravelSettingsCache(): void
    {
        if (!$this->commandExists('settings:clear-cache')) {
            return;
        }

        $this->clearCache('settings:clear-cache', '⚙️  Laravel settings cache');
    }

    /**
     * Re-optimize the application
     */
    protected function optimizeApplication(): void
    {
        $optimizeCommands = [
            'config:cache' => '⚙️  Configuration',
            'route:cache' => '🛣️  Routes',
            'view:cache' => '👁️  Views',
        ];

        foreach ($optimizeCommands as $command => $description) {
            $this->components->task("Caching {$description}", function () use ($command) {
                Artisan::call($command, [], $this->getOutput());
                return true;
            });
        }

        // Run optimize command
        $this->components->task('🚀 Running optimize', function () {
            Artisan::call('optimize', [], $this->getOutput());
            return true;
        });
    }

    /**
     * Check if a command exists
     */
    protected function commandExists(string $command): bool
    {
        try {
            return collect(Artisan::all())->has($command);
        } catch (\Exception $e) {
            return false;
        }
    }
}
