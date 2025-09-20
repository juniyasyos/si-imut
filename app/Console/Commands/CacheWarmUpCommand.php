<?php

namespace App\Console\Commands;

use App\Services\Cache\CacheManager;
use Illuminate\Console\Command;

class CacheWarmUpCommand extends Command
{
    protected $signature = 'cache:warm-up {--force : Force warm-up even if cache is healthy}';
    protected $description = 'Warm up application caches with frequently accessed data';

    public function handle(CacheManager $cacheManager): int
    {
        $this->info('Starting cache warm-up process...');

        $result = $cacheManager->warmUp();

        if ($result['success']) {
            $this->info("Cache warm-up completed successfully in {$result['duration_seconds']} seconds");

            $this->table(
                ['Service', 'Status'],
                collect($result['results'])->map(fn($status, $service) => [
                    $service,
                    $status ? '✅ Success' : '❌ Failed'
                ])->toArray()
            );

            return Command::SUCCESS;
        } else {
            $this->error("Cache warm-up failed: {$result['error']}");
            return Command::FAILURE;
        }
    }
}
