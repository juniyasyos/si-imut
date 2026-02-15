<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\IAMLogin;
use App\Filament\Pages\Login;
use App\Filament\Plugins\PanelTheme;
use App\Filament\Resources\LaporanImutResource\Widgets\ImutDataCompletionChart;
use App\Filament\Resources\LaporanImutResource\Widgets\UnitKerjaCompletionChart;
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\ImutCapaianUnitKerjaWidget;
use App\Filament\Widgets\LaporanLatestWidget;
use App\Livewire\TtdUploadComponent;
use App\Livewire\CustomPersonalInfo;
use App\Models\UnitKerja;
use App\Models\User;
use App\Settings\KaidoSetting;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Juniyasyos\DashStackTheme\DashStackThemePlugin;
use Juniyasyos\FilamentLaravelBackup\FilamentLaravelBackupPlugin;
use Juniyasyos\FilamentMediaManager\FilamentMediaManagerPlugin;
use Juniyasyos\FilamentPWA\FilamentPWAPlugin;
use Juniyasyos\FilamentSettingsHub\FilamentSettingsHubPlugin;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
    private ?KaidoSetting $settings = null;

    public function panel(Panel $panel): Panel
    {
        // Check if SSO is enabled
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        $panel = $panel
            ->default()
            ->id('siimut')
            ->path('siimut')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // AccountWidget::class,
                // ImutCapaianUnitKerjaWidget::class,
                // ImutDataCompletionChart::make([
                //     'laporanId' => LaporanLatestWidget::getLatestLaporan()?->id,
                //     'columnSpanCustom' => 1
                // ]),
                // UnitKerjaCompletionChart::make([
                //     'laporanId' => LaporanLatestWidget::getLatestLaporan()?->id,
                //     'columnSpanCustom' => 1
                // ]),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->sidebarCollapsibleOnDesktop(true)
            ->authMiddleware([
                Authenticate::class,
            ])
            ->middleware([])
            ->navigationGroups([
                'User & Access Control',
                'Quality Indicators',
                'System & Configurations',
            ])
            ->plugins(
                $this->getPlugins()
            )
            ->databaseNotifications();

        // Only register login page if SSO is disabled
        if (!$ssoEnabled) {
            $panel->login(Login::class);
        }

        return $panel;
    }

    private function getPlugins(): array
    {
        $plugins = [
            FilamentProgressbarPlugin::make()->color('#29b'),
            ResizedColumnPlugin::make(),
            FilamentApexChartsPlugin::make(),
            DashStackThemePlugin::make(),
            FilamentShieldPlugin::make(),
            FilamentPWAPlugin::make(),
            FilamentSettingsHubPlugin::make(),
            FilamentLaravelBackupPlugin::make(),
            FilamentMediaManagerPlugin::make()->allowUserAccess()->allowSubFolders(),
            ActivitylogPlugin::make()
                ->navigationIcon('heroicon-o-clock')
                ->navigationItem()
                ->navigationGroup('User & Access Control')
                ->label('Audit & Activity Logs'),
            AuthUIEnhancerPlugin::make()
                ->showEmptyPanelOnMobile(false)
                ->formPanelPosition('right')
                ->formPanelWidth('60%')
                ->emptyPanelView('auth.custom-page-auth'),
            BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    shouldRegisterNavigation: false,
                    navigationGroup: 'System & Configuration',
                    hasAvatars: false,
                    slug: 'my-profile'
                )
                ->myProfileComponents([
                    'personal_info' => CustomPersonalInfo::class,
                    'ttd_upload' => TtdUploadComponent::class,
                ])
                ->enableTwoFactorAuthentication(),
        ];

        return $plugins;
    }
}
