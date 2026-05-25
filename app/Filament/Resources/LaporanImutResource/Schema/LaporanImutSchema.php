<?php

namespace App\Filament\Resources\LaporanImutResource\Schema;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use App\Models\LaporanImutAutoGenerationSetting;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Auth;

class LaporanImutSchema
{
    public static function make(): array
    {
        // Load setting untuk auto-generate period dan duration
        $settings = LaporanImutAutoGenerationSetting::getInstance();

        // Hitung assessment period dari setting (back data entry duration)
        $assessmentStart = now()->addDays($settings->back_data_entry_duration);
        $assessmentEnd = $assessmentStart->copy()->endOfMonth();

        return [
            Section::make('Informasi Laporan')
                ->description('Lengkapi data laporan di bawah ini.')
                ->schema([
                    // Auto-generated name - tidak perlu input manual
                    TextInput::make('name')
                        ->label('Nama Laporan')
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated() // tetap ikut terkirim ke server
                        ->default(function () {
                            $now = Carbon::now();
                            return 'Laporan IMUT Periode ' . $now->translatedFormat('F Y');
                        })
                        ->helperText('Nama laporan dibuat otomatis berdasarkan periode yang dipilih.'),

                    Grid::make(2)
                        ->schema([
                            Select::make('report_month')
                                ->label('Bulan Laporan')
                                ->options([
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember'
                                ])
                                ->required()
                                ->default(now()->month)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                    static::updateLaporanName($get, $set);
                                })
                                ->helperText('Bulan periode laporan ini.'),

                            Select::make('report_year')
                                ->label('Tahun Laporan')
                                ->options(function () {
                                    $currentYear = now()->year;
                                    $years = [];
                                    for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                                        $years[$i] = $i;
                                    }
                                    return $years;
                                })
                                ->required()
                                ->default(now()->year)
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                    static::updateLaporanName($get, $set);
                                })
                                ->helperText('Tahun periode laporan ini.'),
                        ]),


                    DatePicker::make('assessment_period_start')
                        ->label('Dimulainya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->live()
                        ->default(now()->format('Y-m-d'))
                        ->afterStateUpdated(function (callable $set, $state) {
                            if (!$state) return;

                            $today = now()->toDateString();
                            $set('status', $state > $today ? 'coming_soon' : 'process');

                            // Auto-update report_month dan report_year
                            $date = Carbon::parse($state);
                            $set('report_month', $date->month);
                            $set('report_year', $date->year);
                        }),

                    DatePicker::make('assessment_period_end')
                        ->label('Berakhirnya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->minDate(fn(callable $get) => $get('assessment_period_start'))
                        ->rule('after_or_equal:assessment_period_start')
                        ->live()
                        ->afterStateUpdated(function (callable $set, $state) {
                            if (!$state) return;

                            $today = now()->toDateString();
                            $set('status', $state < $today ? 'complete' : 'process');
                        }),

                    ToggleButtons::make('status')
                        ->label('Status Laporan')
                        ->options([
                            'process' => 'Dalam Proses',
                            'complete' => 'Selesai',
                            'coming_soon' => 'Segera Hadir',
                        ])
                        ->icons([
                            'process' => 'heroicon-o-arrow-path',
                            'complete' => 'heroicon-o-check-circle',
                            'coming_soon' => 'heroicon-o-clock',
                        ])
                        ->colors([
                            'process' => 'warning',
                            'complete' => 'success',
                            'coming_soon' => 'gray',
                        ])
                        ->default('process')
                        ->inline()
                        ->disabled() // user tidak bisa mengubah
                        ->dehydrated() // tetap ikut terkirim ke server
                        ->extraAttributes(['readonly' => true]) // HTML readonly agar UI-nya tidak bisa diubah
                        ->helperText('Status laporan ditentukan otomatis berdasarkan tanggal berakhirnya periode asesmen.'),

                    Select::make('created_by')
                        ->label('Dibuat oleh')
                        ->options(User::pluck('name', 'id'))
                        ->default(fn() => Auth::id())
                        ->disabled()
                        ->columnSpanFull(),

                    // NEW: Durasi Pengisian Analisis & Rekomendasi
                    Section::make('📋 Pengaturan Timeline Pengisian')
                        ->description('Durasi waktu yang tersedia untuk pengisian analisis dan rekomendasi.')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('recommendation_analysis_duration')
                                ->label('Durasi Pengisian Analisis & Rekomendasi')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(30)
                                ->default($settings->recommendation_analysis_duration)
                                ->required()
                                ->suffix('hari')
                                ->helperText('Jumlah hari yang tersedia untuk pengisian analisis dan rekomendasi setelah periode asesmen berakhir.')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Pemilihan Unit Kerja')
                        ->description('Tentukan unit kerja yang berwenang melakukan penilaian indikator mutu pada periode laporan ini.')
                        ->icon('heroicon-o-building-office-2')
                        ->columnSpanFull()
                        ->schema([
                            CheckboxList::make('unitKerjas')
                                ->relationship('unitKerjas', 'unit_name')
                                ->label('Daftar Unit Kerja yang Berpartisipasi')
                                ->columns(4)
                                ->required()
                                ->bulkToggleable()
                                ->default(fn() => UnitKerja::pluck('id')->toArray())
                                ->searchable()
                                ->disabled(fn($record) => $record?->status === 'complete')
                                ->helperText(fn($record) => $record?->status === 'complete'
                                    ? '🔒 Laporan sudah selesai. Pemilihan unit kerja tidak dapat diubah.'
                                    : 'Pilih unit kerja yang akan berpartisipasi dalam laporan ini.')
                                ->hint(fn($record) => $record?->id ? 'Lihat progress di halaman summary laporan' : 'Pilih semua unit yang relevan')
                                ->hintIcon('heroicon-m-information-circle'),
                        ]),
                ])
                ->columns(2),
        ];
    }

    /**
     * Auto-generate laporan name based on selected period
     */
    private static function updateLaporanName(callable $get, callable $set): void
    {
        $month = $get('report_month');
        $year = $get('report_year');

        if ($month && $year) {
            $monthNames = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];

            $monthName = $monthNames[$month] ?? '';
            $generatedName = "Laporan IMUT {$monthName} {$year}";

            $set('name', $generatedName);
        }
    }

    /**
     * Get statistics for unit kerja penilaian counts (REMOVED FOR PERFORMANCE)
     * Use summary pages to view detailed statistics instead
     */
}
