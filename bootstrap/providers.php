<?php

return [
    App\Kernel\Providers\AppServiceProvider::class,
    App\Kernel\Providers\AuthServiceProvider::class,
    App\Modules\FormEngine\FormEngineServiceProvider::class,
    App\Modules\ImutMaster\ImutMasterServiceProvider::class,
    App\Modules\DailyReport\DailyReportServiceProvider::class,
    App\Modules\Laporan\LaporanServiceProvider::class,
    App\Modules\Benchmarking\BenchmarkingServiceProvider::class,
    App\Modules\Authorization\AuthorizationServiceProvider::class,
    App\Modules\Reporting\ReportingServiceProvider::class,
    App\Providers\FilamentBackupServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\LaporanImutServiceProvider::class,
    App\Providers\UnitKerjaProvider::class,
    App\Providers\PermissionCacheProvider::class,
    SocialiteProviders\Manager\ServiceProvider::class,
];
