<?php

namespace App\Console\Commands;

use App\Services\Cache\CacheManager;
use Illuminate\Console\Command;

class CacheHealthCheckCommand extends Command
{
    protected $signature = 'cache:health-check {--json : Output in JSON format}';
    protected $description = 'Check the health status of all cache services';

    public function handle(CacheManager $cacheManager): int
    {
        $this->info('Checking cache health status...');

        $status = $cacheManager->getHealthStatus();

        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        // Display formatted health status
        $this->displayHealthStatus($status);

        return $status['overall_status'] === 'healthy' ? Command::SUCCESS : Command::FAILURE;
    }

    private function displayHealthStatus(array $status): void
    {
        $statusIcon = match($status['overall_status']) {
            'healthy' => '✅',
            'degraded' => '⚠️',
            'unhealthy' => '❌',
            default => '❓'
        };

        $this->info("Overall Status: {$statusIcon} " . ucfirst($status['overall_status']));
        $this->newLine();

        // Display store status
        if (!empty($status['stores'])) {
            $this->info('Cache Stores:');
            foreach ($status['stores'] as $store => $storeStatus) {
                $icon = $storeStatus['status'] === 'healthy' ? '✅' : '❌';
                $this->line("  {$icon} {$store}: " . ucfirst($storeStatus['status']));

                if (isset($storeStatus['response_time'])) {
                    $this->line("    Response time: {$storeStatus['response_time']}ms");
                }

                if (isset($storeStatus['record_count'])) {
                    $this->line("    Records: {$storeStatus['record_count']}");
                }
            }
            $this->newLine();
        }

        // Display service status
        if (!empty($status['services'])) {
            $this->info('Cache Services:');
            foreach ($status['services'] as $service => $serviceStatus) {
                $icon = $serviceStatus['status'] === 'healthy' ? '✅' : '❌';
                $this->line("  {$icon} {$service}: " . ucfirst($serviceStatus['status']));
            }
            $this->newLine();
        }

        // Display metrics
        if (!empty($status['metrics']) && !isset($status['metrics']['error'])) {
            $this->info('Cache Metrics:');
            $this->line("  Total keys: {$status['metrics']['total_keys']}");
            $this->line("  Memory usage: {$status['metrics']['memory_usage']}");
            $this->line("  Hit rate: {$status['metrics']['hit_rate']}%");
        }
    }
}
