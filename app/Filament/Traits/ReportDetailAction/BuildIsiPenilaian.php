<?php

namespace App\Filament\Traits\ReportDetailAction;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Services\Form\FormCalculationService;
use Carbon\Carbon;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

trait BuildIsiPenilaian
{
    protected function buildIsiPenilaianAction(): Action
    {
        $livewireComponent = $this;

        return Action::make('isi_penilaian')
            ->label('Isi Penilaian')
            ->icon('heroicon-o-pencil-square')
            ->color('primary')
            ->hiddenLabel()
            ->button()
            ->slideOver()
            ->modalHeading(fn($record) => ($record->imut_data ?? ''))
            ->modalSubmitActionLabel('Simpan')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            // ->disabled(fn() => $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian'))
            ->mountUsing(function (Form $form, $record) {
                $form->fill($this->getPenilaianFormFillData($record));
            })
            ->form(function ($record) use ($livewireComponent) {
                // Get record to build table view parameters
                $formTemplateId = null;
                $imutProfileId = null;
                $unitKerjaId = null;
                $period = null;

                if ($record) {
                    $formTemplateId = optional($record->profile)->activeFormTemplate?->id;
                    $imutProfileId = optional($record->profile)->id;
                    $unitKerjaId = optional($record->laporanUnitKerja)->unit_kerja_id;
                    $laporan = optional($record->laporanUnitKerja)->laporanImut;
                    if ($laporan) {
                        $period = sprintf('%04d-%02d', $laporan->report_year, $laporan->report_month);
                    }
                }

                return [
                    Section::make('Perhitungan')
                        ->schema($this->buildPerhitunganSchemaForAction($livewireComponent))
                        ->columns(3),

                    Section::make('Unggah Bukti Pendukung')
                        ->visible(function ($record) {
                            // Show media upload if:
                            // 1. Not monthly data (original logic) OR
                            // 2. Has existing media files uploaded
                            $hasExistingMedia = false;
                            $penilaian = \App\Models\ImutPenilaian::find($record->id);
                            if ($penilaian) {
                                $hasExistingMedia = $penilaian->getMedia('*')->count() > 0;
                            }

                            return !$record->profile->imutData->is_monthly || $hasExistingMedia;
                        })
                        ->schema($this->getMediaUploadFieldForAction($livewireComponent)),

                    Section::make('Data Pendukung')
                        ->visible(function (callable $get, $record) {
                            // Show data pendukung if:
                            // 1. Is monthly data (original logic) AND
                            // 2. Has NO existing media files uploaded
                            $hasExistingMedia = false;
                            $penilaian = \App\Models\ImutPenilaian::find($record->id);
                            if ($penilaian) {
                                // dd($penilaian->getMedia('*'));
                                $hasExistingMedia = $penilaian->getMedia('*')->count() > 0;
                            }

                            return $record->profile->imutData->is_monthly && !$hasExistingMedia;
                        })
                        ->description('Tidak ada dokumen pendukung. Lihat data laporan harian untuk informasi lebih detail.')
                        ->schema([
                            \Filament\Forms\Components\View::make('filament.forms.components.alternative-data-section')
                                ->columnSpanFull()
                                ->viewData(fn($record) => [
                                    'formTemplateId' => $formTemplateId,
                                    'imutProfileId' => $imutProfileId,
                                    'unitKerjaId' => $unitKerjaId,
                                    'period' => $period,
                                    'laporanId' => $laporan?->id,
                                ]),
                        ]),

                    Section::make('Analisis dan Rekomendasi')
                        ->schema($this->buildAnalysisSchemaForAction($livewireComponent)),
                ];
            })
            ->action(function ($record, array $data) {
                $this->updatePenilaianFromAction($record, $data);
            })
            ->successNotificationTitle('Penilaian berhasil disimpan')
            ->after(fn() => $this->dispatch('$refresh'));
    }

    protected function buildPerhitunganSchemaForAction($livewireComponent): array
    {
        $shouldLock = !$livewireComponent->createAnalisistAndRecomendation();

        if (Auth::user()?->hasRole(['tim_mutu', 'super_admin'])) {

            return [
                TextInput::make('numerator_value')
                    ->label('Numerator')
                    ->numeric()
                    ->placeholder('0.00')
                    ->nullable()
                    ->debounce(1000)
                    ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

                TextInput::make('denominator_value')
                    ->label('Denominator')
                    ->numeric()
                    ->placeholder('0.00')
                    ->nullable()
                    ->debounce(1000)
                    ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

                TextInput::make('result_operation')
                    ->label('Result (%)')
                    ->numeric()
                    ->placeholder('0.00')
                    ->readOnly()
                    ->debounce(1000)
                    ->dehydrated(false)
                    ->afterStateHydrated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),
            ];
        } else {
            return [
                TextInput::make('numerator_value')
                    ->label('Numerator')
                    ->numeric()
                    ->placeholder('0.00')
                    ->nullable()
                    ->debounce(1000)
                    ->readOnly($shouldLock)
                    ->disabled(fn($record) => $record->profile->imutData->is_monthly)
                    ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

                TextInput::make('denominator_value')
                    ->label('Denominator')
                    ->numeric()
                    ->placeholder('0.00')
                    ->nullable()
                    ->debounce(1000)
                    ->readOnly($shouldLock)
                    ->disabled(fn($record) => $record->profile->imutData->is_monthly)
                    ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

                TextInput::make('result_operation')
                    ->label('Result (%)')
                    ->numeric()
                    ->placeholder('0.00')
                    ->readOnly()
                    ->debounce(1000)
                    ->dehydrated(false)
                    ->afterStateHydrated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),
            ];
        }
    }

    protected function buildAnalysisSchemaForAction($livewireComponent): array
    {
        $shouldLock = !$livewireComponent->createAnalisistAndRecomendation();

        if (Auth::user()?->hasRole(['tim_mutu', 'super_admin'])) {
            return [
                RichEditor::make('analysis')
                    ->label('Analisis')
                    ->required(!$shouldLock)
                    ->minLength(20)
                    ->maxLength(100000)
                    ->live(onBlur: true)
                    ->placeholder('Tuliskan hasil analisis lengkap minimal 20 karakter. Contoh: Berdasarkan data yang terkumpul, tingkat kepatuhan cuci tangan masih rendah karena...')
                    ->helperText(function ($state) {
                        $length = strlen($state ?? '');
                        $remaining = max(0, 20 - $length);
                        if ($remaining > 0) {
                            return "Minimal 20 karakter. Kurang {$remaining} karakter lagi. ({$length}/20)";
                        }
                        return "Karakter: {$length}/100000";
                    })
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'redo',
                        'underline',
                        'undo',
                    ]),

                RichEditor::make('recommendations')
                    ->label('Rekomendasi')
                    ->required(!$shouldLock)
                    ->minLength(20)
                    ->maxLength(100000)
                    ->live(onBlur: true)
                    ->placeholder('Berikan rekomendasi tindak lanjut minimal 20 karakter. Contoh: Disarankan untuk meningkatkan sosialisasi protokol cuci tangan dan melakukan monitoring...')
                    ->helperText(function ($state) {
                        $length = strlen($state ?? '');
                        $remaining = max(0, 20 - $length);
                        if ($remaining > 0) {
                            return "Minimal 20 karakter. Kurang {$remaining} karakter lagi. ({$length}/20)";
                        }
                        return "Karakter: {$length}/100000";
                    })
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'redo',
                        'underline',
                        'undo',
                    ]),
            ];
        }
        return [
            RichEditor::make('analysis')
                ->label('Analisis')
                ->required(!$shouldLock)
                ->minLength(20)
                ->maxLength(100000)
                ->readOnly($shouldLock)
                ->live(onBlur: true)
                ->placeholder('Tuliskan hasil analisis lengkap minimal 20 karakter. Contoh: Berdasarkan data yang terkumpul, tingkat kepatuhan cuci tangan masih rendah karena...')
                ->helperText(function ($state) {
                    $length = strlen($state ?? '');
                    $remaining = max(0, 20 - $length);
                    if ($remaining > 0) {
                        return "Minimal 20 karakter. Kurang {$remaining} karakter lagi. ({$length}/20)";
                    }
                    return "Karakter: {$length}/100000";
                })
                ->columnSpanFull()
                ->toolbarButtons([
                    'bold',
                    'bulletList',
                    'italic',
                    'orderedList',
                    'redo',
                    'underline',
                    'undo',
                ]),

            RichEditor::make('recommendations')
                ->label('Rekomendasi')
                ->required(!$shouldLock)
                ->minLength(20)
                ->maxLength(100000)
                ->readOnly($shouldLock)
                ->live(onBlur: true)
                ->placeholder('Berikan rekomendasi tindak lanjut minimal 20 karakter. Contoh: Disarankan untuk meningkatkan sosialisasi protokol cuci tangan dan melakukan monitoring...')
                ->helperText(function ($state) {
                    $length = strlen($state ?? '');
                    $remaining = max(0, 20 - $length);
                    if ($remaining > 0) {
                        return "Minimal 20 karakter. Kurang {$remaining} karakter lagi. ({$length}/20)";
                    }
                    return "Karakter: {$length}/100000";
                })
                ->columnSpanFull()
                ->toolbarButtons([
                    'bold',
                    'bulletList',
                    'italic',
                    'orderedList',
                    'redo',
                    'underline',
                    'undo',
                ]),
        ];
    }

    protected function getMediaUploadFieldForAction($livewireComponent): array
    {
        $shouldLock = !$livewireComponent->createAnalisistAndRecomendation();

        return [
            SpatieMediaLibraryFileUpload::make('document_upload')
                ->label('Unggah Dokumen Pendukung')
                ->collection(fn(callable $get) => $get('selected_collection'))
                ->disk(config('media-library.disk_name', 's3'))
                ->disabled($shouldLock)
                ->directory(function (callable $get, $record) {
                    if (!$record) {
                        return '';
                    }

                    $penilaian = ImutPenilaian::find($record->id);
                    $laporan = $penilaian?->laporanUnitKerja?->laporanImut;

                    if ($laporan) {
                        // Format: Maret 2026
                        $bulanNama = Carbon::createFromDate(
                            $laporan->report_year,
                            $laporan->report_month,
                            1
                        )->locale('id')->translatedFormat('F Y');

                        return $bulanNama;
                    }

                    return '';
                })
                ->openable()
                ->downloadable()
                ->maxSize(20480)
                ->preserveFilenames()
                ->previewable(true)
                ->columnSpanFull()
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/*',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->helperText('File yang didukung: PDF, Word, Excel, Gambar. Maks. 20MB')
            // ->customProperties(fn(callable $get) => [
            //     'directory' => $livewireComponent->getUploadDirectory($get('selected_collection'))
            // ])
        ];
    }

    protected function updateResultForAction(callable $set, callable $get): void
    {
        $formCalculationService = app(FormCalculationService::class);
        $formCalculationService->updatePenilaianResult($set, $get);
    }

    /**
     * Prepare form fill data for a given record.
     * If $includeMedia is false, do not include media-related fields.
     */
    protected function getPenilaianFormFillData($record, bool $includeMedia = true): array
    {
        $penilaian = ImutPenilaian::find($record->id);

        $numerator = $record->numerator_value ?? null;
        $denominator = $record->denominator_value ?? null;
        $analysis = $record->analysis ?? '';
        $recommendations = $record->recommendations ?? '';

        $fill = [
            'numerator_value'   => $numerator,
            'denominator_value' => $denominator,
            'analysis'          => $analysis,
            'recommendations'   => $recommendations,
        ];

        if ($includeMedia && $penilaian) {
            $selected_collection = $this->getOrCreateLaporanImutFolder($penilaian);
            $fill['selected_collection'] = $selected_collection;
        }

        return $fill;
    }

    /**
     * Update penilaian from action data.
     */
    protected function updatePenilaianFromAction($record, array $data): void
    {
        $penilaian = ImutPenilaian::find($record->id);

        if (! $penilaian) {
            return;
        }

        $selected_collection = $this->getOrCreateLaporanImutFolder($penilaian);

        $update = [
            'numerator_value'   => $data['numerator_value'] ?? null,
            'denominator_value' => $data['denominator_value'] ?? null,
            'analysis'          => $data['analysis'] ?? null,
            'recommendations'   => $data['recommendations'] ?? null,
            'selected_collection' => $selected_collection,
        ];
        if ($record->profile->imutData->is_monthly) {
            // For monthly data, we only update analysis and recommendations, not the numerator/denominator
            $update = [
                'analysis' => $data['analysis'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
            ];
        } else {
            $update = [
                'numerator_value'   => $data['numerator_value'] ?? null,
                'denominator_value' => $data['denominator_value'] ?? null,
                'analysis'          => $data['analysis'] ?? null,
                'recommendations'   => $data['recommendations'] ?? null,
                'selected_collection' => $selected_collection,
            ];
        }

        $penilaian->update($update);

        // Attach uploaded media to Folder model
        $this->attachUploadedMediaToFolder($selected_collection);
    }

    /**
     * Attach unattached media to Folder model
     * This ensures that newly uploaded files via SpatieMediaLibraryFileUpload
     * are properly associated with the Folder in the database
     */
    protected function attachUploadedMediaToFolder(string $folderCollection): void
    {
        // Get the folder by collection name
        $folder = Folder::where('collection', $folderCollection)->first();

        if (!$folder) {
            return;
        }

        // Get the media model class
        $mediaModel = class_exists('Spatie\MediaLibrary\MediaCollections\Models\Media')
            ? 'Spatie\MediaLibrary\MediaCollections\Models\Media'
            : 'Spatie\Permission\Models\Media';

        // Find unattached media with this collection_name
        // Unattached media will have NULL model_id and model_type
        $unattachedMedia = $mediaModel::where('collection_name', $folderCollection)
            ->whereNull('model_id')
            ->whereNull('model_type')
            ->get();

        // Attach each unattached media file to the folder
        foreach ($unattachedMedia as $media) {
            $media->update([
                'model_id' => $folder->id,
                'model_type' => Folder::class,
            ]);
        }
    }

    /**
     * Build table view URL for a record
     */
    protected function buildTableViewUrl($record): string
    {
        $formTemplateId = optional($record->profile)->activeFormTemplate?->id;
        $imutProfileId = optional($record->profile)->id;
        $unitKerjaId = optional($record->laporanUnitKerja)->unit_kerja_id;
        $laporan = optional($record->laporanUnitKerja)->laporanImut;

        $url = route('table-view');
        if ($formTemplateId) {
            $params = ['form_template_id' => $formTemplateId];
            if ($imutProfileId) {
                $params['imut_profile_id'] = $imutProfileId;
            }
            if ($unitKerjaId) {
                $params['unit_kerja_id'] = $unitKerjaId;
            }
            if ($laporan) {
                $params['period'] = sprintf('%04d-%02d', $laporan->report_year, $laporan->report_month);
            }
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    public function isLaporanPeriodClosed(): bool
    {
        // Ensure laporan is loaded
        if (!$this->laporan && !$this->loadLaporan()) {
            return false;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($this->laporan->assessment_period_end);

        // During assessment period (including end date): not editable for analysis
        if ($today->lte($endDate)) {
            return false;
        }

        return true;
    }

    public function createAnalisistAndRecomendation(): bool
    {
        if (!$this->laporan && !$this->loadLaporan()) {
            return false;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($this->laporan->assessment_period_end);
        $endDateWithGrace = $endDate->copy()->addDays(
            $this->laporan->recommendation_analysis_duration ?? 0
        );

        // True hanya jika di antara endDate dan endDateWithGrace
        if ($today->gt($endDate) && $today->lte($endDateWithGrace)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the laporan is editable for analysis and recommendations.
     *
     * Logic:
     * - During assessment period (up to end date): NOT editable
     * - After end date: Editable within recommendation_analysis_duration days
     *
     * @return bool
     */
    public function isLaporanEditable(): bool
    {
        // Ensure laporan is loaded
        if (!$this->laporan && !$this->loadLaporan()) {
            return false;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($this->laporan->assessment_period_end);

        // During assessment period (including end date): not editable for analysis
        if ($today->lte($endDate)) {
            return false;
        }

        // After assessment period: editable within analysis duration window
        $analysisDuration = $this->laporan->recommendation_analysis_duration ?? 0;
        $analysisDeadline = $endDate->copy()->addDays($analysisDuration);

        return $today->lte($analysisDeadline);
    }

    /**
     * Get or create "Laporan IMUT" folder under unit kerja folder
     * Returns the UUID of the Laporan IMUT folder
     */
    protected function getOrCreateLaporanImutFolder($penilaian): string
    {
        $unitKerja = $penilaian->laporanUnitKerja?->unitKerja;

        if (!$unitKerja) {
            return 'default';
        }

        // Find the unit kerja folder (parent folder)
        $unitKerjaFolder = Folder::where('collection', Str::slug($unitKerja->unit_name))->first();

        if (!$unitKerjaFolder) {
            return 'default';
        }

        $subLaporan = $unitKerjaFolder->collection . '-laporan-imut';
        // Look for "Laporan IMUT" subfolder inside unit kerja folder
        $laporanImutFolder = Folder::where('parent_id', $unitKerjaFolder->id)
            ->where('collection', $subLaporan)
            ->first();

        // If not found, create it
        if (!$laporanImutFolder) {
            $laporanImutFolder = Folder::create([
                'parent_id' => $unitKerjaFolder->id,
                'name' => 'Laporan IMUT',
                'collection' => $subLaporan,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
            ]);
        }

        // Get laporan period from LaporanImut model, not ImutPenilaian
        $laporan = $penilaian->laporanUnitKerja?->laporanImut;

        if (!$laporan) {
            return $laporanImutFolder->collection;
        }

        // Format: Maret 2026
        $bulanNama = Carbon::createFromDate(
            $laporan->report_year,
            $laporan->report_month,
            1
        )->locale('id')->translatedFormat('F Y');

        $penilaianPeriode = strtolower(str_replace(' ', '-', $bulanNama));

        $folderLaporanImutPeriode = Folder::where('parent_id', $laporanImutFolder->id)
            ->where('collection', $subLaporan . '-' . $penilaianPeriode)
            ->first();

        // If not found, create it
        if (!$folderLaporanImutPeriode) {
            $folderLaporanImutPeriode = Folder::create([
                'parent_id' => $laporanImutFolder->id,
                'name' => $bulanNama,
                'collection' => $subLaporan . '-' . $penilaianPeriode,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
            ]);
        }

        return $folderLaporanImutPeriode->collection;
    }
}
