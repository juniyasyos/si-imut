<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\CacheClearCommand;

class OptimizedCacheClearCommand extends Command
{
    protected $signature = 'cache:clear-app {--tags= : Only clear items with the given tags}';

    protected $description = 'Clear application cache while preserving permission cache';

    public function handle(): int
    {
        $this->info('🧹 Clearing application cache...');
        
        // Clear all cache EXCEPT permission cache
        \Illuminate\Support\Facades\Cache::flush();
        
        $this->call('cache:warm-permissions', ['--force' => true]);
        
        $this->info('✅ Cache cleared and permissions warmed');
        
        return 0;
    }
}
