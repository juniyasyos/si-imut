<?php

namespace App\Modules\Benchmarking;

use Illuminate\Support\ServiceProvider;

class BenchmarkingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            Contracts\BenchmarkingInterface::class,
            Services\BenchmarkingService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
