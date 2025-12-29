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
            ->form(function () use ($livewireComponent) {
                return [
                    Section::make('Perhitungan')
                        ->schema($this->buildPerhitunganSchemaForAction($livewireComponent))
                        ->columns(3),

                    Section::make('Unggah Bukti Pendukung')
                        ->schema($this->getMediaUploadFieldForAction($livewireComponent)),

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

    protected function buildLihatDetailAction(): Action
    {
        return Action::make('lihat')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->url(fn($record) => $this->getPenilaianUrl($record));
    }

    protected function getPenilaianUrl($record): string
    {
        $laporanSlug = LaporanImut::findOrFail($record->laporan_imut_id)->slug;

        return \App\Filament\Resources\LaporanImutResource::getUrl('edit-penilaian', [
            'laporanSlug' => $laporanSlug,
            'record' => $record->id,
        ]);
    }

    protected function buildPerhitunganSchemaForAction($livewireComponent): array
    {
        $shouldLock = $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian');

        return [
            TextInput::make('numerator_value')
                ->label('Numerator')
                ->numeric()
                ->placeholder('0.00')
                ->nullable()
                ->debounce(1000)
                ->readOnly($shouldLock)
                ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

            TextInput::make('denominator_value')
                ->label('Denominator')
                ->numeric()
                ->placeholder('0.00')
                ->nullable()
                ->debounce(1000)
                ->readOnly($shouldLock)
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

    protected function buildAnalysisSchemaForAction($livewireComponent): array
    {
        $shouldLock = $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian');
        $canRecommend = Gate::allows('create_recommendation_penilaian_imut::penilaian') || Gate::allows('force_editable_imut::penilaian');

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
                ->disabled(!$canRecommend)
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
}
