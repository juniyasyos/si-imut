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
                        ->description('Periode laporan menggunakan tanggal 1 sampai akhir bulan secara otomatis.')
                        ->schema([
                            Forms\Components\Placeholder::make('period_display')
                                ->label('Periode Laporan')
                                ->content(fn() => new \Illuminate\Support\HtmlString('
                                    <div class="text-sm">
                                        <span class="font-semibold">Tanggal 1 sampai akhir bulan</span>
                                        <p class="text-gray-500 text-xs mt-1">Otomatis menyesuaikan dengan jumlah hari di setiap bulan (28-31 hari)</p>
                                    </div>
                                ')),

                            Forms\Components\Select::make('report_month_based_on')
                                ->label('Nama Laporan Berdasarkan')
                                ->helperText('Tentukan bulan mana yang dipakai untuk nama laporan')
                                ->options([
                                    'start' => 'Bulan Awal Periode (Tanggal 1)',
                                    'end' => 'Bulan Akhir Periode (Akhir Bulan)',
                                ])
                                ->default('start')
                                ->required()
                                ->disabled(fn(Forms\Get $get) => !$get('is_enabled')),

                            Forms\Components\Placeholder::make('period_preview')
                                ->label('Preview Penamaan Laporan')
                                ->content(function (Forms\Get $get) {
                                    $basedOn = $get('report_month_based_on') ?? 'start';

                                    if ($basedOn === 'start') {
                                        return new \Illuminate\Support\HtmlString('
                                            <div class="text-sm space-y-2">
                                                <p class="text-gray-700 dark:text-gray-300">
                                                    Contoh: <strong>Laporan IMUT Januari 2026</strong>
                                                    <span class="text-xs text-gray-500">(periode 1 Jan - 31 Jan)</span>
                                                </p>
                                                <p class="text-gray-700 dark:text-gray-300">
                                                    Contoh: <strong>Laporan IMUT Februari 2026</strong>
                                                    <span class="text-xs text-gray-500">(periode 1 Feb - 28 Feb)</span>
                                                </p>
                                            </div>
                                        ');
                                    } else {
                                        return new \Illuminate\Support\HtmlString('
                                            <div class="text-sm space-y-2">
                                                <p class="text-gray-700 dark:text-gray-300">
                                                    Contoh: <strong>Laporan IMUT Januari 2026</strong>
                                                    <span class="text-xs text-gray-500">(periode 1 Des 2025 - 31 Jan 2026)</span>
                                                </p>
                                                <p class="text-gray-700 dark:text-gray-300">
                                                    Contoh: <strong>Laporan IMUT Februari 2026</strong>
                                                    <span class="text-xs text-gray-500">(periode 1 Jan - 28 Feb 2026)</span>
                                                </p>
                                            </div>
                                        ');
                                    }
                                })
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

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
                    // Ensure integer casting for numeric fields
                    $data['data_entry_duration'] = isset($data['data_entry_duration']) && is_numeric($data['data_entry_duration']) ? (int)$data['data_entry_duration'] : 7;
                    $data['recommendation_analysis_duration'] = isset($data['recommendation_analysis_duration']) && is_numeric($data['recommendation_analysis_duration']) ? (int)$data['recommendation_analysis_duration'] : 2;

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
