<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class FilamentBackupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->shouldRegisterBackupPackage()) {
            return;
        }

        $this->app->register(\Juniyasyos\FilamentLaravelBackup\FilamentLaravelBackupServiceProvider::class);
    }

    protected function shouldRegisterBackupPackage(): bool
    {
        try {
            return Schema::hasTable('backup_configuration');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
