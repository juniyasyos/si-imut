<?php

namespace App\Filament\Traits\ReportDetailAction;

use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use App\Services\Form\FormCalculationService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

trait DetailInfoReport
{
    protected const PROFILE_FIELDS = [
        'imut_data_id',
        'imut_profil',
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
        'data_collection_tool',
        'analysis_plan',
        'target_operator',
        'target_value',
        'analysis_period_type',
        'analysis_period_value',
    ];

    /**
     * Prepare form fill data for profile information display.
     */
    protected function getProfileFormFillData($record): array
    {
        // Get the profile data from the record
        $profile = null;

        // Try to get profile from different possible relationships
        if (isset($record->imut_profile)) {
            $profile = $record->imut_profile;
        } elseif (isset($record->imut_profil_id)) {
            $profile = ImutProfile::find($record->imut_profil_id);
        } elseif (isset($record->imut_data) && isset($record->imut_data->imut_profile)) {
            $profile = $record->imut_data->imut_profile;
        }

        if (!$profile) {
            return [];
        }

        // Fill all profile fields
        $fill = [
            'imut_data_id' => $profile->imut_data_id,
            'imut_profil' => $profile->version,
            'responsible_person' => $profile->responsible_person,
            'indicator_type' => $profile->indicator_type,
            'rationale' => $profile->rationale,
            'objective' => $profile->objective,
            'operational_definition' => $profile->operational_definition,
            'quality_dimension' => $profile->quality_dimension,
            'numerator_formula' => $profile->numerator_formula,
            'denominator_formula' => $profile->denominator_formula,
            'inclusion_criteria' => $profile->inclusion_criteria,
            'exclusion_criteria' => $profile->exclusion_criteria,
            'data_source' => $profile->data_source,
            'data_collection_frequency' => $profile->data_collection_frequency,
            'data_collection_method' => $profile->data_collection_method,
            'sampling_method' => $profile->sampling_method,
            'data_collection_tool' => $profile->data_collection_tool,
            'analysis_plan' => $profile->analysis_plan,
            'target_operator' => $profile->target_operator,
            'target_value' => $profile->target_value,
            'analysis_period_type' => $profile->analysis_period_type,
            'analysis_period_value' => $profile->analysis_period_value,
        ];

        return $fill;
    }

    protected function buildDetailInfo(): Action
    {
        $livewireComponent = $this;

        return Action::make('detail_info')
            ->label('Detail Info')
            ->icon('heroicon-o-information-circle')
            ->color('primary')
            ->slideOver()
            ->modalHeading(fn($record) => ($record->imut_data ?? ''))
            ->modalSubmitActionLabel('Simpan')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            // ->disabled(fn() => $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian'))
            ->mountUsing(function (Form $form, $record) {
                $form->fill($this->getProfileFormFillData($record));
            })
            ->form(function () use ($livewireComponent) {
                return array_merge(
                    self::ImutPenilaianProfileSchema(),
                    self::basicInformationSchemaProfile(),
                    self::operationalDefinitionSchemaProfile(),
                    self::dataAndAnalysisSchemaProfile()
                );
            })
            ->action(function ($record, array $data) {
                $this->updatePenilaianFromAction($record, $data);
            })
            ->successNotificationTitle('Penilaian berhasil disimpan')
            ->after(fn() => $this->dispatch('$refresh'));
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
                    TextInput::make('imut_profil')
                        ->label('Versi Profil IMUT')
                        ->disabled()
                        ->required(),

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

    // ==========================================================================
    // Helper methods for profile schema (local implementations)
    // ==========================================================================

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
            : false;

        return $periodNotClosed
            || (Gate::denies('update_profile_penilaian_imut::penilaian') && Gate::denies('force_editable_imut::penilaian'));
    }

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
