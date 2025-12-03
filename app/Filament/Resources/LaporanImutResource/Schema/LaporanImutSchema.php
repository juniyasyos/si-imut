<?php

namespace App\Filament\Resources\LaporanImutResource\Schema;

use App\Filament\Resources\LaporanImutResource;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Auth;

class LaporanImutSchema extends LaporanImutResource
{
    public static function make(): array
    {
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
                                ->reactive()
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
                                ->reactive()
                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                    static::updateLaporanName($get, $set);
                                })
                                ->helperText('Tahun periode laporan ini.'),
                        ]),


                    DatePicker::make('assessment_period_start')
                        ->label('Dimulainya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->reactive()
                        ->default(now()->format('Y-m-d'))
                        ->afterStateUpdated(function (callable $set, $state) {
                            if ($state > now()->toDateString()) {
                                $set('status', 'coming_soon');
                            } else {
                                $set('status', 'process');
                            }

                            // Auto-update report_month dan report_year
                            if ($state) {
                                $date = Carbon::parse($state);
                                $set('report_month', $date->month);
                                $set('report_year', $date->year);
                            }
                        }),

                    DatePicker::make('assessment_period_end')
                        ->label('Berakhirnya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->minDate(fn(callable $get) => $get('assessment_period_start'))
                        ->rule('after_or_equal:assessment_period_start')
                        ->afterStateUpdated(function (callable $set, $state) {
                            if ($state < now()->toDateString()) {
                                $set('status', 'complete');
                            } else {
                                $set('status', 'process');
                            }
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

                    Section::make('Pemilihan Unit Kerja')
                        ->description('Tentukan unit kerja yang berwenang melakukan penilaian indikator mutu pada periode laporan ini.')
                        ->icon('heroicon-o-building-office-2')
                        ->columnSpanFull()
                        ->schema([
                            CheckboxList::make('unitKerjas')
                                ->relationship('unitKerjas', 'unit_name')
                                ->label('Daftar Unit Kerja yang Berpartisipasi')
                                ->columns(2)
                                ->required()
                                ->bulkToggleable()
                                ->default(UnitKerja::pluck('id')->toArray())
                                ->searchable()
                                ->disabled(fn($record) => $record?->status === 'complete')
                                ->descriptions(function ($state, $record) {
                                    // Hanya tampilkan statistik pada mode edit
                                    if (!$record?->id) {
                                        return [];
                                    }

                                    $stats = static::getUnitKerjaPenilaianStats($record->id);
                                    $descriptions = [];

                                    foreach (UnitKerja::all() as $unitKerja) {
                                        if (isset($stats[$unitKerja->id])) {
                                            $stat = $stats[$unitKerja->id];
                                            $total = $stat['total'];
                                            $filled = $stat['filled'];
                                            $percentage = $total > 0 ? round(($filled / $total) * 100) : 0;

                                            if ($total > 0) {
                                                // Tentukan badge warna berdasarkan progress
                                                if ($percentage >= 80) {
                                                    $badge = '✅'; // Hijau - Baik
                                                } elseif ($percentage >= 50) {
                                                    $badge = '⚠️'; // Kuning - Sedang
                                                } elseif ($percentage > 0) {
                                                    $badge = '🔶'; // Oranye - Mulai
                                                } else {
                                                    $badge = '⭕'; // Merah - Kosong
                                                }

                                                $descriptions[$unitKerja->id] = sprintf(
                                                    '%s %d dari %d penilaian terisi • Progress: %d%%',
                                                    $badge,
                                                    $filled,
                                                    $total,
                                                    $percentage
                                                );
                                            } else {
                                                $descriptions[$unitKerja->id] = '⚪ Belum ada data penilaian';
                                            }
                                        } else {
                                            $descriptions[$unitKerja->id] = '⚪ Belum ada data penilaian';
                                        }
                                    }

                                    return $descriptions;
                                })
                                ->helperText(fn($record) => $record?->status === 'complete' 
                                    ? '🔒 Laporan sudah selesai. Pemilihan unit kerja tidak dapat diubah.'
                                    : '⚠️ PERINGATAN: Menghapus centang pada unit kerja yang sudah memiliki data akan **menghapus permanen** semua penilaian yang telah diinput oleh unit tersebut.')
                                ->hint('Pilih semua unit yang relevan')
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
     * Get statistics for unit kerja penilaian counts
     * Returns array with unit_kerja_id as key and count as value
     */
    public static function getUnitKerjaPenilaianStats(?int $laporanId): array
    {
        if (!$laporanId) {
            return [];
        }

        $laporan = \App\Models\LaporanImut::with(['laporanUnitKerjas.imutPenilaians'])->find($laporanId);

        if (!$laporan) {
            return [];
        }

        $stats = [];
        foreach ($laporan->laporanUnitKerjas as $laporanUnitKerja) {
            $unitKerjaId = $laporanUnitKerja->unit_kerja_id;
            $penilaianCount = $laporanUnitKerja->imutPenilaians()->count();
            $penilaianFilled = $laporanUnitKerja->imutPenilaians()
                ->whereNotNull('numerator_value')->whereNotNull('denominator_value')
                ->count();

            $stats[$unitKerjaId] = [
                'total' => $penilaianCount,
                'filled' => $penilaianFilled,
                'empty' => $penilaianCount - $penilaianFilled,
            ];
        }

        return $stats;
    }
}
