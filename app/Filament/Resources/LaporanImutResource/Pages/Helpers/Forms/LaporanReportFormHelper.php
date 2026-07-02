<?php

namespace App\Filament\Resources\LaporanImutResource\Pages\Helpers\Forms;

use App\Models\ImutCategory;
use App\Models\UnitKerja;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class LaporanReportFormHelper
{
    public static function categoryReportSchema(): array
    {
        return [
            Forms\Components\Section::make('Pilih Kategori & Periode')
                ->schema([
                    Forms\Components\Select::make('imut_category')
                        ->label('Kategori Indikator')
                        ->options(fn() => ImutCategory::orderBy('id')->pluck('category_name', 'id'))
                        ->multiple()
                        ->required()
                        ->searchable()
                        ->placeholder('Pilih kategori indikator...'),
                    ...self::periodFields(),
                ])
                ->columns(2),
        ];
    }

    public static function unitKerjaReportSchema(): array
    {
        return [
            Forms\Components\Section::make('Pilih Unit Kerja & Periode')
                ->schema([
                    Forms\Components\Select::make('unit_kerja_id')
                        ->label('Unit Kerja')
                        ->options(function () {
                            $user = Auth::user();

                            if ($user->hasAnyRole(['super_admin', 'admin', 'tim_mutu'])) {
                                return UnitKerja::orderBy('unit_name')->pluck('unit_name', 'id');
                            }

                            return $user->unitKerjas()->orderBy('unit_name')->pluck('unit_name', 'id');
                        })
                        ->default(function () {
                            $user = Auth::user();

                            if ($user->unitKerjas()->count() === 0) {
                                return null;
                            }

                            return $user->unitKerjas()->orderBy('unit_name')->first()->id;
                        })
                        ->required()
                        ->searchable()
                        ->placeholder('Pilih unit kerja...'),
                    ...self::periodFields(),
                ])
                ->columns(2),
        ];
    }

    public static function unitKerjaWithCategorySchema(): array
    {
        return [
            Forms\Components\Section::make('Pilih Kategori & Periode')
                ->schema([
                    Forms\Components\Hidden::make('unit_kerja_id')
                        ->default(function () {
                            $user = Auth::user();

                            if ($user->unitKerjas()->count() === 0) {
                                return null;
                            }

                            return $user->unitKerjas()->orderBy('unit_name')->first()->id;
                        }),
                    Forms\Components\Select::make('imut_category')
                        ->label('Kategori Indikator')
                        ->options(fn() => ImutCategory::orderBy('id')->pluck('category_name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->placeholder('Semua Kategori (Pilih jika spesifik)'),
                    ...self::periodFields(),
                ])
                ->columns(2),
        ];
    }

    private static function periodFields(): array
    {
        return [
            Forms\Components\Select::make('periode_tipe')
                ->label('Jenis Periode')
                ->options(self::periodTypeOptions())
                ->default('yearly')
                ->required()
                ->live(),

            Forms\Components\TextInput::make('periode_tahun')
                ->label('Tahun')
                ->numeric()
                ->minValue(2020)
                ->maxValue(9999)
                ->default(now()->year)
                ->required()
                ->visible(fn(Forms\Get $get) => $get('periode_tipe') !== 'custom'),

            Forms\Components\Select::make('periode_quarter')
                ->label('Triwulan')
                ->options([
                    'Q1' => 'Triwulan I (Jan-Mar)',
                    'Q2' => 'Triwulan II (Apr-Jun)',
                    'Q3' => 'Triwulan III (Jul-Sep)',
                    'Q4' => 'Triwulan IV (Okt-Des)',
                ])
                ->required()
                ->visible(fn(Forms\Get $get) => $get('periode_tipe') === 'quarterly'),

            Forms\Components\Select::make('periode_semester')
                ->label('Semester')
                ->options([
                    'S1' => 'Semester I (Jan-Jun)',
                    'S2' => 'Semester II (Jul-Des)',
                ])
                ->required()
                ->visible(fn(Forms\Get $get) => $get('periode_tipe') === 'semester'),

            Forms\Components\Select::make('periode_start_month')
                ->label('Bulan Awal')
                ->options(self::monthOptions())
                ->required()
                ->visible(fn(Forms\Get $get) => $get('periode_tipe') === 'custom'),

            Forms\Components\TextInput::make('periode_start_year')
                ->label('Tahun Awal')
                ->numeric()
                ->minValue(2020)
                ->maxValue(9999)
                ->default(now()->year)
                ->required()
                ->visible(fn(Forms\Get $get) => $get('periode_tipe') === 'custom'),

            Forms\Components\Select::make('periode_end_month')
                ->label('Bulan Akhir')
                ->options(self::monthOptions())
                ->required()
                ->visible(fn(Forms\Get $get) => $get('periode_tipe') === 'custom'),

            Forms\Components\TextInput::make('periode_end_year')
                ->label('Tahun Akhir')
                ->numeric()
                ->minValue(2020)
                ->maxValue(9999)
                ->default(now()->year)
                ->required()
                ->visible(fn(Forms\Get $get) => $get('periode_tipe') === 'custom'),
        ];
    }

    private static function periodTypeOptions(): array
    {
        return [
            'yearly' => 'Tahunan',
            'quarterly' => 'Triwulan',
            'semester' => 'Semester',
            'custom' => 'Custom (Range Bulan)',
        ];
    }

    private static function monthOptions(): array
    {
        return [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    }
}
