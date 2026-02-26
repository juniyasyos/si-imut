<?php

namespace App\Filament\Traits\ReportDetailAction;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Services\Form\FormCalculationService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
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
                    $formTemplateId = optional($record->profile)->formTemplates()->first()?->id;
                    $imutProfileId = optional($record->profile)->id;
                    $unitKerjaId = optional($record->laporanUnitKerja)->unit_kerja_id;
                    $laporan = optional($record->laporanUnitKerja)->laporanImut;
                    if ($laporan) {
                        $period = sprintf('%04d-%02d', $laporan->report_year, $laporan->report_month);
                    }
                }

                // dd([
                //     'formTemplateId' => $formTemplateId,
                //     'imutProfileId' => $imutProfileId,
                //     'unitKerjaId' => $unitKerjaId,
                //     'period' => $period,
                //     'laporan' => $laporan,
                // ]);
                return [
                    Section::make('Perhitungan')
                        ->schema($this->buildPerhitunganSchemaForAction($livewireComponent))
                        ->columns(3),

                    Section::make('Unggah Bukti Pendukung')
                        ->visible(function ($record) {
                            return ! $record->profile->imutData->is_monthly;
                        })
                        ->disabled(true)
                        ->schema($this->getMediaUploadFieldForAction($livewireComponent)),

                    Section::make('Data Pendukung')
                        ->visible(function (callable $get, $record) {
                            return $record->profile->imutData->is_monthly;
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

    protected function buildAnalysisSchemaForAction($livewireComponent): array
    {
        $shouldLock = $livewireComponent->createAnalisistAndRecomendation();

        return [
            Textarea::make('analysis')
                ->label('Analisis')
                ->rows(4)
                ->required()
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
                ->columnSpanFull(),

            Textarea::make('recommendations')
                ->label('Rekomendasi')
                ->required()
                ->minLength(20)
                ->maxLength(100000)
                ->readOnly($shouldLock)
                ->rows(4)
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
                ->columnSpanFull(),
        ];
    }

    protected function getMediaUploadFieldForAction($livewireComponent): array
    {
        $shouldLock = $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian');

        return [
            SpatieMediaLibraryFileUpload::make('document_upload')
                ->label('Unggah Dokumen Pendukung')
                ->collection(fn(callable $get) => $get('selected_collection'))
                ->directory(fn(callable $get) => 'uploads/imut-documents/' . ($get('selected_collection')))
                ->openable()
                ->downloadable()
                ->maxSize(20480)
                ->preserveFilenames()
                ->previewable(true)
                ->columnSpanFull()
                ->disabled($shouldLock)
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/*',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->helperText('File yang didukung: PDF, Word, Excel, Gambar. Maks. 20MB')
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
            $unitKerja = $penilaian->laporanUnitKerja?->unitKerja;
            $folder = Folder::where('collection', Str::slug($unitKerja->unit_name))->first();
            $fill['selected_collection'] = $folder?->collection ?? 'default';
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

        $unitKerja = $penilaian->laporanUnitKerja?->unitKerja;
        $folder = Folder::where('collection', Str::slug($unitKerja->unit_name))->first();

        $update = [
            'numerator_value'   => $data['numerator_value'] ?? null,
            'denominator_value' => $data['denominator_value'] ?? null,
            'analysis'          => $data['analysis'] ?? null,
            'recommendations'   => $data['recommendations'] ?? null,
            'selected_collection' => $folder?->collection ?? 'default',
        ];

        $penilaian->update($update);
    }

    /**
     * Build table view URL for a record
     */
    protected function buildTableViewUrl($record): string
    {
        $formTemplateId = optional($record->profile)->formTemplates()->first()?->id;
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
}
