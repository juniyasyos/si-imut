<?php

namespace App\Console\Commands;

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use App\Services\Reporting\DailyReportAggregationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CheckImutCalculationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imut:check-calculation
                            {--month= : Bulan laporan (1-12 atau nama bulan, misal: 10 atau oktober)}
                            {--year= : Tahun laporan (misal: 2025, 2026)}
                            {--filter= : Filter judul/kode IMUT berdasarkan kata kunci (misal: IDO)}
                            {--laporan-id= : ID Laporan IMUT spesifik (opsional)}
                            {--unit= : Filter nama/ID unit kerja (opsional)}
                            {--sync-orphans : Bersihkan penilaian yang relasi unit kerjanya sudah dicabut di master}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemeriksaan & audit kalkulasi IMUT serta sinkronisasi penilaian orphaned';

    public function handle(
        DailyReportAggregationService $service,
        DailyReportResponseRepositoryInterface $dailyReportRepo
    ): int {
        $this->info('🔍 SIIMUT - Tool Audit & Diagnosa Kalkulasi Indikator Mutu');
        $this->newLine();

        $laporan = $this->resolveLaporan();
        if (!$laporan) {
            return Command::FAILURE;
        }

        $keyword = $this->option('filter');
        $unitFilter = $this->option('unit');
        $syncOrphans = (bool) $this->option('sync-orphans');

        $this->inspectLaporan(
            $laporan,
            $service,
            $dailyReportRepo,
            $keyword,
            $unitFilter,
            $syncOrphans
        );

        return Command::SUCCESS;
    }

    /**
     * Resolve LaporanImut berdasarkan parameter
     */
    private function resolveLaporan(): ?LaporanImut
    {
        if ($laporanId = $this->option('laporan-id')) {
            $laporan = LaporanImut::find($laporanId);
            if (!$laporan) {
                $this->error("❌ Laporan dengan ID {$laporanId} tidak ditemukan!");
                return null;
            }
            return $laporan;
        }

        $monthInput = $this->option('month');
        $yearInput = $this->option('year');

        if ($monthInput !== null || $yearInput !== null) {
            $query = LaporanImut::query();

            if ($monthInput !== null) {
                $month = $this->parseMonth($monthInput);
                if (!$month) {
                    $this->error("❌ Format bulan tidak valid: '{$monthInput}'. Gunakan angka 1-12 atau nama bulan (misal: 'oktober').");
                    return null;
                }
                $query->where('report_month', $month);
            }

            if ($yearInput !== null) {
                $query->where('report_year', (int) $yearInput);
            }

            $reports = $query->orderByDesc('report_year')
                ->orderByDesc('report_month')
                ->get();

            if ($reports->isEmpty()) {
                $desc = [];
                if ($monthInput) $desc[] = "Bulan: {$monthInput}";
                if ($yearInput) $desc[] = "Tahun: {$yearInput}";
                $this->error("❌ Tidak ditemukan Laporan IMUT dengan kriteria: " . implode(', ', $desc));
                return null;
            }

            if ($reports->count() > 1 && $yearInput === null) {
                $this->warn("⚠️ Ditemukan lebih dari 1 laporan untuk bulan ini. Menggunakan laporan tahun terbaru: {$reports->first()->name}");
            }

            return $reports->first();
        }

        $latest = LaporanImut::orderByDesc('report_year')
            ->orderByDesc('report_month')
            ->first();

        if ($latest) {
            $this->warn("ℹ️ Menggunakan laporan terbaru: {$latest->name} (ID: {$latest->id})");
            return $latest;
        }

        $this->error("❌ Tidak ada Laporan IMUT yang tersedia di database.");
        return null;
    }

    /**
     * Parse input bulan ke integer 1-12
     */
    private function parseMonth(string|int $input): ?int
    {
        if (is_numeric($input)) {
            $m = (int) $input;
            return ($m >= 1 && $m <= 12) ? $m : null;
        }

        $clean = strtolower(trim((string) $input));
        $map = [
            'januari' => 1, 'january' => 1, 'jan' => 1,
            'februari' => 2, 'february' => 2, 'feb' => 2,
            'maret' => 3, 'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'june' => 6, 'jun' => 6,
            'juli' => 7, 'july' => 7, 'jul' => 7,
            'agustus' => 8, 'august' => 8, 'ags' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'october' => 10, 'okt' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'december' => 12, 'des' => 12, 'dec' => 12,
        ];

        return $map[$clean] ?? null;
    }

    /**
     * Jalankan inspeksi
     */
    private function inspectLaporan(
        LaporanImut $laporan,
        DailyReportAggregationService $service,
        DailyReportResponseRepositoryInterface $dailyReportRepo,
        ?string $keyword,
        ?string $unitFilter,
        bool $syncOrphans
    ): void {
        $startDate = Carbon::parse($laporan->assessment_period_start);
        $endDate = Carbon::parse($laporan->assessment_period_end);

        $this->info("📋 Laporan: {$laporan->name} (ID: {$laporan->id})");
        $this->line("   📅 Periode Asesmen : {$startDate->format('d M Y')} s/d {$endDate->format('d M Y')} (" . ($startDate->diffInDays($endDate) + 1) . " hari)");
        $this->line("   🗓️ Periode Laporan : Bulan {$laporan->report_month} Tahun {$laporan->report_year}");
        $this->line("   ⚙️ Aksi Sinkron    : " . ($syncOrphans ? '<fg=yellow;options=bold>AKTIF (--sync-orphans: membersihkan penilaian orphaned)</>' : '<fg=cyan>NON-AKTIF (Hanya audit & deteksi)</>'));
        $this->newLine();

        $laporan->loadMissing([
            'laporanUnitKerjas.unitKerja',
            'laporanUnitKerjas.imutPenilaians.profile.formTemplates',
            'laporanUnitKerjas.imutPenilaians.profile.activeFormTemplate',
            'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
        ]);

        $allPenilaians = $laporan->laporanUnitKerjas->flatMap(function ($luk) {
            return $luk->imutPenilaians->map(function ($p) use ($luk) {
                $p->setRelation('laporanUnitKerja', $luk);
                return $p;
            });
        });

        if ($keyword !== null && trim($keyword) !== '') {
            $keyword = trim($keyword);
            $matchedPenilaians = $allPenilaians->filter(function ($p) use ($keyword) {
                $title = $p->profile?->imutData?->title ?? '';
                $slug = $p->profile?->imutData?->slug ?? '';
                $id = (string) ($p->profile?->imutData?->id ?? '');

                return stripos($title, $keyword) !== false ||
                    stripos($slug, $keyword) !== false ||
                    $id === $keyword;
            });

            if ($matchedPenilaians->isEmpty()) {
                $this->warn("⚠️ Tidak ada indikator yang cocok dengan '{$keyword}' di {$laporan->name}.");
                $this->diagnoseMissingKeyword($keyword, $startDate, $endDate);
                return;
            }

            $penilaiansToProcess = $matchedPenilaians;
        } else {
            $penilaiansToProcess = $allPenilaians;
        }

        if ($unitFilter !== null && trim($unitFilter) !== '') {
            $unitFilter = trim($unitFilter);
            $penilaiansToProcess = $penilaiansToProcess->filter(function ($p) use ($unitFilter) {
                $unitName = $p->laporanUnitKerja?->unitKerja?->unit_name ?? '';
                $unitId = (string) ($p->laporanUnitKerja?->unitKerja?->id ?? '');

                return stripos($unitName, $unitFilter) !== false || $unitId === $unitFilter;
            });

            if ($penilaiansToProcess->isEmpty()) {
                $this->warn("⚠️ Tidak ada penilaian yang cocok dengan filter unit: '{$unitFilter}'.");
                return;
            }
        }

        $groupedByImut = $penilaiansToProcess->groupBy(function ($p) {
            return $p->profile?->imut_data_id ?? 0;
        });

        $this->info("🎯 Ditemukan " . $groupedByImut->count() . " Indikator Mutu untuk dianalisa:");
        $this->newLine();

        $totalOrphansDetected = 0;

        foreach ($groupedByImut as $imutDataId => $penilaians) {
            $orphansInGroup = $this->processImutGroup(
                $imutDataId,
                $penilaians,
                $service,
                $syncOrphans
            );
            $totalOrphansDetected += $orphansInGroup;
        }

        if ($totalOrphansDetected > 0 && !$syncOrphans) {
            $this->newLine();
            $this->warn("⚠️ Terdeteksi {$totalOrphansDetected} penilaian yang relasi unit kerjanya sudah DICABUT dari master data.");
            $this->line("   Nilai penilaian dari unit-unit ini saat ini masih ikut terhitung ke agregat Rumah Sakit.");
            $this->line("   👉 Untuk menghapus data penilaian yang sudah dicabut ini dari laporan, jalankan:");
            $this->comment("      php artisan imut:check-calculation --month={$laporan->report_month} --year={$laporan->report_year} " . ($keyword ? "--filter=\"{$keyword}\" " : "") . "--sync-orphans");
        }
    }

    private function processImutGroup(
        int $imutDataId,
        Collection $penilaians,
        DailyReportAggregationService $service,
        bool $syncOrphans
    ): int {
        $first = $penilaians->first();
        $imutData = $first->profile?->imutData;
        $title = $imutData?->title ?? "IMUT Data ID #{$imutDataId}";
        $category = $imutData?->categories?->category_name ?? '-';
        $standard = $first->profile ? ($first->profile->target_operator . ' ' . $first->profile->target_value . '%') : '-';

        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📌 [ID: {$imutDataId}] {$title}");
        $this->line("   Kategori : {$category} | Standar: {$standard} | Total Unit Terdaftar: " . $penilaians->count());
        $this->newLine();

        $tableRows = [];
        $totalDbNum = 0.0;
        $totalDbDenum = 0.0;
        $totalCalcNum = 0.0;
        $totalCalcDenum = 0.0;

        $orphanedPenilaians = collect();

        foreach ($penilaians as $penilaian) {
            $unitKerjaId = $penilaian->laporanUnitKerja?->unit_kerja_id;
            $unitName = $penilaian->laporanUnitKerja?->unitKerja?->unit_name ?? "Unit #{$unitKerjaId}";
            $formTemplate = $penilaian->profile?->activeFormTemplate;

            // Cek apakah relasi unit kerja dengan IMUT Data ini masih aktif di master
            $isMasterLinked = DB::table('imut_data_unit_kerja')
                ->where('unit_kerja_id', $unitKerjaId)
                ->where('imut_data_id', $imutDataId)
                ->exists();

            if (!$isMasterLinked) {
                $orphanedPenilaians->push($penilaian);
            }

            $dbNum = $penilaian->numerator_value;
            $dbDenum = $penilaian->denominator_value;
            $dbPct = ($dbDenum !== null && $dbDenum > 0 && $dbNum !== null)
                ? ceil(($dbNum / $dbDenum) * 100 * 100) / 100
                : null;

            if ($dbNum !== null && $dbDenum !== null) {
                $totalDbNum += (float) $dbNum;
                $totalDbDenum += (float) $dbDenum;
            }

            $calcResult = $service->calculateForPenilaian($penilaian);
            $calcNum = (float) $calcResult['numerator'];
            $calcDenum = (float) $calcResult['denominator'];
            $calcPct = (float) $calcResult['percentage'];

            $totalCalcNum += $calcNum;
            $totalCalcDenum += $calcDenum;

            $status = '';
            $notes = [];

            if (!$isMasterLinked) {
                $status = '<fg=yellow;options=bold>RELASI_DICABUT</>';
                $notes[] = '<fg=yellow>Relasi master sudah dicabut (Orphaned)</>';
            } elseif ($dbNum > 0 && ($dbDenum === null || $dbDenum == 0)) {
                $status = '<fg=red;options=bold>ANOMALI</>';
                $notes[] = '<fg=red>Num > 0 tapi Denum = 0!</>';
            } elseif (!$formTemplate) {
                $status = '<fg=red>NO_TEMPLATE</>';
                $notes[] = 'FormTemplate tidak aktif';
            } elseif ($calcDenum == 0 && $dbDenum !== null) {
                $status = '<fg=yellow>NO_DAILY_DATA</>';
                $notes[] = "Daily = 0, DB = {$dbNum}/{$dbDenum}";
            } elseif ($dbNum === null && $dbDenum === null) {
                $status = '<fg=cyan>EMPTY_IN_DB</>';
                $notes[] = 'DB belum terisi';
            } elseif (abs((float)$dbNum - $calcNum) < 0.001 && abs((float)$dbDenum - $calcDenum) < 0.001) {
                $status = '<fg=green>MATCH</>';
            } else {
                $status = '<fg=red;options=bold>MISMATCH</>';
                $notes[] = "Selisih: N " . ($calcNum - (float)$dbNum) . ", D " . ($calcDenum - (float)$dbDenum);
            }

            $missingDaysCount = count($calcResult['calculation_metadata']['missing_dates'] ?? []);
            if ($missingDaysCount > 0 && $formTemplate) {
                $notes[] = "Kosong {$missingDaysCount} hari";
            }

            $tableRows[] = [
                \Illuminate\Support\Str::limit($unitName, 25),
                $dbNum !== null ? number_format((float)$dbNum, 2) : '-',
                $dbDenum !== null ? number_format((float)$dbDenum, 2) : '-',
                $dbPct !== null ? number_format($dbPct, 2) . '%' : '-',
                number_format($calcNum, 2),
                number_format($calcDenum, 2),
                number_format($calcPct, 2) . '%',
                $status,
                implode('; ', $notes),
            ];
        }

        $this->table(
            [
                'Unit Kerja',
                'DB Num',
                'DB Denum',
                'DB %',
                'Daily Num',
                'Daily Denum',
                'Daily %',
                'Status',
                'Keterangan & Catatan Diagnosa',
            ],
            $tableRows
        );

        $overallDbPct = $totalDbDenum > 0 ? ceil(($totalDbNum / $totalDbDenum) * 100 * 100) / 100 : 0.0;
        $overallCalcPct = $totalCalcDenum > 0 ? ceil(($totalCalcNum / $totalCalcDenum) * 100 * 100) / 100 : 0.0;

        $aggStatus = (abs($totalDbNum - $totalCalcNum) < 0.01 && abs($totalDbDenum - $totalCalcDenum) < 0.01)
            ? '<fg=green;options=bold>SESUAI (MATCH)</>'
            : '<fg=red;options=bold>ADA SELISIH (MISMATCH)</>';

        $this->newLine();
        $this->line("   📊 <options=bold>RINGKASAN AGREGASI KESELURUHAN UNIT SAAT INI:</>");
        $this->table(
            ['Metrik', 'Nilai Tersimpan di DB', 'Hasil Kalkulasi Daily Report', 'Selisih (Daily - DB)'],
            [
                ['Total Numerator (N)', number_format($totalDbNum, 2), number_format($totalCalcNum, 2), number_format($totalCalcNum - $totalDbNum, 2)],
                ['Total Denominator (D)', number_format($totalDbDenum, 2), number_format($totalCalcDenum, 2), number_format($totalCalcDenum - $totalDbDenum, 2)],
                ['Capaian Agregat (%)', number_format($overallDbPct, 2) . '%', number_format($overallCalcPct, 2) . '%', number_format($overallCalcPct - $overallDbPct, 2) . '%'],
                ['Status Agregat', $aggStatus, $aggStatus, '-'],
            ]
        );

        // Eksekusi pembersihan jika flag --sync-orphans aktif
        if ($syncOrphans && $orphanedPenilaians->isNotEmpty()) {
            $this->newLine();
            $this->warn("🧹 Melakukan pembersihan data penilaian orphaned...");

            $cleanNumTotal = 0;
            $cleanDenumTotal = 0;

            foreach ($orphanedPenilaians as $orphaned) {
                $uName = $orphaned->laporanUnitKerja?->unitKerja?->unit_name ?? "Unit #{$orphaned->laporanUnitKerja?->unit_kerja_id}";
                $nVal = (float) ($orphaned->numerator_value ?? 0);
                $dVal = (float) ($orphaned->denominator_value ?? 0);

                $cleanNumTotal += $nVal;
                $cleanDenumTotal += $dVal;

                $this->line("   🗑️ Menghapus penilaian [ID: {$orphaned->id}] milik unit '{$uName}' (N: {$nVal}, D: {$dVal})");

                // Hapus via Eloquent agar trigger Observer pembersih cache
                $orphaned->delete();
            }

            $newDbNum = $totalDbNum - $cleanNumTotal;
            $newDbDenum = $totalDbDenum - $cleanDenumTotal;
            $newDbPct = $newDbDenum > 0 ? ceil(($newDbNum / $newDbDenum) * 100 * 100) / 100 : 0.0;

            $this->newLine();
            $this->info("✅ Berhasil membersihkan " . $orphanedPenilaians->count() . " penilaian orphaned dan mereset cache!");
            $this->line("   📊 <options=bold>REKAPITULASI SETELAH PEMBERSIHAN UNIT DICABUT:</>");
            $this->table(
                ['Metrik', 'Sebelum Cleanup', 'Setelah Cleanup', 'Koreksi'],
                [
                    ['Total Numerator (N)', number_format($totalDbNum, 2), number_format($newDbNum, 2), '-' . number_format($cleanNumTotal, 2)],
                    ['Total Denominator (D)', number_format($totalDbDenum, 2), number_format($newDbDenum, 2), '-' . number_format($cleanDenumTotal, 2)],
                    ['Capaian Agregat (%)', number_format($overallDbPct, 2) . '%', number_format($newDbPct, 2) . '%', number_format($newDbPct - $overallDbPct, 2) . '%'],
                ]
            );
        }

        $this->newLine();

        return $orphanedPenilaians->count();
    }

    private function diagnoseMissingKeyword(string $keyword, Carbon $startDate, Carbon $endDate): void
    {
        $this->newLine();
        $this->line("🔍 <options=bold>Diagnosa Master IMUT untuk '{$keyword}':</>");

        $foundImuts = ImutData::where('title', 'LIKE', "%{$keyword}%")
            ->orWhere('slug', 'LIKE', "%{$keyword}%")
            ->with(['profiles'])
            ->get();

        if ($foundImuts->isEmpty()) {
            $this->error("   ❌ Kata kunci '{$keyword}' tidak ditemukan di Master IMUT.");
            return;
        }

        foreach ($foundImuts as $im) {
            $this->line("   - [ID: {$im->id}] {$im->title} (Status: " . ($im->status ? 'Aktif' : 'Non-Aktif') . ")");
            if ($im->profiles->isEmpty()) {
                $this->warn("     ⚠️ Belum ada profil aktif.");
            } else {
                foreach ($im->profiles as $prof) {
                    $vFrom = $prof->valid_from ? Carbon::parse($prof->valid_from)->format('d-m-Y') : 'tanpa batas';
                    $vUntil = $prof->valid_until ? Carbon::parse($prof->valid_until)->format('d-m-Y') : 'tanpa batas';
                    $this->line("     • Profil ID {$prof->id} ({$prof->version}): Berlaku {$vFrom} s/d {$vUntil}");

                    $pFrom = $prof->valid_from ? Carbon::parse($prof->valid_from) : null;
                    $pUntil = $prof->valid_until ? Carbon::parse($prof->valid_until) : null;

                    $isValid = true;
                    if ($pFrom && $pFrom->gt($endDate)) $isValid = false;
                    if ($pUntil && $pUntil->lt($startDate)) $isValid = false;

                    if (!$isValid) {
                        $this->warn("       ⚠️ Profil TIDAK AKTIF di rentang periode laporan ini ({$startDate->format('d-m-Y')} s/d {$endDate->format('d-m-Y')}).");
                    }
                }
            }
        }
    }
}
