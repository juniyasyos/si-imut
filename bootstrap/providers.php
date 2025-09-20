<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\LaporanImutServiceProvider::class,
    App\Providers\UnitKerjaProvider::class,
    App\Providers\FactoryServiceProvider::class,
    App\Providers\BusinessLogicServiceProvider::class,
    App\Providers\CacheServiceProvider::class,
    App\Providers\OptimizationServiceProvider::class,
    SocialiteProviders\Manager\ServiceProvider::class,
];
