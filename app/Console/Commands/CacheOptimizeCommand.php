<?php

namespace App\Console\Commands;

use App\Services\Cache\CacheManager;
use Illuminate\Console\Command;

class CacheOptimizeCommand extends Command
{
    protected $signature = 'cache:optimize {--warm-up : Also warm up caches after optimization}';
    protected $description = 'Optimize cache performance by cleaning up and reorganizing data';

    public function handle(CacheManager $cacheManager): int
    {
        $this->info('Starting cache optimization...');

        $result = $cacheManager->optimize();

        if ($result['success']) {
            $this->info('Cache optimization completed successfully');

            // Display optimization results
            if (isset($result['results']['redis_cleanup'])) {
                $cleanup = $result['results']['redis_cleanup'];
                if (!isset($cleanup['error'])) {
                    $this->line("Redis cleanup: {$cleanup['keys_deleted']} keys deleted, {$cleanup['memory_freed']} freed");
                }
            }

            if (isset($result['results']['warm_up']['success']) && $result['results']['warm_up']['success']) {
                $warmUp = $result['results']['warm_up'];
                $this->line("Cache warm-up: completed in {$warmUp['duration_seconds']} seconds");
            }

            return Command::SUCCESS;
        } else {
            $this->error("Cache optimization failed: {$result['error']}");
            return Command::FAILURE;
        }
    }
}
