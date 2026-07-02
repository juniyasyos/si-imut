<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\LaporanImutResource\Pages\Helpers\Actions\AutoGenerationSettingsActionHelper;
use App\Filament\Resources\LaporanImutResource\Pages\Helpers\Actions\LaporanReportActionHelper;
use App\Filament\Resources\LaporanImutResource\Pages\Helpers\Forms\AutoGenerationSettingsFormHelper;
use App\Filament\Resources\LaporanImutResource\Pages\Helpers\Forms\LaporanReportFormHelper;
use App\Filament\Resources\LaporanImutResource\Pages\Helpers\Forms\RunAutoGenerationNowFormHelper;
use App\Filament\Resources\LaporanImutResource;
use App\Filament\Widgets\RecommendationAnalysisTimMutuWidget;
use App\Filament\Widgets\RecommendationAnalysisUnitKerjaWidget;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ListLaporanImuts extends ListRecords
{
    protected static string $resource = LaporanImutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-m-plus')
                ->color('primary'),

            ActionGroup::make([
                Actions\Action::make('viewKategoriLaporan')
                    ->label('Laporan per Kategori IMUT')
                    ->icon('heroicon-o-chart-pie')
                    ->color('success')
                    ->modalHeading('Laporan Indikator per Kategori')
                    ->modalDescription('Pilih kategori indikator dan periode laporan yang ingin ditampilkan.')
                    ->modalWidth('2xl')
                    ->visible(fn() => Gate::allows('update_laporan::imut'))
                    ->form(LaporanReportFormHelper::categoryReportSchema())
                    ->openUrlInNewTab()
                    ->action(fn(array $data) => LaporanReportActionHelper::buildCategoryRedirect($data)),

                Actions\Action::make('viewUnitKerjaLaporan')
                    ->label('Laporan per Unit Kerja')
                    ->icon('heroicon-o-building-office-2')
                    ->color('success')
                    ->modalHeading('Laporan IMUT Unit Kerja')
                    ->modalDescription('Pilih unit kerja dan periode untuk melihat laporan detail.')
                    ->modalWidth('2xl')
                    ->visible(fn() => Auth::check())
                    ->form(LaporanReportFormHelper::unitKerjaReportSchema())
                    ->openUrlInNewTab()
                    ->action(fn(array $data) => LaporanReportActionHelper::buildUnitKerjaRedirect($data)),
            ])
                ->button()
                ->label('Rekap Laporan')
                ->icon('heroicon-m-chart-bar-square')
                ->color('success')
                ->visible(fn() => Gate::allows('update_laporan::imut')),

            Actions\Action::make('viewUnitKerjaLaporanOnly')
                ->label('Laporan Unit Kerja')
                ->icon('heroicon-o-building-office-2')
                ->color('success')
                ->modalHeading('Laporan IMUT Unit Kerja')
                ->modalDescription('Pilih kategori dan periode untuk melihat laporan unit kerja.')
                ->modalWidth('2xl')
                ->visible(fn() => Gate::denies('update_laporan::imut'))
                ->form(LaporanReportFormHelper::unitKerjaWithCategorySchema())
                ->openUrlInNewTab()
                ->action(fn(array $data) => LaporanReportActionHelper::buildUnitKerjaRedirect($data)),

            ActionGroup::make([
                Actions\Action::make('autoGenerationSettings')
                    ->label('Pengaturan Otomasi')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('info')
                    ->modalHeading('Konfigurasi Otomasi Laporan IMUT')
                    ->modalDescription('Atur bagaimana sistem membuat laporan IMUT otomatis setiap periode.')
                    ->modalWidth('5xl')
                    ->visible(fn() => Gate::allows('update_laporan::imut'))
                    ->form(AutoGenerationSettingsFormHelper::schema())
                    ->fillForm(fn() => AutoGenerationSettingsActionHelper::fillFormData())
                    ->action(fn(array $data) => AutoGenerationSettingsActionHelper::handleSave($data)),

                Actions\Action::make('runAutoGenerationNow')
                    ->label('Jalankan Sekarang')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->visible(fn() => Gate::allows('update_laporan::imut'))
                    ->requiresConfirmation()
                    ->modalHeading('Jalankan otomasi laporan sekarang?')
                    ->modalDescription('Pilih aksi manual. Pembuatan manual hanya untuk bulan berjalan.')
                    ->form(RunAutoGenerationNowFormHelper::schema())
                    ->modalSubmitActionLabel('Ya, Jalankan')
                    ->modalCancelActionLabel('Batal')
                    ->action(fn(array $data) => AutoGenerationSettingsActionHelper::runNow($data)),
            ])
                ->button()
                ->label('Otomasi')
                ->icon('heroicon-m-adjustments-horizontal')
                ->color('gray')
                ->visible(fn() => Gate::allows('update_laporan::imut')),
        ];
    }

    /**
     * Menampilkan recommendation analysis widget di header halaman
     * - Widget untuk Tim Mutu: overview semua unit kerja
     * - Widget untuk Unit Kerja: focus pada unit kerja user
     */
    protected function getHeaderWidgets(): array
    {
        try {
            $user = Auth::user();

            \Log::info('getHeaderWidgets called', [
                'user_id' => $user?->id ?? null,
                'user_roles' => $user?->roles()->pluck('name')->toArray() ?? [],
                'has_unit_kerja' => $user?->unitKerjas()->exists() ?? false,
            ]);

            if (!$user) {
                \Log::debug('No authenticated user, returning empty widgets');
                return [];
            }

            // Check if user is Tim Mutu/Admin
            $isTimMutu = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);
            if ($isTimMutu) {
                \Log::debug('User is Tim Mutu/Admin, returning RecommendationAnalysisTimMutuWidget');
                return [
                    RecommendationAnalysisTimMutuWidget::class,
                ];
            }

            // Check if user has unit kerja
            $hasUnitKerja = $user->unitKerjas()->exists();
            if ($hasUnitKerja) {
                \Log::debug('User has unit kerja, returning RecommendationAnalysisUnitKerjaWidget');
                return [
                    RecommendationAnalysisUnitKerjaWidget::class,
                ];
            }

            \Log::debug('User has no matching widget conditions, returning empty');
            return [];
        } catch (\Exception $e) {
            // Log error but don't break the page
            \Log::error('Error in getHeaderWidgets', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
