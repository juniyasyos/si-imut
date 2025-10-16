<?php

namespace App\Providers;

use App\Domains\Imut\Events\ImutPenilaianSubmitted;
use App\Domains\Imut\Listeners\InvalidateImutCache;
use App\Domains\Organization\Models\UnitKerja;
use App\Domains\Reporting\Events\LaporanGenerated;
use App\Domains\Reporting\Listeners\InvalidateLaporanCache;
use App\Observers\MediaObserver;
use App\Observers\UnitKerjaObserver;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Juniyasyos\FilamentMediaManager\Models\Media;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Vite asset for Filament panel
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => Blade::render("@vite('resources/js/app.js')")
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerModelPolicies();
        $this->registerSocialiteProviders();
        $this->registerLanguageSwitch();
        $this->registerTranslationNamespaces();
        $this->registerObservers();
        $this->registerDomainEventListeners();
        $this->ensureKaidoSettings();

        // Uncomment if you want to force HTTPS in production
        // if (config('app.env') === 'production') {
        //     URL::forceScheme('https');
        // }
    }

    /**
     * Register model-policy mappings automatically.
     */
    protected function registerModelPolicies(): void
    {
        $mappings = collect(glob(app_path('Models') . '/*.php'))
            ->map(fn ($file) => [
                'model' => 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME),
                'policy' => 'App\\Policies\\' . pathinfo($file, PATHINFO_FILENAME) . 'Policy',
            ]);

        foreach (glob(app_path('Domains') . '/*', GLOB_ONLYDIR) as $domainPath) {
            $domain = basename($domainPath);
            $modelDirectory = $domainPath . '/Models';
            $policyDirectory = $domainPath . '/Policies';

            if (! is_dir($modelDirectory) || ! is_dir($policyDirectory)) {
                continue;
            }

            $domainMappings = collect(glob($modelDirectory . '/*.php'))
                ->map(fn ($file) => [
                    'model' => "App\\Domains\\{$domain}\\Models\\" . pathinfo($file, PATHINFO_FILENAME),
                    'policy' => "App\\Domains\\{$domain}\\Policies\\" . pathinfo($file, PATHINFO_FILENAME) . 'Policy',
                ]);

            $mappings = $mappings->merge($domainMappings);
        }

        $mappings->each(function (array $item): void {
            if (class_exists($item['model']) && class_exists($item['policy'])) {
                Gate::policy($item['model'], $item['policy']);
            }
        });
    }

    /**
     * Register socialite providers dynamically.
     */
    protected function registerSocialiteProviders(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Google\Provider::class);
        });
    }

    /**
     * Configure the Filament Language Switch plugin.
     */
    protected function registerLanguageSwitch(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['id', 'en'])
                ->outsidePanelPlacement(Placement::BottomRight);
        });
    }

    /**
     * Load translation files from vendor packages.
     */
    protected function registerTranslationNamespaces(): void
    {
        $vendorLangPath = base_path('lang/vendor');

        collect(File::directories($vendorLangPath))->each(function ($packagePath) {
            $namespace = basename($packagePath);

            $hasTranslationFiles = collect(File::directories($packagePath))
                ->contains(function ($localePath) {
                    return collect(File::files($localePath))
                        ->contains(fn ($file) => in_array($file->getExtension(), ['php', 'json']));
                });

            if ($hasTranslationFiles) {
                $this->loadTranslationsFrom($packagePath, $namespace);
            }
        });
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        UnitKerja::observe(UnitKerjaObserver::class);
        Media::observe(MediaObserver::class);
    }

    private function registerDomainEventListeners(): void
    {
        Event::listen(ImutPenilaianSubmitted::class, InvalidateImutCache::class);
        Event::listen(LaporanGenerated::class, InvalidateLaporanCache::class);
    }

    /**
     * Ensure KaidoSetting has all required properties.
     */
    protected function ensureKaidoSettings(): void
    {
        try {
            // Only run this in web/console context, not during migrations
            if (!app()->runningInConsole() || app()->runningUnitTests()) {
                return;
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }

            $defaultSettings = [
                'site_name' => 'SIIMUT',
                'site_active' => true,
                'registration_enabled' => false,
                'login_enabled' => true,
                'password_reset_enabled' => true,
                'sso_enabled' => false,
            ];

            $existingSettings = \Illuminate\Support\Facades\DB::table('settings')
                ->where('group', 'KaidoSetting')
                ->pluck('name')
                ->toArray();

            foreach ($defaultSettings as $key => $value) {
                $settingName = "KaidoSetting.{$key}";

                if (!in_array($settingName, $existingSettings)) {
                    \Illuminate\Support\Facades\DB::table('settings')->insert([
                        'group' => 'KaidoSetting',
                        'name' => $settingName,
                        'locked' => false,
                        'payload' => json_encode($value),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently handle errors to prevent boot failures
            logger()->warning('Failed to ensure KaidoSettings: ' . $e->getMessage());
        }
    }
}
