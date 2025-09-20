<?php

namespace App\Console\Commands;

use App\Services\Cache\CacheManager;
use Illuminate\Console\Command;

class CacheStatsCommand extends Command
{
    protected $signature = 'cache:stats {--json : Output in JSON format}';
    protected $description = 'Display detailed cache statistics and performance metrics';

    public function handle(CacheManager $cacheManager): int
    {
        $this->info('Gathering cache statistics...');

        $stats = $cacheManager->getStatistics();

        if ($this->option('json')) {
            $this->line(json_encode($stats, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $this->displayStatistics($stats);

        return Command::SUCCESS;
    }

    private function displayStatistics(array $stats): void
    {
        if (isset($stats['error'])) {
            $this->error("Failed to gather statistics: {$stats['error']}");
            return;
        }

        $this->info('Cache Statistics');
        $this->line("Generated at: {$stats['timestamp']}");
        $this->line("Default store: {$stats['default_store']}");
        $this->newLine();

        // Store-specific statistics
        if (!empty($stats['stores'])) {
            foreach ($stats['stores'] as $store => $storeStats) {
                $this->info(ucfirst($store) . ' Store:');

                if (is_array($storeStats)) {
                    foreach ($storeStats as $key => $value) {
                        $this->line("  " . ucwords(str_replace('_', ' ', $key)) . ": {$value}");
                    }
                } else {
                    $this->line("  Status: {$storeStats}");
                }

                $this->newLine();
            }
        }

        // Hit rates
        if (!empty($stats['hit_rates'])) {
            $this->info('Hit Rates:');
            foreach ($stats['hit_rates'] as $store => $rate) {
                $icon = $rate >= 80 ? '✅' : ($rate >= 60 ? '⚠️' : '❌');
                $this->line("  {$icon} {$store}: {$rate}%");
            }
            $this->newLine();
        }

        // Performance indicators
        $this->info('Performance Indicators:');

        if (isset($stats['hit_rates']['redis'])) {
            $hitRate = $stats['hit_rates']['redis'];
            $performance = $hitRate >= 85 ? 'Excellent' :
                          ($hitRate >= 70 ? 'Good' :
                          ($hitRate >= 50 ? 'Fair' : 'Poor'));
            $this->line("  Cache efficiency: {$performance}");
        }

        if (isset($stats['stores']['redis']['used_memory_human'])) {
            $this->line("  Memory usage: {$stats['stores']['redis']['used_memory_human']}");
        }

        if (isset($stats['stores']['redis']['connected_clients'])) {
            $this->line("  Active connections: {$stats['stores']['redis']['connected_clients']}");
        }

        $this->newLine();

        // Recommendations
        $this->displayRecommendations($stats);
    }

    private function displayRecommendations(array $stats): void
    {
        $recommendations = [];

        if (isset($stats['hit_rates']['redis']) && $stats['hit_rates']['redis'] < 70) {
            $recommendations[] = 'Consider increasing cache TTL or warming up more data';
        }

        if (isset($stats['stores']['redis']['connected_clients']) &&
            $stats['stores']['redis']['connected_clients'] > 100) {
            $recommendations[] = 'High number of Redis connections - consider connection pooling';
        }

        if (!empty($recommendations)) {
            $this->info('Recommendations:');
            foreach ($recommendations as $recommendation) {
                $this->line("  💡 {$recommendation}");
            }
        } else {
            $this->info('✅ Cache performance looks good!');
        }
    }
}
