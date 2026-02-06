<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\LaporanImutResource;
use App\Models\LaporanImutAutoGenerationSetting;
use App\Models\UnitKerja;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ListLaporanImuts extends ListRecords
{
    protected static string $resource = LaporanImutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('autoGenerationSettings')
                ->label('Pengaturan Auto Generate')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('info')
                ->modalHeading('Pengaturan Auto Generate Laporan IMUT')
                ->modalDescription('Konfigurasikan pembuatan laporan IMUT secara otomatis sesuai jadwal yang ditentukan.')
                ->modalWidth('5xl')
                ->visible(fn() => Gate::allows('update_laporan::imut'))
                ->form([
                    Forms\Components\Section::make('Pengaturan Dasar')
                        ->schema([
                            Forms\Components\Toggle::make('is_enabled')
                                ->label('Aktifkan Auto Generate')
                                ->helperText('Nyalakan untuk mengaktifkan pembuatan laporan otomatis')
                                ->default(true)
                                ->live(),

                            Forms\Components\Select::make('frequency')
                                ->label('Frekuensi Pembuatan')
                                ->options([
                                    'monthly' => 'Bulanan',
                                ])
                                ->default('monthly')
                                ->required()
                                ->disabled(),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Periode Laporan')
                        ->description('Tentukan periode pelaporan bulanan.')
                        ->schema([
                            Forms\Components\Select::make('period_preset')
                                ->label('Preset Periode')
                                ->helperText('Pilih preset atau atur manual')
                                ->options([
                                    'standard' => '1 - 31 (Awal sampai Akhir Bulan)',
                                    'shift_5' => '5 - 4 (Tanggal 5 bulan ini sampai 4 bulan depan)',
                                    'shift_10' => '10 - 9 (Tanggal 10 bulan ini sampai 9 bulan depan)',
                                    'custom' => 'Custom (Atur Manual)',
                                ])
                                ->default('standard')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state === 'standard') {
                                        $set('period_start_day', 1);
                                        $set('period_end_day', 31);
                                        $set('report_month_based_on', 'start');
                                    } elseif ($state === 'shift_5') {
                                        $set('period_start_day', 5);
                                        $set('period_end_day', 4);
                                        $set('report_month_based_on', 'start');
                                    } elseif ($state === 'shift_10') {
                                        $set('period_start_day', 10);
                                        $set('period_end_day', 9);
                                        $set('report_month_based_on', 'start');
                                    }
                                })
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),

                            Forms\Components\TextInput::make('period_start_day')
                                ->label('Tanggal Mulai')
                                ->helperText('Hari dalam bulan (1-31)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(31)
                                ->default(5)
                                ->required()
                                ->live()
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled') || $get('period_preset') !== 'custom'),

                            Forms\Components\TextInput::make('period_end_day')
                                ->label('Tanggal Akhir')
                                ->helperText('Hari dalam bulan (1-31)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(31)
                                ->default(4)
                                ->required()
                                ->live()
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled') || $get('period_preset') !== 'custom'),

                            Forms\Components\Select::make('report_month_based_on')
                                ->label('Nama Laporan Berdasarkan')
                                ->helperText('Tentukan bulan mana yang dipakai untuk nama laporan')
                                ->options([
                                    'start' => 'Bulan Awal Periode (Tanggal Mulai)',
                                    'end' => 'Bulan Akhir Periode (Tanggal Akhir)',
                                ])
                                ->default('start')
                                ->required()
                                ->live()
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),

                            Forms\Components\Placeholder::make('period_preview')
                                ->label('Preview Periode & Penamaan')
                                ->content(function (Forms\Get $get) {
                                    $start = $get('period_start_day') ?? 5;
                                    $end = $get('period_end_day') ?? 4;
                                    $basedOn = $get('report_month_based_on') ?? 'start';

                                    if ($start <= $end) {
                                        // Dalam satu bulan
                                        return new \Illuminate\Support\HtmlString('
                                            <div class="text-sm space-y-2">
                                                <div class="flex items-start gap-2">
                                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Periode:</span>
                                                    <span class="text-gray-600 dark:text-gray-400">' . $start . ' Januari sampai ' . $end . ' Januari 2026</span>
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Nama Laporan:</span>
                                                    <span class="text-gray-600 dark:text-gray-400">Laporan IMUT Januari 2026</span>
                                                </div>
                                            </div>
                                        ');
                                    } else {
                                        // Lintas bulan
                                        if ($basedOn === 'start') {
                                            return new \Illuminate\Support\HtmlString('
                                                <div class="text-sm space-y-3">
                                                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                                        <div class="flex items-start gap-2 mb-1">
                                                            <span class="font-semibold text-blue-700 dark:text-blue-300">Periode:</span>
                                                            <span class="text-blue-600 dark:text-blue-400">' . $start . ' Januari - ' . $end . ' Februari 2026</span>
                                                        </div>
                                                        <div class="flex items-start gap-2">
                                                            <span class="font-semibold text-blue-700 dark:text-blue-300">Nama Laporan:</span>
                                                            <span class="text-blue-600 dark:text-blue-400">Laporan IMUT <strong>Januari</strong> 2026 <em class="text-xs">(berdasarkan bulan awal)</em></span>
                                                        </div>
                                                    </div>
                                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                        <div class="flex items-start gap-2 mb-1">
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Periode:</span>
                                                            <span class="text-gray-600 dark:text-gray-400">' . $start . ' Februari - ' . $end . ' Maret 2026</span>
                                                        </div>
                                                        <div class="flex items-start gap-2">
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Nama Laporan:</span>
                                                            <span class="text-gray-600 dark:text-gray-400">Laporan IMUT <strong>Februari</strong> 2026</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            ');
                                        } else {
                                            return new \Illuminate\Support\HtmlString('
                                                <div class="text-sm space-y-3">
                                                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                                        <div class="flex items-start gap-2 mb-1">
                                                            <span class="font-semibold text-blue-700 dark:text-blue-300">Periode:</span>
                                                            <span class="text-blue-600 dark:text-blue-400">' . $start . ' Januari - ' . $end . ' Februari 2026</span>
                                                        </div>
                                                        <div class="flex items-start gap-2">
                                                            <span class="font-semibold text-blue-700 dark:text-blue-300">Nama Laporan:</span>
                                                            <span class="text-blue-600 dark:text-blue-400">Laporan IMUT <strong>Februari</strong> 2026 <em class="text-xs">(berdasarkan bulan akhir)</em></span>
                                                        </div>
                                                    </div>
                                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                        <div class="flex items-start gap-2 mb-1">
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Periode:</span>
                                                            <span class="text-gray-600 dark:text-gray-400">' . $start . ' Februari - ' . $end . ' Maret 2026</span>
                                                        </div>
                                                        <div class="flex items-start gap-2">
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Nama Laporan:</span>
                                                            <span class="text-gray-600 dark:text-gray-400">Laporan IMUT <strong>Maret</strong> 2026</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            ');
                                        }
                                    }
                                })
                                ->columnSpanFull(),
                        ])
                        ->columns(4),

                    Forms\Components\Section::make('Timeline & Deadline')
                        ->description('Tentukan durasi waktu untuk setiap tahap pengisian laporan')
                        ->schema([
                            Forms\Components\TextInput::make('data_entry_duration')
                                ->label('Berapa Hari Sebelumnya Bisa Diisi (hari)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(90)
                                ->default(7)
                                ->required()
                                ->suffix('hari')
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),

                            Forms\Components\TextInput::make('recommendation_analysis_duration')
                                ->label('Durasi Pengisian Analisis & Rekomendasi (hari)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(30)
                                ->default(2)
                                ->required()
                                ->suffix('hari')
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),
                        ])
                        ->collapsible()
                        ->columns(2),

                    Forms\Components\Section::make('Pengaturan Otomasi')
                        ->schema([
                            Forms\Components\Toggle::make('auto_calculate')
                                ->label('Auto Calculate dari Daily Reports')
                                ->helperText('Otomatis hitung numerator/denominator dari daily reports')
                                ->default(true)
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),

                            Forms\Components\Toggle::make('auto_publish')
                                ->label('Auto Publish')
                                ->helperText('Langsung publish atau tetap draft')
                                ->default(false)
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),

                            Forms\Components\CheckboxList::make('default_unit_kerjas')
                                ->label('Unit Kerja Default')
                                ->helperText('Unit kerja yang otomatis di-include')
                                ->searchable()
                                ->bulkToggleable()
                                ->options(UnitKerja::orderBy('unit_name')->pluck('unit_name', 'id'))
                                ->columns(3)
                                ->columnSpanFull()
                                ->gridDirection('row')
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),
                        ])
                        ->collapsible()
                        ->columns(3)
                ])
                ->fillForm(function () {
                    $settings = LaporanImutAutoGenerationSetting::getInstance();
                    return $settings->toArray();
                })
                ->action(function (array $data) {
                    // Remove non-database fields
                    unset($data['period_preset']);

                    $settings = LaporanImutAutoGenerationSetting::getInstance();
                    $settings->fill($data);
                    $settings->updated_by = Auth::id();
                    $settings->save();

                    Notification::make()
                        ->title('Pengaturan Berhasil Disimpan')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-m-plus'),
        ];
    }
}
