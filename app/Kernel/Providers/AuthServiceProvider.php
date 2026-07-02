<?php

namespace App\Kernel\Providers;

use App\Models\DailyReportResponse;
use App\Policies\ActivityPolicy;
use App\Policies\DailyReportResponsePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        DailyReportResponse::class => DailyReportResponsePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Ambil model dari config
        $folderModel = config('media-manager.model.folder');
        $mediaModel = config('media-manager.model.media');

        // Daftarkan policy custom ke model dari config
        if (class_exists(\App\Providers\FolderCustomPolicy::class)) {
            Gate::policy($folderModel, \App\Providers\FolderCustomPolicy::class);
        }
        if (class_exists(\App\Providers\MediaCustomPolicy::class)) {
            Gate::policy($mediaModel, \App\Providers\MediaCustomPolicy::class);
        }
    }
}
