<?php

namespace App\Modules\Authorization;

use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            Contracts\AuthorizationInterface::class,
            Services\AuthorizationService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
