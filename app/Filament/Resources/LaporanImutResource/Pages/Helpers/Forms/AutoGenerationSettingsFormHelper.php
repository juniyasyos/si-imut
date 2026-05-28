<?php

namespace App\Filament\Resources\LaporanImutResource\Pages\Helpers\Forms;

use App\Models\UnitKerja;
use Carbon\Carbon;
use Filament\Forms;
use Illuminate\Support\HtmlString;

class AutoGenerationSettingsFormHelper
{
    public static function schema(): array
    {
        return [
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

                    Forms\Components\TextInput::make('schedule_day_of_month')
                        ->label('Tanggal Eksekusi Scheduler')
                        ->helperText('Scheduler generate laporan akan jalan setiap tanggal ini (1-28).')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(28)
                        ->default(1)
                        ->required()
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),

                    Forms\Components\TimePicker::make('schedule_run_time')
                        ->label('Jam Eksekusi Scheduler')
                        ->helperText('Format 24 jam, contoh: 01:00')
                        ->seconds(false)
                        ->minutesStep(5)
                        ->default('01:00')
                        ->required()
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),

                    Forms\Components\Placeholder::make('schedule_next_info')
                        ->label('Info Jadwal Pembuatan Laporan')
                        ->content(function (Forms\Get $get) {
                            if (! $get('is_enabled')) {
                                return new HtmlString('<span class="text-sm text-gray-500">Otomasi nonaktif. Laporan tidak akan dibuat otomatis sampai fitur diaktifkan.</span>');
                            }

                            $day = max(1, min(28, (int) ($get('schedule_day_of_month') ?? 1)));
                            $time = (string) ($get('schedule_run_time') ?? '01:00');

                            if (! preg_match('/^([01]\\d|2[0-3]):([0-5]\\d)/', $time, $matches)) {
                                $time = '01:00';
                            } else {
                                $time = $matches[1] . ':' . $matches[2];
                            }

                            $now = now();
                            $nextRun = Carbon::create($now->year, $now->month, min($day, $now->daysInMonth), (int) substr($time, 0, 2), (int) substr($time, 3, 2), 0);

                            if ($nextRun->lte($now)) {
                                $nextRun = Carbon::create($now->year, $now->month, 1, (int) substr($time, 0, 2), (int) substr($time, 3, 2), 0)
                                    ->addMonth()
                                    ->day(min($day, Carbon::create($now->year, $now->month, 1)->addMonth()->daysInMonth));
                            }

                            return new HtmlString(sprintf(
                                '<div class="text-sm space-y-1">'
                                . '<p class="font-medium text-gray-800 dark:text-gray-200">Laporan dibuat otomatis setiap tanggal <strong>%d</strong> jam <strong>%s</strong>.</p>'
                                . '<p class="text-gray-500">Eksekusi berikutnya: <strong>%s</strong>.</p>'
                                . '</div>',
                                $day,
                                $time,
                                $nextRun->translatedFormat('d F Y H:i')
                            ));
                        })
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Periode Laporan')
                ->description('Periode laporan menggunakan tanggal 1 sampai akhir bulan secara otomatis.')
                ->schema([
                    Forms\Components\Placeholder::make('period_display')
                        ->label('Periode Laporan')
                        ->content(fn() => new HtmlString('
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
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),

                    Forms\Components\Placeholder::make('period_preview')
                        ->label('Preview Penamaan Laporan')
                        ->content(function (Forms\Get $get) {
                            $basedOn = $get('report_month_based_on') ?? 'start';

                            if ($basedOn === 'start') {
                                return new HtmlString('
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
                            }

                            return new HtmlString('
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
                        })
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Timeline & Deadline')
                ->description('Tentukan durasi waktu untuk setiap tahap pengisian laporan')
                ->schema([
                    Forms\Components\TextInput::make('back_data_entry_duration')
                        ->label('Berapa Hari Sebelumnya Bisa Diisi (hari)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(90)
                        ->default(7)
                        ->required()
                        ->suffix('hari')
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),

                    Forms\Components\TextInput::make('recommendation_analysis_duration')
                        ->label('Durasi Pengisian Analisis & Rekomendasi (hari)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(30)
                        ->default(2)
                        ->required()
                        ->suffix('hari')
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),
                ])
                ->collapsible()
                ->columns(2),

            Forms\Components\Section::make('Pengaturan Otomasi')
                ->schema([
                    Forms\Components\Toggle::make('auto_calculate')
                        ->label('Auto Calculate dari Daily Reports')
                        ->helperText('Otomatis hitung numerator/denominator dari daily reports')
                        ->default(true)
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),

                    Forms\Components\Toggle::make('auto_publish')
                        ->label('Auto Publish')
                        ->helperText('Langsung publish atau tetap draft')
                        ->default(false)
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),

                    Forms\Components\CheckboxList::make('default_unit_kerjas')
                        ->label('Unit Kerja Default')
                        ->helperText('Unit kerja yang otomatis di-include')
                        ->searchable()
                        ->bulkToggleable()
                        ->options(UnitKerja::orderBy('unit_name')->pluck('unit_name', 'id'))
                        ->columns(3)
                        ->columnSpanFull()
                        ->gridDirection('row')
                        ->disabled(fn(Forms\Get $get) => ! $get('is_enabled')),
                ])
                ->collapsible()
                ->columns(3),
        ];
    }
}
