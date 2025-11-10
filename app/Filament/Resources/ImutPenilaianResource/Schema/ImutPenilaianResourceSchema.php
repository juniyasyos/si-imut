<?php

namespace App\Filament\Resources\ImutPenilaianResource\Schema;

use App\Filament\Resources\ImutPenilaianResource;
use App\Models\ImutProfile;
use App\Services\Form\FormCalculationService;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImutPenilaianResourceSchema extends ImutPenilaianResource
{
    /**
     * Field names yang akan di-populate dari ImutProfile
     */
    protected const PROFILE_FIELDS = [
        'imut_data_id',
        'responsible_person',
        'indicator_type',
        'rationale',
        'objective',
        'operational_definition',
        'quality_dimension',
        'numerator_formula',
        'denominator_formula',
        'inclusion_criteria',
        'exclusion_criteria',
        'data_source',
        'data_collection_frequency',
        'data_collection_method',
        'sampling_method',
        'analysis_period_type',
        'analysis_period_value',
        'target_operator',
        'target_value',
        'start_periode',
        'end_periode',
        'data_collection_tool',
        'analysis_plan',
    ];

    // ============================================================================
    // MAIN SCHEMA BUILDERS
    // ============================================================================

    /**
     * Schema utama untuk form penilaian IMUT
     */
    public static function make(): array
    {
        return [
            Tabs::make('Penilaian IMUT')
                ->tabs([
                    Tab::make('Profil IMUT')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            ...self::ImutPenilaianProfileSchema(),
                            ...self::basicInformationSchemaProfile(),
                            ...self::operationalDefinitionSchemaProfile(),
                            ...self::dataAndAnalysisSchemaProfile(),
                        ]),
                    Tab::make('Penilaian')
                        ->icon('heroicon-o-pencil')
                        ->schema(self::penilaianFormSchema()),
                ])
                ->columnSpanFull(),
        ];
    }

    // ============================================================================
    // PUBLIC API METHODS
    // ============================================================================

    /**
     * Check if period is closed (for static context without livewire instance)
     * Returns TRUE if period is closed (cannot edit unless force permission)
     */
    public static function isPeriodClosed(): bool
    {
        // For now, we don't check period closure in static context
        // Only check if user has force edit permission
        return false; // Assume period is always open for static checks
    }

    /**
     * Check if field should be readonly (cannot edit numerator/denominator)
     * Returns TRUE if field should be readonly (disabled for editing)
     */
    public static function canEditNumeratorDenominatorStatic(): bool
    {
        return self::shouldLockPenilaian();
    }

    /**
     * Check if recommendation field should be disabled
     * Returns TRUE if field should be disabled
     */
    public static function canCreateRecommendation(): bool
    {
        return self::userCannot('create_recommendation_penilaian_imut::penilaian');
    }

    /**
     * Schema untuk form perhitungan penilaian (digunakan di modal/livewire)
     */
    public static function penilaianCalculationSchema(): array
    {
        return [
            self::buildNumericInputField('numerator_value', 'Numerator'),
            self::buildNumericInputField('denominator_value', 'Denominator'),
            self::buildResultField(),
            self::buildDocumentUploadField(),
        ];
    }

    /**
     * Schema untuk analisis dan rekomendasi (digunakan di modal/livewire)
     */
    public static function penilaianAnalysisSchema(): array
    {
        return [
            self::buildAnalysisField(),
            self::buildRecommendationField(),
        ];
    }

    // ============================================================================
    // PROFILE SCHEMA SECTIONS
    // ============================================================================

    /**
     * Schema untuk informasi profil IMUT
     */
    protected static function ImutPenilaianProfileSchema(): array
    {
        return [
            Section::make('Informasi Profil')
                ->description('Pilih profil dan standar IMUT yang sesuai.')
                ->schema([
                    Hidden::make('imut_data_id'),
                    Select::make('imut_profil_id')
                        ->label('Versi Profil IMUT')
                        ->options(fn($get) => self::profileOptions($get('imut_data_id')))
                        ->disabled(fn($livewire) => self::shouldDisableProfileSelection($livewire))
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required()
                        ->placeholder('Pilih versi profil')
                        ->afterStateUpdated(function ($state, callable $set) {
                            $profile = ImutProfile::find($state);

                            self::populateProfileFields($set, $profile);
                        }),

                    Select::make('target_operator')
                        ->label('🎯 Target Nilai')
                        ->options(fn($get) => self::targetOperatorOptions($get('target_value')))
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->columns(2),
        ];
    }

    /**
     * Schema untuk informasi dasar profil
     */
    protected static function basicInformationSchemaProfile(): array
    {
        return [
            Section::make('Informasi Dasar')
                ->description('Isi data umum indikator mutu profil.')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('responsible_person')
                            ->label('Penanggung Jawab')
                            ->placeholder('Nama lengkap penanggung jawab')
                            ->required()
                            ->readOnly()
                            ->columnSpan(1)
                            ->maxLength(255),

                        ToggleButtons::make('indicator_type')
                            ->label('Tipe Indikator')
                            ->disabled()
                            ->inline()
                            ->options([
                                'process' => 'Proses',
                                'output' => 'Output',
                                'outcome' => 'Outcome',
                            ])
                            ->icons([
                                'process' => 'heroicon-o-cog',
                                'output' => 'heroicon-o-chart-bar',
                                'outcome' => 'heroicon-o-academic-cap',
                            ]),
                        TextInput::make('rationale')
                            ->label('Rasional')
                            ->placeholder('Jelaskan alasan pemilihan indikator')
                            ->required()
                            ->readOnly()
                            ->columnSpanFull(),

                        TextInput::make('objective')
                            ->label('Tujuan')
                            ->placeholder('Apa tujuan dari indikator ini?')
                            ->required()
                            ->readOnly()
                            ->columnSpanFull(),

                        TextInput::make('operational_definition')
                            ->label('Definisi Operasional')
                            ->required()
                            ->readOnly()
                            ->placeholder('Deskripsikan definisi operasional indikator')
                            ->columnSpanFull(),

                        TextInput::make('quality_dimension')
                            ->label('Dimensi Mutu')
                            ->readOnly(),
                    ]),
                ]),
        ];
    }

    /**
     * Schema untuk definisi operasional profil
     */
    protected static function operationalDefinitionSchemaProfile(): array
    {
        return [
            Section::make('💡 Perhitungan Indikator')
                ->collapsed()
                ->description('Masukkan rumus dan kriteria yang digunakan untuk menghitung indikator mutu.')
                ->schema([
                    Fieldset::make('🧮 Rumus Perhitungan')
                        ->columns(1)
                        ->schema([
                            Textarea::make('numerator_formula')
                                ->label('Rumus Pembilang (Numerator)')
                                ->rows(3)
                                ->readOnly()
                                ->required()
                                ->placeholder('Contoh: Jumlah pasien yang menerima layanan X...')
                                ->helperText('Rumus untuk bagian atas (numerator) dari indikator.'),

                            Textarea::make('denominator_formula')
                                ->label('Rumus Penyebut (Denumerator)')
                                ->rows(3)
                                ->readOnly()
                                ->required()
                                ->placeholder('Contoh: Jumlah total pasien yang memenuhi syarat...')
                                ->helperText('Rumus untuk bagian bawah (denominator) dari indikator.'),
                        ]),

                    Fieldset::make('📋 Kriteria Data')
                        ->columns(2)
                        ->schema([
                            TextInput::make('inclusion_criteria')
                                ->label('Kriteria Inklusi')
                                ->required()
                                ->readOnly()
                                ->placeholder('Contoh: Pasien usia ≥ 18 tahun...')
                                ->helperText('Data yang harus disertakan.'),

                            TextInput::make('exclusion_criteria')
                                ->label('Kriteria Eksklusi')
                                ->required()
                                ->readOnly()
                                ->placeholder('Contoh: Pasien tanpa rekam medis lengkap...')
                                ->helperText('Data yang harus dikecualikan dari penghitungan.'),
                        ]),
                ]),
        ];
    }

    /**
     * Schema untuk data dan analisis profil
     */
    protected static function dataAndAnalysisSchemaProfile(): array
    {
        return [
            Section::make('📥 Pengumpulan & 🔍 Analisis Data')
                ->collapsed()
                ->description('Detail proses pengumpulan data, metode, dan perencanaan analisis indikator mutu.')
                ->schema([
                    Fieldset::make('📋 Informasi Pengumpulan')
                        ->columns(2)
                        ->schema([
                            TextInput::make('data_source')
                                ->label('Sumber Data')
                                ->placeholder('Contoh: EMR, Audit Form, Survey')
                                ->readOnly()
                                ->helperText('Sumber utama data indikator ini berasal dari mana.')
                                ->prefixIcon('heroicon-o-server'),

                            TextInput::make('data_collection_frequency')
                                ->label('Frekuensi Pengumpulan')
                                ->placeholder('Contoh: Bulanan, Mingguan')
                                ->helperText('Berapa sering data dikumpulkan.')
                                ->readOnly()
                                ->prefixIcon('heroicon-o-calendar-days'),

                            TextInput::make('data_collection_method')
                                ->label('Metode Pengumpulan')
                                ->placeholder('Contoh: Elektronik, Manual, Observasi')
                                ->readOnly()
                                ->helperText('Bagaimana proses pengumpulan data dilakukan.')
                                ->prefixIcon('heroicon-o-finger-print'),

                            TextInput::make('sampling_method')
                                ->label('Metode Sampling')
                                ->placeholder('Contoh: Total sampling, Random sampling')
                                ->readOnly()
                                ->helperText('Metode pemilihan sampel data untuk dianalisis.')
                                ->prefixIcon('heroicon-o-beaker'),
                        ]),

                    Fieldset::make('📈 Detail Analisis')
                        ->columns(2)
                        ->schema([
                            TextInput::make('analysis_period_type')
                                ->label('Tipe Periode Analisis')
                                ->placeholder('Contoh: Bulanan, Semester')
                                ->readOnly()
                                ->helperText('Jenis periode yang digunakan dalam analisis.')
                                ->prefixIcon('heroicon-o-clock'),

                            TextInput::make('analysis_period_value')
                                ->label('Nilai Periode')
                                ->numeric()
                                ->readOnly()
                                ->placeholder('Contoh: 1, 3, 6')
                                ->helperText('Angka yang menunjukkan rentang waktu (dalam bulan/minggu).')
                                ->prefixIcon('heroicon-o-adjustments-horizontal'),

                            TextInput::make('target_value')
                                ->label('🎯 Nilai Target')
                                ->numeric()
                                ->readOnly()
                                ->placeholder('Contoh: 90, 95, 100')
                                ->helperText('Target pencapaian kinerja indikator.')
                                ->prefixIcon('heroicon-o-arrow-trending-up'),
                        ]),

                    Fieldset::make('🛠️ Alat & Strategi Analisis')
                        ->columns(1)
                        ->schema([
                            Textarea::make('data_collection_tool')
                                ->label('Alat Kumpul Data')
                                ->placeholder('Contoh: Kuesioner, Google Form, EMR, Form Audit')
                                ->rows(2)
                                ->readOnly()
                                ->helperText('Alat bantu atau instrumen yang digunakan dalam proses pengumpulan.'),

                            Textarea::make('analysis_plan')
                                ->label('Rencana Analisis')
                                ->placeholder('Langkah-langkah bagaimana data akan dianalisis untuk mengevaluasi indikator.')
                                ->rows(3)
                                ->readOnly()
                                ->helperText('Ceritakan secara ringkas bagaimana analisis dilakukan.'),
                        ]),
                ]),
        ];
    }

    /**
     * Schema lengkap untuk form penilaian
     */
    public static function penilaianFormSchema(): array
    {
        return [
            Hidden::make('penilaian_id'),
            self::buildPerhitunganSection(),
            self::buildAnalysisSection(),
        ];
    }

    /**
     * Update result calculation
     */
    public static function updateResult(callable $set, callable $get): void
    {
        $formCalculationService = app(FormCalculationService::class);
        $formCalculationService->updatePenilaianResult($set, $get);
    }

    // ============================================================================
    // PERMISSION HELPERS
    // ============================================================================

    protected static function userCan(string $permission): bool
    {
        $user = Auth::user();
        return $user && Gate::forUser($user)->allows($permission);
    }

    protected static function userCannot(string $permission): bool
    {
        return ! self::userCan($permission);
    }

    protected static function hasForceEdit(): bool
    {
        return self::userCan('force_editable_imut::penilaian');
    }

    /**
     * Determine if numerator/denominator edits should be locked.
     */
    protected static function shouldLockPenilaian(?object $livewire = null): bool
    {
        $isPeriodClosed = $livewire && method_exists($livewire, 'isLaporanPeriodClosed')
            ? $livewire->isLaporanPeriodClosed()
            : self::isPeriodClosed();

        $isForceEditable = self::hasForceEdit();

        return ($isPeriodClosed && ! $isForceEditable)
            || self::userCannot('update_numerator_denominator_imut::penilaian');
    }

    protected static function shouldDisableRecommendationField(): bool
    {
        return self::userCannot('create_recommendation_penilaian_imut::penilaian') && ! self::hasForceEdit();
    }

    // ============================================================================
    // FIELD BUILDERS
    // ============================================================================

    protected static function buildNumericInputField(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->placeholder('0.00')
            ->nullable()
            ->debounce(1000)
            ->readOnly(fn($livewire) => self::shouldLockPenilaian($livewire))
            ->afterStateUpdated(fn(callable $set, callable $get) => self::updateResult($set, $get));
    }

    protected static function buildResultField(): TextInput
    {
        return TextInput::make('result_operation')
            ->label('Result (%)')
            ->numeric()
            ->placeholder('0.00')
            ->readOnly()
            ->debounce(1000)
            ->dehydrated(false)
            ->afterStateHydrated(fn(callable $set, callable $get) => self::updateResult($set, $get));
    }

    protected static function buildDocumentUploadField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('document_upload')
            ->label('Unggah Dokumen Pendukung')
            ->collection(fn(callable $get) => $get('selected_collection') ?? 'default')
            ->directory(fn(callable $get) => 'uploads/imut-documents/' . ($get('selected_collection') ?? 'default'))
            ->openable()
            ->downloadable()
            ->maxSize(20480)
            ->preserveFilenames()
            ->previewable(true)
            ->columnSpanFull()
            ->disabled(fn($livewire) => self::shouldLockPenilaian($livewire))
            ->acceptedFileTypes([
                'application/pdf',
                'image/*',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->helperText('File yang didukung: PDF, Word, Excel, Gambar. Maks. 20MB');
    }

    protected static function buildAnalysisField(): Textarea
    {
        return Textarea::make('analysis')
            ->label('Analisis')
            ->rows(4)
            ->nullable()
            ->readOnly(fn($livewire) => self::shouldLockPenilaian($livewire))
            ->placeholder('Tuliskan hasil analisis (opsional)...')
            ->columnSpanFull();
    }

    protected static function buildRecommendationField(): Textarea
    {
        return Textarea::make('recommendations')
            ->label('Rekomendasi')
            ->nullable()
            ->disabled(fn() => self::shouldDisableRecommendationField())
            ->rows(4)
            ->placeholder('Berikan saran atau rekomendasi (opsional)...')
            ->columnSpanFull();
    }

    protected static function buildPerhitunganSection(): Section
    {
        return Section::make('Perhitungan')
            ->schema([
                self::buildNumericInputField('numerator_value', 'Numerator')
                    ->readOnly(fn($livewire) => self::shouldLockPenilaian($livewire)),

                self::buildNumericInputField('denominator_value', 'Denominator')
                    ->readOnly(fn($livewire) => self::shouldLockPenilaian($livewire)),

                self::buildResultField(),

                self::buildDocumentUploadField(),
            ])
            ->columns(3);
    }

    protected static function buildAnalysisSection(): Section
    {
        return Section::make('Analisis dan Rekomendasi')
            ->schema([
                self::buildAnalysisField()
                    ->readOnly(fn($livewire) => self::shouldLockPenilaian($livewire)),

                self::buildRecommendationField(),
            ]);
    }

    // ============================================================================
    // OPTION BUILDERS
    // ============================================================================

    /**
     * Build Versi Profil options for the select field.
     */
    protected static function profileOptions($imutDataId): array
    {
        if (! $imutDataId) {
            return [];
        }

        return ImutProfile::where('imut_data_id', $imutDataId)
            ->get()
            ->mapWithKeys(fn($profile) => [$profile->id => (string) $profile->version])
            ->toArray();
    }

    protected static function shouldDisableProfileSelection(?object $livewire): bool
    {
        $periodNotClosed = $livewire && method_exists($livewire, 'isLaporanPeriodClosed')
            ? ! $livewire->isLaporanPeriodClosed()
            : ! self::isPeriodClosed();

        return $periodNotClosed
            || (self::userCannot('update_profile_penilaian_imut::penilaian')
                && self::userCannot('force_editable_imut::penilaian'));
    }

    // ============================================================================
    // DATA POPULATION HELPERS
    // ============================================================================

    protected static function populateProfileFields(callable $set, ?ImutProfile $profile): void
    {
        if (! $profile) {
            self::resetProfileFields($set);

            return;
        }

        foreach (self::PROFILE_FIELDS as $field) {
            $set($field, $profile->{$field} ?? null);
        }
    }

    protected static function resetProfileFields(callable $set): void
    {
        foreach (self::PROFILE_FIELDS as $field) {
            $set($field, null);
        }
    }

    protected static function targetOperatorOptions($targetValue): array
    {
        $value = $targetValue ?? '-';

        return [
            '>=' => "≥ $value",
            '<=' => "≤ $value",
            '>' => "> $value",
            '<' => "< $value",
            '=' => "= $value",
        ];
    }
}
