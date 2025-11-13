<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Widgets\LineChart;
use App\Models\ImutData;
use App\Models\LaporanImut;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SummaryDiagram extends Page
{
    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.summary-imut-data-diagram';

    public array $data = [];

    public ?ImutData $imutData = null;

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();

        return Gate::any([
            'view_all_data_imut::data'
        ], $user);
    }

    public function mount(): void
    {
        $slug = request()->query('record');

        if (! $slug) {
            abort(404, 'Slug Data IMUT tidak ditemukan.');
        }

        $imutData = ImutData::with(['profiles', 'categories'])->where('slug', $slug)->first();

        if (! $imutData) {
            abort(404, 'Data IMUT tidak valid.');
        }

        $this->imutData = $imutData;

        $this->data = [
            'imutDataId' => $imutData->id,
            'title' => $imutData->title,
            'status' => $imutData->status,
            'kategori' => $imutData->categories?->name ?? '-',
            'jumlah_profil' => $imutData->profiles->count(),
        ];
    }

    public function getTitle(): string
    {
        return 'Ikhtisar Data IMUT';
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl('index') => 'Daftar Data IMUT',
            ImutDataResource::getUrl('edit', ['record' => $this->imutData?->slug]) => $this->imutData?->title ?? 'Detail',
            'Ikhtisar',
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            LineChart::make(['imutData' => $this->imutData]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printReport')
                ->label('Print Laporan')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(function () {
                    // Ambil laporan terbaru yang sudah complete
                    $latestLaporan = LaporanImut::where('status', 'complete')
                        ->latest('assessment_period_end')
                        ->first();

                    if (!$latestLaporan) {
                        // Fallback ke laporan terbaru apapun statusnya
                        $latestLaporan = LaporanImut::latest('assessment_period_end')->first();
                    }

                    if (!$latestLaporan) {
                        return null;
                    }

                    return route('print.preview.imut-indicator-report', [
                        'imut_data_id' => $this->imutData->id,
                        'laporan_id' => $latestLaporan->id,
                        'period_filter' => 'year',
                    ]);
                })
                ->openUrlInNewTab()
                ->visible(fn() => $this->imutData !== null),

            Action::make('exportPDF')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(function () {
                    // Ambil laporan terbaru yang sudah complete
                    $latestLaporan = LaporanImut::where('status', 'complete')
                        ->latest('assessment_period_end')
                        ->first();

                    if (!$latestLaporan) {
                        // Fallback ke laporan terbaru apapun statusnya
                        $latestLaporan = LaporanImut::latest('assessment_period_end')->first();
                    }

                    if (!$latestLaporan) {
                        return null;
                    }

                    return route('print.preview.imut-indicator-report', [
                        'imut_data_id' => $this->imutData->id,
                        'laporan_id' => $latestLaporan->id,
                        'period_filter' => 'year',
                        'auto_print' => '1', // Parameter untuk auto trigger print dialog
                    ]);
                })
                ->openUrlInNewTab()
                ->visible(fn() => $this->imutData !== null),
        ];
    }


}
