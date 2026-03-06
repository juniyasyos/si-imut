<?php

namespace App\Filament\Forms;

use App\Filament\Resources\ImutDataResource;
use App\Models\RegionType;
use App\Traits\HasPermissionChecks;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImutProfileForm
{
    /**
     * Check if profile field should be disabled based on ImutData creator.
     * ImutProfile has relation to ImutData, so we check imutData->created_by
     *
     * @param  Model|null  $record  ImutProfile record
     * @return bool
     */
    protected static function shouldDisableProfileField(?Model $record): bool
    {
        if (! $record || ! isset($record->imutData)) {
            return false;
        }

        return ($record->imutData->created_by !== Auth::id())
            && ! static::userCan('force_editable_imut::profile');
    }

    /**
     * Summary of make
     *
     * @return Forms\Components\Tabs[]
     */
    public static function make(): array
    {
        return [
            \Filament\Forms\Components\Hidden::make('imut_data_id'),
            Tabs::make('Form Profil Indikator')
                ->tabs([
                    Tab::make('ℹ️ Informasi Dasar')
                        ->schema(self::basicInformationSchema()),

                    Tab::make('🧮 Perhitungan')
                        ->schema(self::operationalDefinitionSchema()),

                    Tab::make('📋 Data Resource')
                        ->schema(self::dataResourceSchema()),

                    Tab::make('🎯 Analisis')
                        ->schema(self::AnalysisSchema()),
                ])
                ->columnSpan(['lg' => 2]),
        ];
    }

    protected static function basicInformationSchema(): array
    {
        return [
            Section::make('Informasi Dasar')
                ->description('Isi data umum indikator mutu profil.')
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('version')
                            ->label('Versi')
                            ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                            ->helperText('Contoh: v1, v2.1')
                            ->required()
                            ->reactive()
                            ->columnSpanFull()
                            ->maxLength(50),

                        TextInput::make('slug')
                            ->label(__('filament-forms::imut-data.fields.slug'))
                            ->readOnly()
                            ->hidden()
                            ->extraAttributes(['class' => 'bg-gray-100 text-gray-500'])
                            ->columnSpan(1),

                        DatePicker::make('valid_from')
                            ->label('Berlaku Mulai')
                            ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                            ->required()
                            ->default(now()->toDateString())
                            ->helperText('Tanggal profil ini mulai berlaku')
                            ->reactive()
                            ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get, ?Model $record) {
                                self::checkForOverlaps($state, $get('valid_until'), $get('imut_data_id'), $record);
                            }),

                        DatePicker::make('valid_until')
                            ->label('Berlaku Sampai')
                            ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                            ->helperText('Kosongkan jika profil berlaku selamanya. Isi jika ingin membatasi masa berlaku.')
                            ->reactive()
                            ->afterOrEqual('valid_from')
                            ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get, ?Model $record) {
                                self::checkForOverlaps($get('valid_from'), $state, $get('imut_data_id'), $record);
                            }),

                        TextInput::make('responsible_person')
                            ->label('Penanggung Jawab')
                            ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                            ->placeholder('Peran yang benanggung jawab')
                            ->required()
                            ->maxLength(255),

                        ToggleButtons::make('indicator_type')
                            ->label('Tipe Indikator')
                            ->disabled(fn(?Model $record) => self::shouldDisableProfileField($record))
                            ->options([
                                'process' => 'Proses',
                                'output' => 'Output',
                                'outcome' => 'Outcome',
                            ])
                            ->default('process')
                            ->icons([
                                'process' => 'heroicon-o-cog',
                                'output' => 'heroicon-o-chart-bar',
                                'outcome' => 'heroicon-o-academic-cap',
                            ])
                            ->colors([
                                'process' => 'warning',
                                'output' => 'info',
                                'outcome' => 'success',
                            ])
                            ->inline()
                            ->required()
                            ->helperText('Pilih jenis indikator yang sesuai.'),
                    ]),
                ]),

            Section::make('Deskripsi Indikator')
                ->description('Uraikan latar belakang, tujuan, dan makna indikator.')
                ->schema([
                    RichEditor::make('rationale')
                        ->label('Rasional')
                        ->disabled(fn(?Model $record) => self::shouldDisableProfileField($record))
                        ->hint('Mengapa indikator ini penting untuk diukur.'),

                    RichEditor::make('objective')
                        ->label('Tujuan')
                        ->disabled(fn(?Model $record) => self::shouldDisableProfileField($record))
                        ->hint('Apa yang ingin dicapai melalui indikator ini.'),

                    RichEditor::make('operational_definition')
                        ->label('Definisi Operasional')
                        ->disabled(fn(?Model $record) => self::shouldDisableProfileField($record))
                        ->hint('Penjelasan rinci istilah dalam indikator.'),

                    TextInput::make('quality_dimension')
                        ->label('Dimensi Mutu')
                        ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                        ->hint('Contoh: Efektivitas, Efisiensi, Aksesibilitas.'),
                ]),
        ];
    }

    protected static function operationalDefinitionSchema(): array
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
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->rows(3)
                                ->required()
                                ->placeholder('Contoh: Jumlah pasien yang menerima layanan X...')
                                ->helperText('Rumus untuk bagian atas (numerator) dari indikator.'),

                            Textarea::make('denominator_formula')
                                ->label('Rumus Penyebut (Denumerator)')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->rows(3)
                                ->required()
                                ->placeholder('Contoh: Jumlah total pasien yang memenuhi syarat...')
                                ->helperText('Rumus untuk bagian bawah (denominator) dari indikator.'),
                        ]),

                    Fieldset::make('📋 Kriteria Data')
                        ->columns(2)
                        ->schema([
                            TextInput::make('inclusion_criteria')
                                ->label('Kriteria Inklusi')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->required()
                                ->placeholder('Contoh: Pasien usia ≥ 18 tahun...')
                                ->helperText('Data yang harus disertakan.'),

                            TextInput::make('exclusion_criteria')
                                ->label('Kriteria Eksklusi')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->required()
                                ->placeholder('Contoh: Pasien tanpa rekam medis lengkap...')
                                ->helperText('Data yang harus dikecualikan dari penghitungan.'),
                        ]),
                ]),
        ];
    }

    protected static function dataResourceSchema(): array
    {
        return [
            Section::make('📥 Pengumpulan')
                ->description('Detail proses pengumpulan data, dan metode')
                ->schema([

                    // === Fieldset: Pengumpulan Data ===
                    Fieldset::make('📋 Informasi Pengumpulan')
                        ->columns(2)
                        ->schema([
                            TextInput::make('data_source')
                                ->label('Sumber Data')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->placeholder('Contoh: EMR, Audit Form, Survey')
                                ->helperText('Sumber utama data indikator ini berasal dari mana.')
                                ->prefixIcon('heroicon-o-server'),

                            TextInput::make('data_collection_frequency')
                                ->label('Frekuensi Pengumpulan')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->placeholder('Contoh: Bulanan, Mingguan')
                                ->helperText('Berapa sering data dikumpulkan.')
                                ->prefixIcon('heroicon-o-calendar-days'),

                            TextInput::make('data_collection_method')
                                ->label('Metode Pengumpulan')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->placeholder('Contoh: Elektronik, Manual, Observasi')
                                ->helperText('Bagaimana proses pengumpulan data dilakukan.')
                                ->prefixIcon('heroicon-o-finger-print'),

                            TextInput::make('sampling_method')
                                ->label('Metode Sampling')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->placeholder('Contoh: Total sampling, Random sampling')
                                ->helperText('Metode pemilihan sampel data untuk dianalisis.')
                                ->prefixIcon('heroicon-o-beaker'),
                        ]),
                ]),
        ];
    }

    protected static function AnalysisSchema(): array
    {
        return [
            Section::make('🔍 Analisis Data')
                ->description('Detail perencanaan analisis indikator mutu.')
                ->schema([
                    // === Fieldset: Detail Analisis ===
                    Fieldset::make('📈 Detail Analisis')
                        ->columns(2)
                        ->schema([
                            ToggleButtons::make('target_operator')
                                ->label('Operator Target')
                                ->disabled(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->options([
                                    '>=' => '≥',
                                    '<=' => '≤',
                                    '=' => '=',
                                ])
                                ->default('=')
                                ->inline()
                                ->helperText('Pilih operator pembanding untuk nilai target.'),

                            TextInput::make('target_value')
                                ->label('🎯 Nilai Target')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->numeric()
                                ->placeholder('Contoh: 90, 95, 100')
                                ->helperText('Target pencapaian kinerja indikator.')
                                ->prefixIcon('heroicon-o-arrow-trending-up'),
                        ]),

                    // === Periode Analisis ===
                    Fieldset::make('🗓️ Periode Analisis')
                        ->columns(2)
                        ->schema([
                            Select::make('analysis_period_type')
                                ->label('Tipe Periode Analisis')
                                ->disabled(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->options([
                                    'mingguan' => 'Mingguan',
                                    'bulanan' => 'Bulanan',
                                ])
                                ->required()
                                ->reactive()
                                ->default('bulanan')
                                ->helperText('Jenis periode yang digunakan dalam analisis.')
                                ->prefixIcon('heroicon-o-clock'),

                            TextInput::make('analysis_period_value')
                                ->label('Nilai Periode')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->numeric()
                                ->required()
                                ->placeholder('Contoh: 1, 3, 6')
                                ->helperText('Angka yang menunjukkan rentang waktu.')
                                ->prefixIcon('heroicon-o-adjustments-horizontal'),
                        ]),

                    // === Alat & Rencana Analisis ===
                    Fieldset::make('🛠️ Alat & Strategi Analisis')
                        ->columns(1)
                        ->schema([
                            Textarea::make('data_collection_tool')
                                ->label('Alat Kumpul Data')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->placeholder('Contoh: Kuesioner, Google Form, EMR, Form Audit')
                                ->rows(2)
                                ->helperText('Alat bantu atau instrumen yang digunakan dalam proses pengumpulan.'),

                            Textarea::make('analysis_plan')
                                ->label('Rencana Analisis')
                                ->readOnly(fn(?Model $record) => self::shouldDisableProfileField($record))
                                ->placeholder('Langkah-langkah bagaimana data akan dianalisis untuk mengevaluasi indikator.')
                                ->rows(3)
                                ->helperText('Ceritakan secara ringkas bagaimana analisis dilakukan.'),
                        ]),
                ]),
        ];
    }

    /**
     * Check for overlapping profiles and show warning notification
     */
    protected static function checkForOverlaps($validFrom, $validUntil, $imutDataId, ?Model $record): void
    {
        if (!$validFrom || !$imutDataId) {
            return;
        }

        try {
            $query = \App\Models\ImutProfile::where('imut_data_id', $imutDataId);

            // Exclude current record if editing
            if ($record && $record->exists) {
                $query->where('id', '!=', $record->id);
            }

            $validUntilCheck = $validUntil ?: '9999-12-31';

            $overlapping = $query->where(function ($q) use ($validFrom, $validUntilCheck) {
                $q->where(function ($subQ) use ($validFrom, $validUntilCheck) {
                    // Check if new period overlaps with existing periods
                    $subQ->where('valid_from', '<=', $validUntilCheck)
                        ->where(function ($innerQ) use ($validFrom) {
                            $innerQ->whereNull('valid_until')
                                ->orWhere('valid_until', '>=', $validFrom);
                        });
                });
            })->get();

            if ($overlapping->isNotEmpty()) {
                $conflictDetails = $overlapping->map(function ($profile) {
                    $until = $profile->valid_until ? $profile->valid_until->format('d/m/Y') : 'selamanya';
                    return "• {$profile->version} ({$profile->valid_from->format('d/m/Y')} s/d {$until})";
                })->join("\n");

                \Filament\Notifications\Notification::make()
                    ->title('⚠️ Peringatan: Periode Bertumpang Tindih!')
                    ->body("Periode yang Anda masukkan bertumpang tindih dengan profil lain:\n\n{$conflictDetails}\n\nHarap sesuaikan tanggal untuk menghindari konflik.")
                    ->warning()
                    ->persistent()
                    ->send();
            }
        } catch (\Exception $e) {
            // Silently fail to avoid blocking form interaction
            logger()->error('Error checking overlaps in form: ' . $e->getMessage());
        }
    }
}
