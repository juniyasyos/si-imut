<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\LaporanImutResource;
use App\Filament\Widgets\RecommendationAnalysisTimMutuWidget;
use App\Filament\Widgets\RecommendationAnalysisUnitKerjaWidget;
use App\Models\ImutCategory;
use App\Models\LaporanImutAutoGenerationSetting;
use App\Models\UnitKerja;
use Filament\Actions;
use Filament\Actions\ActionGroup;
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
                ->icon('heroicon-o-cog-6-tooth')
                ->color('info')
                ->label('Manajemen Otomasi Laporan')
                ->modalHeading('Konfigurasi Otomasi Laporan IMUT')
                ->modalDescription('Sesuaikan cara sistem membuat laporan IMUT secara otomatis setiap periode.')
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
                            Forms\Components\TextInput::make('back_data_entry_duration')
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
                    $data['back_data_entry_duration'] = isset($data['back_data_entry_duration']) && is_numeric($data['back_data_entry_duration']) ? (int)$data['back_data_entry_duration'] : 6;
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
            ActionGroup::make([
                Actions\Action::make('viewKategoriLaporan')
                    ->label('Laporan Mutu per Kategori')
                    ->icon('heroicon-o-chart-bar-square')
                    ->color('success')
                    ->modalHeading('Laporan Indikator per Kategori')
                    ->modalDescription('Pilih kategori indikator dan periode untuk melihat laporan.')
                    ->modalWidth('2xl')
                    ->visible(fn() => Gate::allows('update_laporan::imut'))
                    ->form([
                        Forms\Components\Section::make('Pilih Kategori & Periode')
                            ->schema([
                                Forms\Components\Select::make('imut_category')
                                    ->label('Kategori Indikator')
                                    ->options(fn() => ImutCategory::orderBy('id')->pluck('category_name', 'id'))
                                    ->multiple()
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Pilih kategori indikator...'),

                                Forms\Components\Select::make('periode_tipe')
                                    ->label('Jenis Periode')
                                    ->options([
                                        'yearly' => 'Tahunan',
                                        'quarterly' => 'Triwulan',
                                        'semester' => 'Semester',
                                        'custom' => 'Custom (Range Bulan)',
                                    ])
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
                                    ->options([
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
                                    ])
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
                                    ->options([
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
                                    ])
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
                            ])
                            ->columns(2),
                    ])
                    ->openUrlInNewTab()
                    ->action(function (array $data) {
                        $tipe = $data['periode_tipe'];
                        $periode = '';
                        if ($tipe === 'yearly') {
                            $periode = $data['periode_tahun'];
                        } elseif ($tipe === 'quarterly') {
                            $periode = $data['periode_tahun'] . '-' . $data['periode_quarter'];
                        } elseif ($tipe === 'semester') {
                            $periode = $data['periode_tahun'] . '-' . $data['periode_semester'];
                        } elseif ($tipe === 'custom') {
                            $periode = $data['periode_start_year'] . '-' . $data['periode_start_month'] . ',' .
                                $data['periode_end_year'] . '-' . $data['periode_end_month'];
                        }
                        $categories = implode(',', $data['imut_category'] ?? []);
                        $url = route('laporan.indikator-mutu.by-category');
                        $url .= '?categories=' . urlencode($categories) . '&periode=' . urlencode($periode);
                        return redirect($url);
                    }),

                Actions\Action::make('viewUnitKerjaLaporan')
                    ->label('Laporan Unit Kerja')
                    ->icon('heroicon-o-chart-bar-square')
                    ->color('success')
                    ->modalHeading('Laporan IMUT Unit Kerja')
                    ->modalDescription('Pilih unit kerja dan periode untuk melihat laporan detail.')
                    ->modalWidth('2xl')
                    ->visible(fn() => Auth::check())
                    ->form([
                        Forms\Components\Section::make('Pilih Unit Kerja & Periode')
                            ->schema([
                                Forms\Components\Select::make('unit_kerja_id')
                                    ->label('Unit Kerja')
                                    ->options(function () {
                                        $user = Auth::user();

                                        // Check if user has admin or tim mutu roles
                                        $isAdminOrTimMutu = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);

                                        if ($isAdminOrTimMutu) {
                                            // Admin/Tim Mutu can see all unit kerja
                                            return UnitKerja::orderBy('unit_name')->pluck('unit_name', 'id');
                                        } else {
                                            // PIC/Pengumpul Data can only see their assigned unit kerja
                                            return $user->unitKerjas()->orderBy('unit_name')->pluck('unit_name', 'id');
                                        }
                                    })
                                    ->default(function () {
                                        $user = Auth::user();

                                        if ($user->unitKerjas()->count() === 0) {
                                            return null; // No default if user has no assigned unit kerja
                                        } else {
                                            return $user->unitKerjas()->orderBy('unit_name')->first()->id;
                                        }
                                    })
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Pilih unit kerja...'),

                                Forms\Components\Select::make('periode_tipe')
                                    ->label('Jenis Periode')
                                    ->options([
                                        'yearly' => 'Tahunan',
                                        'quarterly' => 'Triwulan',
                                        'semester' => 'Semester',
                                        'custom' => 'Custom (Range Bulan)',
                                    ])
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
                                    ->options([
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
                                    ])
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
                                    ->options([
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
                                    ])
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
                            ])
                            ->columns(2),
                    ])
                    ->openUrlInNewTab()
                    ->action(function (array $data) {
                        $user = Auth::user();

                        // Check if user has access to the selected unit kerja
                        $isAdminOrTimMutu = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);

                        if (!$isAdminOrTimMutu) {
                            // For PIC/Pengumpul Data, check if they have access to the selected unit kerja
                            $hasAccess = $user->unitKerjas()->where('unit_kerja_id', $data['unit_kerja_id'])->exists();

                            if (!$hasAccess) {
                                Notification::make()
                                    ->title('Akses Ditolak')
                                    ->body('Anda tidak memiliki akses ke unit kerja yang dipilih.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        $unitKerja = UnitKerja::find($data['unit_kerja_id']);
                        if (!$unitKerja) {
                            Notification::make()
                                ->title('Unit Kerja tidak ditemukan')
                                ->danger()
                                ->send();
                            return;
                        }

                        $tipe = $data['periode_tipe'];
                        $periode = '';

                        if ($tipe === 'yearly') {
                            $periode = $data['periode_tahun'];
                        } elseif ($tipe === 'quarterly') {
                            $periode = $data['periode_tahun'] . '-' . $data['periode_quarter'];
                        } elseif ($tipe === 'semester') {
                            $periode = $data['periode_tahun'] . '-' . $data['periode_semester'];
                        } elseif ($tipe === 'custom') {
                            $periode = $data['periode_start_year'] . '-' . $data['periode_start_month'] . ','
                                . $data['periode_end_year'] . '-' . $data['periode_end_month'];
                        }

                        $url = route('laporan.indikator-mutu.unit-kerja.show-with-period', [
                            'unitKerja' => $unitKerja->slug,
                            'tipe' => $tipe,
                            'periode' => $periode,
                        ]);

                        return redirect($url);
                    }),
            ])
                ->button()
                ->visible(fn() => Gate::allows('update_laporan::imut'))
                ->icon('heroicon-m-chart-bar-square')
                ->label('Rangkap Laporan'),

            Actions\Action::make('viewUnitKerjaLaporanOnly')
                ->label('Laporan Unit Kerja')
                ->icon('heroicon-o-chart-bar-square')
                ->color('success')
                ->modalHeading('Laporan IMUT Unit Kerja')
                ->modalDescription('Pilih unit kerja dan periode untuk melihat laporan detail.')
                ->modalWidth('2xl')
                ->visible(fn() => Gate::denies('update_laporan::imut'))
                ->form([
                    Forms\Components\Section::make('Pilih Unit Kerja & Periode')
                        ->schema([
                            Forms\Components\Select::make('unit_kerja_id')
                                ->label('Unit Kerja')
                                ->options(function () {
                                    $user = Auth::user();

                                    // Check if user has admin or tim mutu roles
                                    $isAdminOrTimMutu = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);

                                    if ($isAdminOrTimMutu) {
                                        // Admin/Tim Mutu can see all unit kerja
                                        return UnitKerja::orderBy('unit_name')->pluck('unit_name', 'id');
                                    } else {
                                        // PIC/Pengumpul Data can only see their assigned unit kerja
                                        return $user->unitKerjas()->orderBy('unit_name')->pluck('unit_name', 'id');
                                    }
                                })
                                ->default(function () {
                                    $user = Auth::user();

                                    if ($user->unitKerjas()->count() === 0) {
                                        return null; // No default if user has no assigned unit kerja
                                    } else {
                                        return $user->unitKerjas()->orderBy('unit_name')->first()->id;
                                    }
                                })
                                ->required()
                                ->searchable()
                                ->placeholder('Pilih unit kerja...'),

                            Forms\Components\Select::make('periode_tipe')
                                ->label('Jenis Periode')
                                ->options([
                                    'yearly' => 'Tahunan',
                                    'quarterly' => 'Triwulan',
                                    'semester' => 'Semester',
                                    'custom' => 'Custom (Range Bulan)',
                                ])
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
                                ->options([
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
                                ])
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
                                ->options([
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
                                ])
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
                        ])
                        ->columns(2),
                ])
                ->openUrlInNewTab()
                ->action(function (array $data) {
                    $user = Auth::user();

                    // Check if user has access to the selected unit kerja
                    $isAdminOrTimMutu = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);

                    if (!$isAdminOrTimMutu) {
                        // For PIC/Pengumpul Data, check if they have access to the selected unit kerja
                        $hasAccess = $user->unitKerjas()->where('unit_kerja_id', $data['unit_kerja_id'])->exists();

                        if (!$hasAccess) {
                            Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Anda tidak memiliki akses ke unit kerja yang dipilih.')
                                ->danger()
                                ->send();
                            return;
                        }
                    }

                    $unitKerja = UnitKerja::find($data['unit_kerja_id']);
                    if (!$unitKerja) {
                        Notification::make()
                            ->title('Unit Kerja tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    $tipe = $data['periode_tipe'];
                    $periode = '';

                    if ($tipe === 'yearly') {
                        $periode = $data['periode_tahun'];
                    } elseif ($tipe === 'quarterly') {
                        $periode = $data['periode_tahun'] . '-' . $data['periode_quarter'];
                    } elseif ($tipe === 'semester') {
                        $periode = $data['periode_tahun'] . '-' . $data['periode_semester'];
                    } elseif ($tipe === 'custom') {
                        $periode = $data['periode_start_year'] . '-' . $data['periode_start_month'] . ','
                            . $data['periode_end_year'] . '-' . $data['periode_end_month'];
                    }

                    $url = route('laporan.indikator-mutu.unit-kerja.show-with-period', [
                        'unitKerja' => $unitKerja->slug,
                        'tipe' => $tipe,
                        'periode' => $periode,
                    ]);

                    return redirect($url);
                }),

            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-m-plus'),
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
