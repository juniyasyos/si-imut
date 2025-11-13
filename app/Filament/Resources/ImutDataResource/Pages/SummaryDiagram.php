<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Widgets\LineChart;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\QueryBuilders\LaporanUnitKerja;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Browsershot\Browsershot;

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

            // Action::make('exportPDF')
            //     ->label('Export PDF')
            //     ->icon('heroicon-o-document-arrow-down')
            //     ->color('success')
            //     ->requiresConfirmation()
            //     ->modalHeading('Export Laporan ke PDF')
            //     ->modalDescription('Laporan akan diexport dalam format PDF. Proses ini mungkin memerlukan waktu beberapa detik.')
            //     ->modalSubmitActionLabel('Export')
            //     ->action(function () {
            //         try {
            //             // Ambil laporan terbaru yang sudah complete
            //             $latestLaporan = LaporanImut::where('status', 'complete')
            //                 ->latest('assessment_period_end')
            //                 ->first();

            //             if (!$latestLaporan) {
            //                 // Fallback ke laporan terbaru apapun statusnya
            //                 $latestLaporan = LaporanImut::latest('assessment_period_end')->first();
            //             }

            //             if (!$latestLaporan) {
            //                 Notification::make()
            //                     ->title('Tidak ada laporan tersedia')
            //                     ->danger()
            //                     ->send();
            //                 return;
            //             }

            //             // Generate data untuk view
            //             $imutData = $this->imutData;
            //             $periodFilter = 'year';

            //             // Get historical data
            //             $historicalData = $this->getHistoricalDataForYear($imutData->id, $latestLaporan->report_year, $periodFilter);

            //             // Get unit kerja data
            //             $unitKerjaData = LaporanUnitKerja::getReportByImutDataDetails(
            //                 $latestLaporan->id,
            //                 $imutData->id
            //             )->get();

            //             // Calculate summary
            //             $totalNumerator = $unitKerjaData->sum('numerator_value');
            //             $totalDenominator = $unitKerjaData->sum('denominator_value');
            //             $averagePercentage = $totalDenominator > 0 ? ($totalNumerator / $totalDenominator) * 100 : 0;

            //             $summary = [
            //                 'total_unit_kerja' => $unitKerjaData->count(),
            //                 'total_numerator' => $totalNumerator,
            //                 'total_denominator' => $totalDenominator,
            //                 'average_percentage' => $averagePercentage,
            //             ];

            //             // Get available notes
            //             $availableNotes = \App\Models\ImutDataNote::where('imut_data_id', $imutData->id)
            //                 ->where('period_year', $latestLaporan->report_year)
            //                 ->orderBy('created_at', 'desc')
            //                 ->get();

            //             // Get selected note
            //             $selectedNote = $availableNotes->first();

            //             // Render HTML
            //             $html = view('filament.prints.imut-indicator-report', [
            //                 'imutData' => $imutData,
            //                 'laporan' => $latestLaporan,
            //                 'unitKerjaData' => $unitKerjaData,
            //                 'summary' => $summary,
            //                 'historicalData' => $historicalData,
            //                 'selectedNote' => $selectedNote,
            //                 'availableNotes' => $availableNotes,
            //                 'periodFilter' => $periodFilter,
            //             ])->render();

            //             // Generate filename
            //             $filename = 'Laporan_' . \Str::slug($imutData->title) . '_' . now()->format('Y-m-d_His') . '.pdf';
            //             $filePath = storage_path('app/public/exports/' . $filename);

            //             // Pastikan folder exists
            //             if (!file_exists(dirname($filePath))) {
            //                 mkdir(dirname($filePath), 0755, true);
            //             }

            //             // Generate PDF dengan Browsershot
            //             Browsershot::html($html)
            //                 ->setNodeBinary(config('browsershot.node_binary', '/usr/bin/node'))
            //                 ->setNpmBinary(config('browsershot.npm_binary', '/usr/bin/npm'))
            //                 ->showBackground()
            //                 ->waitUntilNetworkIdle()
            //                 ->emulateMedia('print')
            //                 ->format('A4')
            //                 ->margins(10, 10, 10, 10)
            //                 ->newHeadless()
            //                 ->timeout(120)
            //                 ->save($filePath);

            //             // Return download response
            //             Notification::make()
            //                 ->title('PDF berhasil diexport!')
            //                 ->success()
            //                 ->send();

            //             return response()->download($filePath)->deleteFileAfterSend(true);
            //         } catch (\Exception $e) {
            //             Notification::make()
            //                 ->title('Gagal export PDF')
            //                 ->body('Error: ' . $e->getMessage())
            //                 ->danger()
            //                 ->send();

            //             \Log::error('Export PDF Error: ' . $e->getMessage());
            //         }
            //     })
            //     ->visible(fn() => $this->imutData !== null),
        ];
    }

    /**
     * Get historical data untuk tahun tertentu dengan filter
     */
    private function getHistoricalDataForYear($imutDataId, $year, $periodFilter = 'year'): array
    {
        // Tentukan range bulan berdasarkan filter
        $monthRange = $this->getMonthRangeByFilter($periodFilter);

        // Ambil semua laporan di tahun ini
        $laporans = LaporanImut::whereYear('assessment_period_start', $year)
            ->whereIn('report_month', $monthRange)
            ->orderBy('report_month', 'asc')
            ->get();

        $historicalData = [];
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

        foreach ($laporans as $lap) {
            // Get data untuk laporan ini
            $unitKerjaData = LaporanUnitKerja::getReportByImutDataDetails(
                $lap->id,
                $imutDataId
            )->get();

            $numerator = $unitKerjaData->sum('numerator_value');
            $denominator = $unitKerjaData->sum('denominator_value');
            $percentage = $denominator > 0 ? ($numerator / $denominator) * 100 : null;

            $historicalData[] = [
                'month' => $monthNames[$lap->report_month] ?? 'Unknown',
                'month_short' => $monthNames[$lap->report_month] ?? 'Unknown',
                'year' => $lap->report_year,
                'numerator' => $numerator,
                'denominator' => $denominator,
                'percentage' => $percentage,
            ];
        }

        return $historicalData;
    }

    /**
     * Get month range berdasarkan filter
     */
    private function getMonthRangeByFilter($filter): array
    {
        return match ($filter) {
            'semester_1' => [1, 2, 3, 4, 5, 6],
            'semester_2' => [7, 8, 9, 10, 11, 12],
            'q1' => [1, 2, 3],
            'q2' => [4, 5, 6],
            'q3' => [7, 8, 9],
            'q4' => [10, 11, 12],
            default => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], // year
        };
    }
}
