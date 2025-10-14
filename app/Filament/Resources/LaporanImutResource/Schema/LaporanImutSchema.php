<?php

namespace App\Filament\Resources\LaporanImutResource\Schema;

use App\Filament\Resources\LaporanImutResource;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Models\User;
use App\Rules\UniqueLaporanPeriode;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LaporanImutSchema extends LaporanImutResource
{
    public static function make(): array
    {
        return [
            Section::make('Informasi Laporan')
                ->description('Lengkapi data laporan di bawah ini.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Laporan')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->default(function () {
                            $now = Carbon::now();

                            return 'Laporan IMUT Periode ' . $now->translatedFormat('F Y');
                        })
                        ->helperText('Nama laporan dapat diubah dan tidak harus unik.'),

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
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                    static::validateUniquePeriod($get, $set, $state, $get('report_year'));
                                })
                                ->rules([
                                    fn (Get $get): UniqueLaporanPeriode => new UniqueLaporanPeriode(
                                        $get('id'), // ignore current record ID
                                        $get('report_year'), // pass current year
                                        null // month will be passed by the rule
                                    ),
                                ])
                                ->helperText('Bulan periode laporan ini.'),

                            TextInput::make('report_year')
                                ->label('Tahun Laporan')
                                ->numeric()
                                ->minValue(2020)
                                ->maxValue(now()->year + 1)
                                ->required()
                                ->default(now()->year)
                                ->reactive()
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                    static::validateUniquePeriod($get, $set, $get('report_month'), $state);
                                })
                                ->rules([
                                    fn (Get $get): UniqueLaporanPeriode => new UniqueLaporanPeriode(
                                        $get('id'), // ignore current record ID
                                        null, // year will be passed by the rule
                                        $get('report_month') // pass current month
                                    ),
                                ])
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

                    Section::make('Unit Kerja')
                        ->description('Pilih unit kerja yang akan mengisi indikator mutu.')
                        ->columnSpanFull()
                        ->schema([
                            CheckboxList::make('unitKerjas')
                                ->relationship('unitKerjas', 'unit_name')
                                ->label('Unit Kerja yang Bisa Menilai')
                                ->columns(3)
                                ->required()
                                // ->disabledOn('edit')
                                ->bulkToggleable()
                                ->default(UnitKerja::pluck('id')->toArray()),
                        ]),
                ])
                ->columns(2),
        ];
    }

    /**
     * Validate unique period combination of month and year
     */
    protected static function validateUniquePeriod(callable $get, callable $set, $month, $year): void
    {
        if (!$month || !$year) {
            return;
        }

        // Check if combination already exists (excluding current record if editing)
        $existingReport = LaporanImut::where('report_month', $month)
            ->where('report_year', $year)
            ->when($get('id'), function ($query, $id) {
                return $query->where('id', '!=', $id);
            })
            ->first();

        if ($existingReport) {
            $monthName = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ][$month] ?? $month;

            // Show user-friendly notification
            Notification::make()
                ->title('Periode Laporan Sudah Ada')
                ->body("Laporan untuk periode {$monthName} {$year} sudah dibuat dengan nama: \"{$existingReport->name}\"")
                ->warning()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('lihat')
                        ->label('Lihat Laporan')
                        ->url(route('filament.admin.resources.laporan-imuts.view', $existingReport->id))
                        ->button(),
                ])
                ->send();

            // Reset to previous valid values or current month/year
            $set('report_month', now()->month);
            $set('report_year', now()->year);
        }
    }
}
