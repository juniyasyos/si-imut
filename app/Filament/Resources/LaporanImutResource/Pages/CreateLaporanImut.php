<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use Filament\Actions;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\LaporanImutAutoGenerationSetting;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LaporanImutResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Log;

class CreateLaporanImut extends CreateRecord
{
    protected static string $resource = LaporanImutResource::class;
    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function form(Form $form): Form
    {
        // Load setting untuk auto-generate period dan duration
        $settings = LaporanImutAutoGenerationSetting::getInstance();

        // Hitung assessment period dari awal bulan ini sampai akhir bulan ini
        $assessmentStart = now()->startOfMonth();
        $assessmentEnd = now()->endOfMonth();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Laporan')
                    ->description('Lengkapi data laporan di bawah ini.')
                    ->schema([
                        // Auto-generated name
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Laporan')
                            ->columnSpanFull()
                            ->disabled()
                            ->dehydrated()
                            ->default(function () {
                                $now = Carbon::now();
                                return 'Laporan IMUT Periode ' . $now->translatedFormat('F Y');
                            })
                            ->helperText('Nama laporan dibuat otomatis berdasarkan periode laporan.'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('report_month')
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
                                        $this->updateLaporanName($get, $set);
                                    })
                                    ->helperText('Bulan periode laporan ini.'),

                                Forms\Components\Select::make('report_year')
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
                                        $this->updateLaporanName($get, $set);
                                    })
                                    ->helperText('Tahun periode laporan ini.'),
                            ]),

                        // READONLY: Period fields from setting
                        Forms\Components\Section::make('⏰ Periode Asesmen (Otomatis dari Pengaturan)')
                            ->description('Tanggal asesmen dihitung otomatis berdasarkan konfigurasi pengaturan laporan.')
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('assessment_period_start')
                                            ->label('Dimulainya Periode Asesmen')
                                            ->disabled()
                                            ->dehydrated()
                                            ->default($assessmentStart->format('Y-m-d'))
                                            ->helperText('Dihitung: Awal bulan ini (durasi pengisian data dari awal sampai akhir bulan)')
                                            ->readOnly(),

                                        Forms\Components\TextInput::make('assessment_period_end')
                                            ->label('Berakhirnya Periode Asesmen')
                                            ->disabled()
                                            ->dehydrated()
                                            ->default($assessmentEnd->format('Y-m-d'))
                                            ->helperText('Dihitung: Akhir bulan dari start periode')
                                            ->readOnly(),
                                    ]),
                            ]),

                        // Status (readonly)
                        Forms\Components\ToggleButtons::make('status')
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
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['readonly' => true])
                            ->helperText('Status laporan ditentukan otomatis berdasarkan periode asesmen.'),

                        Forms\Components\Select::make('created_by')
                            ->label('Dibuat oleh')
                            ->options(User::pluck('name', 'id'))
                            ->default(fn() => Auth::id())
                            ->disabled()
                            ->columnSpanFull(),

                        // NEW: Durasi Pengisian Analisis & Rekomendasi
                        Forms\Components\Section::make('📋 Pengaturan Timeline Pengisian')
                            ->description('Durasi waktu yang tersedia untuk pengisian analisis dan rekomendasi.')
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\TextInput::make('recommendation_analysis_duration')
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pemilihan Unit Kerja')
                    ->description('Tentukan unit kerja yang berwenang melakukan penilaian indikator mutu pada periode laporan ini.')
                    ->icon('heroicon-o-building-office-2')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\CheckboxList::make('unitKerjas')
                            ->relationship('unitKerjas', 'unit_name')
                            ->label('Daftar Unit Kerja yang Berpartisipasi')
                            ->columns(4)
                            ->required()
                            ->bulkToggleable()
                            ->default(fn() => UnitKerja::pluck('id')->toArray())
                            ->searchable()
                            ->helperText('Pilih semua unit kerja yang relevan untuk laporan ini.')
                            ->hint('Pilih semua unit yang relevan')
                            ->hintIcon('heroicon-m-information-circle'),
                    ]),
            ]);
    }

    private function updateLaporanName(callable $get, callable $set): void
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
            $set('name', "Laporan IMUT {$monthName} {$year}");
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        // Check for existing report with same period before creating
        $existingReport = LaporanImut::where('report_month', $data['report_month'])
            ->where('report_year', $data['report_year'])
            ->first();

        if ($existingReport) {
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

            $monthName = $monthNames[$data['report_month']] ?? $data['report_month'];

            // Show user-friendly notification instead of debug bar
            Notification::make()
                ->title('Laporan Periode Sudah Ada')
                ->body("Laporan untuk periode {$monthName} {$data['report_year']} sudah dibuat dengan nama: \"{$existingReport->name}\"")
                ->warning()
                ->persistent()
                ->send();

            // Throw validation exception to prevent creation
            throw ValidationException::withMessages([
                'report_month' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
                'report_year' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
            ]);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (QueryException $e) {
            // Handle duplicate entry error specifically
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'unique_periode_laporan') !== false) {
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

                $monthName = $monthNames[$data['report_month']] ?? $data['report_month'];

                // Find existing report to show in notification
                $existingReport = LaporanImut::where('report_month', $data['report_month'])
                    ->where('report_year', $data['report_year'])
                    ->first();

                Notification::make()
                    ->title('Laporan Periode Sudah Ada')
                    ->body("Laporan untuk periode {$monthName} {$data['report_year']} sudah dibuat" .
                        ($existingReport ? " dengan nama: \"{$existingReport->name}\"" : '.'))
                    ->warning()
                    ->persistent()
                    // ->actions([
                    //     \Filament\Notifications\Actions\Action::make('lihat')
                    //         ->label('Lihat Laporan Existing')
                    //         ->url($existingReport ?
                    //             route('filament.siimut.resources.laporan-imuts.view', $existingReport->id) :
                    //             route('filament.siimut.resources.laporan-imuts.index')
                    //         )
                    //         ->button(),
                    // ])
                    ->send();

                throw ValidationException::withMessages([
                    'report_month' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
                    'report_year' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
                ]);
            }

            // Re-throw other exceptions
            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        try {
            // 1. CLEANUP: Hapus orphaned ImutPenilaian dari laporan sebelumnya
            // yang punya IMUT data tidak konsisten dengan relasi unit kerja
            $this->cleanupOrphanedPenilaian($this->record);

            // 2. VALIDATE: Cek konsistensi data laporan
            $validationResult = $this->validateLaporanDataConsistency($this->record);

            if (!$validationResult['valid']) {
                Notification::make()
                    ->title('⚠️ Peringatan Data Inconsistency')
                    ->body($validationResult['message'])
                    ->warning()
                    ->persistent()
                    ->send();

                // Log untuk audit trail
                \Log::warning("Laporan {$this->record->id} created with data inconsistency", [
                    'issues' => $validationResult['issues'],
                    'created_by' => Auth::id(),
                ]);
            }

            Notification::make()
                ->title('Proses Penilaian Dimulai')
                ->body('Data sedang diproses di latar belakang...')
                ->status('info')
                ->send();

            dispatch(new \App\Jobs\ProsesPenilaianImut($this->record->id));
        } catch (\Exception $e) {
            \Log::error("Error in CreateLaporanImut::afterCreate", [
                'laporan_id' => $this->record->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('❌ Error Proses Penilaian')
                ->body("Terjadi kesalahan: {$e->getMessage()}")
                ->danger()
                ->persistent()
                ->send();
        }
    }

    /**
     * Hapus orphaned ImutPenilaian yang tidak konsisten dengan relasi unit kerja
     */
    private function cleanupOrphanedPenilaian(LaporanImut $laporan): void
    {
        // Untuk laporan ini, cari penilaian dengan IMUT data yang tidak milik unit kerja
        // Gunakan NOT EXISTS untuk cek validasi dengan lebih clean
        $orphanedCount = DB::table('laporan_unit_kerjas')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporan->id)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('imut_data_unit_kerja')
                    ->whereColumn('imut_data_unit_kerja.unit_kerja_id', 'laporan_unit_kerjas.unit_kerja_id')
                    ->whereColumn('imut_data_unit_kerja.imut_data_id', 'imut_profil.imut_data_id');
            })
            ->count();

        if ($orphanedCount > 0) {
            \Log::warning("Found {$orphanedCount} orphaned ImutPenilaian for laporan {$laporan->id}");
        }
    }

    /**
     * Validasi konsistensi data laporan sebelum proses
     * 
     * @return array ['valid' => bool, 'message' => string, 'issues' => array]
     */
    private function validateLaporanDataConsistency(LaporanImut $laporan): array
    {
        $issues = [];

        foreach ($laporan->unitKerjas as $unitKerja) {
            $unitImutIds = $unitKerja->imutData()->pluck('imut_data.id')->toArray();

            if (empty($unitImutIds)) {
                $issues[] = "Unit Kerja '{$unitKerja->unit_name}' tidak memiliki IMUT data terkait.";
                continue;
            }

            // Cek apakah ada IMUT data di unit yang tidak punya profile valid
            $imutWithoutProfile = $unitKerja->imutData()
                ->get()
                ->filter(function ($imutData) use ($laporan) {
                    return !$this->findValidProfileForReport($imutData, $laporan);
                })
                ->pluck('title')
                ->toArray();

            if (!empty($imutWithoutProfile)) {
                $issues[] = "Unit '{$unitKerja->unit_name}' punya IMUT data tanpa profil valid: " .
                    implode(', ', $imutWithoutProfile);
            }
        }

        return [
            'valid' => empty($issues),
            'message' => empty($issues)
                ? 'Semua data konsisten'
                : 'Ditemukan ' . count($issues) . ' issue data: ' . implode('; ', $issues),
            'issues' => $issues,
        ];
    }

    /**
     * Helper: Cari profile valid untuk periode laporan
     */
    private function findValidProfileForReport(ImutData $imutData, LaporanImut $laporan)
    {
        return $imutData->profiles()
            ->when(!is_null($laporan->assessment_period_start), function ($q) use ($laporan) {
                return $q->where(function ($query) use ($laporan) {
                    $query->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $laporan->assessment_period_start);
                });
            })
            ->when(!is_null($laporan->assessment_period_end), function ($q) use ($laporan) {
                return $q->where(function ($query) use ($laporan) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', $laporan->assessment_period_end);
                });
            })
            ->orderByDesc('version')
            ->first();
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.siimut.resources.laporan-imuts.index') => 'Laporan IMUT',
            null => 'Tambah Data Baru',
        ];
    }
}
