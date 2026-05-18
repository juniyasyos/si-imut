<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\LaporanRepositoryInterface;

class PrintReportController extends Controller
{
    private LaporanRepositoryInterface $laporanRepository;

    public function __construct(LaporanRepositoryInterface $laporanRepository)
    {
        $this->laporanRepository = $laporanRepository;
    }

    /**
     * Preview print laporan IMUT dengan dummy data
     *
     * @return \Illuminate\View\View
     */
    public function previewImutDataReport()
    {
        // Dummy data untuk laporan
        $laporan = (object) [
            'id' => 1,
            'name' => 'Laporan IMUT Bulan Oktober 2024',
            'slug' => 'laporan-imut-bulan-oktober-2024',
            'status' => 'complete', // complete, process, coming_soon
            'assessment_period_start' => '2024-10-01',
            'assessment_period_end' => '2024-10-31',
            'report_month' => 10,
            'report_year' => 2024,
            'created_by' => 1,
            'createdBy' => (object) [
                'name' => 'Dr. Ahmad Suryana, Sp.PK',
            ],
        ];

        // Dummy data summary
        $summary = [
            'total_imut_data' => 25,
            'total_unit_kerja' => 8,
            'average_percentage' => 87.45,
            'filled_count' => 192,
            'total_count' => 200,
        ];

        // Dummy data by category
        $dataByCategory = collect([
            'Pelayanan Klinis' => collect([
                (object) [
                    'id' => 1,
                    'imut_data_title' => 'Identifikasi Pasien dengan Benar',
                    'imut_categories' => 'Pelayanan Klinis',
                    'total_numerator' => 485,
                    'total_denominator' => 500,
                    'percentage' => 97.00,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 2,
                    'imut_data_title' => 'Komunikasi Efektif',
                    'imut_categories' => 'Pelayanan Klinis',
                    'total_numerator' => 456,
                    'total_denominator' => 500,
                    'percentage' => 91.20,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 3,
                    'imut_data_title' => 'Pemberian Obat yang Benar',
                    'imut_categories' => 'Pelayanan Klinis',
                    'total_numerator' => 473,
                    'total_denominator' => 500,
                    'percentage' => 94.60,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 4,
                    'imut_data_title' => 'Ketepatan Waktu Pemberian Antibiotik',
                    'imut_categories' => 'Pelayanan Klinis',
                    'total_numerator' => 89,
                    'total_denominator' => 100,
                    'percentage' => 89.00,
                    'imut_standard' => 80,
                ],
                (object) [
                    'id' => 5,
                    'imut_data_title' => 'Risiko Pasien Jatuh',
                    'imut_categories' => 'Pelayanan Klinis',
                    'total_numerator' => 3,
                    'total_denominator' => 450,
                    'percentage' => 0.67,
                    'imut_standard' => 3,
                ],
            ]),
            'Manajemen dan Kepemimpinan' => collect([
                (object) [
                    'id' => 6,
                    'imut_data_title' => 'Kelengkapan Pengisian Resume Medis',
                    'imut_categories' => 'Manajemen dan Kepemimpinan',
                    'total_numerator' => 427,
                    'total_denominator' => 450,
                    'percentage' => 94.89,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 7,
                    'imut_data_title' => 'Waktu Tunggu Rawat Jalan',
                    'imut_categories' => 'Manajemen dan Kepemimpinan',
                    'total_numerator' => 387,
                    'total_denominator' => 450,
                    'percentage' => 86.00,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 8,
                    'imut_data_title' => 'Kepuasan Pasien',
                    'imut_categories' => 'Manajemen dan Kepemimpinan',
                    'total_numerator' => 412,
                    'total_denominator' => 450,
                    'percentage' => 91.56,
                    'imut_standard' => 80,
                ],
            ]),
            'Sasaran Keselamatan Pasien' => collect([
                (object) [
                    'id' => 9,
                    'imut_data_title' => 'Hand Hygiene',
                    'imut_categories' => 'Sasaran Keselamatan Pasien',
                    'total_numerator' => 892,
                    'total_denominator' => 1000,
                    'percentage' => 89.20,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 10,
                    'imut_data_title' => 'Kepatuhan Penggunaan APD',
                    'imut_categories' => 'Sasaran Keselamatan Pasien',
                    'total_numerator' => 945,
                    'total_denominator' => 1000,
                    'percentage' => 94.50,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 11,
                    'imut_data_title' => 'Pencegahan Infeksi Luka Operasi',
                    'imut_categories' => 'Sasaran Keselamatan Pasien',
                    'total_numerator' => 2,
                    'total_denominator' => 150,
                    'percentage' => 1.33,
                    'imut_standard' => 2,
                ],
            ]),
            'Pengendalian Infeksi' => collect([
                (object) [
                    'id' => 12,
                    'imut_data_title' => 'Infeksi Daerah Operasi (IDO)',
                    'imut_categories' => 'Pengendalian Infeksi',
                    'total_numerator' => 2,
                    'total_denominator' => 120,
                    'percentage' => 1.67,
                    'imut_standard' => 2,
                ],
                (object) [
                    'id' => 13,
                    'imut_data_title' => 'Infeksi Aliran Darah Primer (IADP)',
                    'imut_categories' => 'Pengendalian Infeksi',
                    'total_numerator' => 1,
                    'total_denominator' => 80,
                    'percentage' => 1.25,
                    'imut_standard' => 2,
                ],
                (object) [
                    'id' => 14,
                    'imut_data_title' => 'Infeksi Saluran Kemih (ISK)',
                    'imut_categories' => 'Pengendalian Infeksi',
                    'total_numerator' => 3,
                    'total_denominator' => 100,
                    'percentage' => 3.00,
                    'imut_standard' => 5,
                ],
            ]),
            'Laboratorium' => collect([
                (object) [
                    'id' => 15,
                    'imut_data_title' => 'Waktu Tunggu Hasil Lab',
                    'imut_categories' => 'Laboratorium',
                    'total_numerator' => 423,
                    'total_denominator' => 450,
                    'percentage' => 94.00,
                    'imut_standard' => 100,
                ],
                (object) [
                    'id' => 16,
                    'imut_data_title' => 'Kesalahan Pra-Analitik Lab',
                    'imut_categories' => 'Laboratorium',
                    'total_numerator' => 2,
                    'total_denominator' => 450,
                    'percentage' => 0.44,
                    'imut_standard' => 1,
                ],
            ]),
        ]);

        return view('filament.prints.imut-data-report', compact('laporan', 'summary', 'dataByCategory'));
    }

    /**
     * Generate real print laporan IMUT
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function printImutDataReport(Request $request)
    {
        $laporanId = $request->get('laporan_id');

        // TODO: Implement real data fetching
        // $laporan = LaporanImut::with('createdBy')->findOrFail($laporanId);
        // $dataByCategory = $this->getDataByCategory($laporanId);
        // $summary = $this->getSummary($laporanId);

        // For now, redirect to preview
        return redirect()->route('print.preview.imut-data-report');
    }

    /**
     * Preview print laporan per indikator mutu dengan data asli
     *
     * @return \Illuminate\View\View
     */
    public function previewImutIndicatorReport(Request $request)
    {
        // Authorization check
        if (!Gate::allows('view_all_data_imut::data')) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Get parameters dari request
        $imutDataId = $request->get('imut_data_id');
        $laporanId = $request->get('laporan_id');
        $noteId = $request->get('note_id');
        $periodFilter = $request->get('period_filter', 'year'); // year, semester_1, semester_2, q1, q2, q3, q4

        // Cari imut data
        if ($imutDataId) {
            $imutData = \App\Models\ImutData::with(['categories', 'notes'])->find($imutDataId);
        } else {
            // Default: Kebersihan Tangan atau first available
            $imutData = \App\Models\ImutData::with(['categories', 'notes'])
                ->where('title', 'LIKE', '%Kebersihan Tangan%')
                ->orWhere('title', 'LIKE', '%Hand Hygiene%')
                ->first();

            if (!$imutData) {
                $imutData = \App\Models\ImutData::with(['categories', 'notes'])->first();
            }
        }

        if (!$imutData) {
            return view('filament.prints.imut-indicator-report')->with([
                'error' => 'Data IMUT tidak ditemukan'
            ]);
        }

        // Ambil laporan
        if ($laporanId) {
            $laporan = \App\Models\LaporanImut::with('createdBy')->find($laporanId);
        } else {
            $laporan = \App\Models\LaporanImut::with('createdBy')
                ->where('status', 'complete')
                ->latest('assessment_period_end')
                ->first();

            if (!$laporan) {
                $laporan = \App\Models\LaporanImut::with('createdBy')
                    ->latest('assessment_period_end')
                    ->first();
            }
        }

        if (!$laporan) {
            return view('filament.prints.imut-indicator-report')->with([
                'error' => 'Laporan tidak ditemukan'
            ]);
        }

        // Ambil note yang dipilih atau default (terakhir)
        if ($noteId) {
            $selectedNote = $imutData->notes()->find($noteId);
        } else {
            $selectedNote = $imutData->notes()->latest()->first();
        }

        // Gunakan query builder untuk mendapatkan data per unit kerja
        $unitKerjaData = $this->laporanRepository->getReportByImutDataDetails(
            $laporan->id,
            $imutData->id
        )->get();

        // Calculate summary
        $summary = [
            'total_unit_kerja' => $unitKerjaData->count(),
            'total_numerator' => $unitKerjaData->sum('numerator_value'),
            'total_denominator' => $unitKerjaData->sum('denominator_value'),
            'average_percentage' => $unitKerjaData->count() > 0
                ? $unitKerjaData->avg('percentage')
                : 0,
        ];

        // Ambil data historis untuk tahun ini dengan filter periode
        $historicalData = $this->getHistoricalDataForYear(
            $imutData->id,
            $laporan->report_year,
            $periodFilter
        );

        // Format imutData object
        $imutDataFormatted = (object) [
            'id' => $imutData->id,
            'title' => $imutData->title,
            'slug' => $imutData->slug,
            'categories' => $imutData->categories->short_name ?? $imutData->categories->name ?? 'Tidak ada kategori',
            'standard' => $imutData->latestProfile->imut_standard ?? 100,
            'definition' => $imutData->latestProfile->definition ?? '',
            'numerator_description' => $imutData->latestProfile->numerator ?? 'Jumlah kejadian yang memenuhi kriteria',
            'denominator_description' => $imutData->latestProfile->denominator ?? 'Jumlah total kejadian yang diobservasi',
        ];

        // Ambil semua notes untuk dropdown
        $availableNotes = $imutData->notes()->latest()->get();

        return view('filament.prints.imut-indicator-report', [
            'laporan' => $laporan,
            'imutData' => $imutDataFormatted,
            'unitKerjaData' => $unitKerjaData,
            'summary' => $summary,
            'historicalData' => $historicalData,
            'selectedNote' => $selectedNote,
            'availableNotes' => $availableNotes,
            'periodFilter' => $periodFilter,
        ]);
    }

    /**
     * Get historical data untuk tahun tertentu dengan filter periode
     */
    private function getHistoricalDataForYear(int $imutDataId, int $year, string $periodFilter = 'year'): array
    {
        $data = [];

        // Tentukan range bulan berdasarkan filter
        $monthRange = $this->getMonthRangeByFilter($periodFilter);

        // Ambil semua laporan di tahun ini sesuai filter
        $laporans = \App\Models\LaporanImut::where('report_year', $year)
            ->whereIn('report_month', $monthRange)
            ->orderBy('report_month')
            ->get();

        foreach ($laporans as $laporan) {
            // Ambil summary data untuk laporan ini
            $monthlyData = $this->laporanRepository->getReportByImutDataDetails(
                $laporan->id,
                $imutDataId
            )->get();

            $totalN = $monthlyData->sum('numerator_value');
            $totalD = $monthlyData->sum('denominator_value');
            $percentage = $totalD > 0 ? ($totalN / $totalD) * 100 : null;

            $data[] = [
                'month' => $this->getMonthName($laporan->report_month) . ' ' . $year,
                'month_short' => $this->getMonthName($laporan->report_month),
                'month_num' => $laporan->report_month,
                'year' => $year,
                'percentage' => $percentage !== null ? round($percentage, 2) : null,
                'numerator' => $totalN,
                'denominator' => $totalD,
                'laporan_id' => $laporan->id,
                'laporan_name' => $laporan->name ?? '-',
            ];
        }

        return $data;
    }

    /**
     * Get month range based on period filter
     */
    private function getMonthRangeByFilter(string $filter): array
    {
        return match($filter) {
            'q1' => [1, 2, 3],           // Triwulan I
            'q2' => [4, 5, 6],           // Triwulan II
            'q3' => [7, 8, 9],           // Triwulan III
            'q4' => [10, 11, 12],        // Triwulan IV
            'semester_1' => [1, 2, 3, 4, 5, 6],     // Semester 1
            'semester_2' => [7, 8, 9, 10, 11, 12],  // Semester 2
            'year' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], // Tahunan
            default => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        };
    }

    /**
     * Get historical data untuk grafik (3 bulan terakhir) - DEPRECATED, use getHistoricalDataForYear
     */
    private function getHistoricalData(int $imutDataId, int $currentYear, int $currentMonth): array
    {
        $data = [];
        $months = [];

        // Ambil data 3 bulan terakhir termasuk bulan sekarang
        for ($i = 2; $i >= 0; $i--) {
            $month = $currentMonth - $i;
            $year = $currentYear;

            // Handle year rollover
            if ($month <= 0) {
                $month += 12;
                $year -= 1;
            }

            $months[] = [
                'year' => $year,
                'month' => $month,
                'label' => $this->getMonthName($month) . ' ' . $year
            ];
        }

        // Query untuk setiap bulan
        foreach ($months as $monthData) {
            // Cari laporan untuk bulan tersebut
            $laporan = \App\Models\LaporanImut::where('report_year', $monthData['year'])
                ->where('report_month', $monthData['month'])
                ->first();

            if ($laporan) {
                // Ambil summary data untuk bulan ini
                $monthlyData = $this->laporanRepository->getReportByImutDataDetails(
                    $laporan->id,
                    $imutDataId
                )->get();

                $totalN = $monthlyData->sum('numerator_value');
                $totalD = $monthlyData->sum('denominator_value');
                $percentage = $totalD > 0 ? ($totalN / $totalD) * 100 : 0;

                $data[] = [
                    'month' => $monthData['label'],
                    'month_short' => $this->getMonthName($monthData['month']),
                    'year' => $monthData['year'],
                    'percentage' => round($percentage, 2),
                    'numerator' => $totalN,
                    'denominator' => $totalD,
                    'laporan_name' => $laporan->name ?? '-',
                ];
            } else {
                // Jika tidak ada data untuk bulan ini
                $data[] = [
                    'month' => $monthData['label'],
                    'month_short' => $this->getMonthName($monthData['month']),
                    'year' => $monthData['year'],
                    'percentage' => null,
                    'numerator' => 0,
                    'denominator' => 0,
                    'laporan_name' => '-',
                ];
            }
        }

        return $data;
    }

    /**
     * Get month name in Indonesian
     */
    private function getMonthName(int $month): string
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $monthNames[$month] ?? '';
    }

    /**
     * Generate real print laporan per indikator
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function printImutIndicatorReport(Request $request)
    {
        $laporanId = $request->get('laporan_id');
        $imutDataId = $request->get('imut_data_id');

        // TODO: Implement real data fetching
        // $laporan = LaporanImut::with('createdBy')->findOrFail($laporanId);
        // $imutData = ImutData::findOrFail($imutDataId);
        // $unitKerjaData = $this->getUnitKerjaDataByIndicator($laporanId, $imutDataId);
        // $summary = $this->getIndicatorSummary($laporanId, $imutDataId);

        // For now, redirect to preview
        return redirect()->route('print.preview.imut-indicator-report');
    }
}
