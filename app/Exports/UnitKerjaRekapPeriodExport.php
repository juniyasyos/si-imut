<?php

namespace App\Exports;

use App\Services\Chart\ImutPeriodService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Export Excel rekapitulasi capaian IMUT per unit kerja dengan kolom dinamis per bulan
 * dan 3 tingkat header (Unit Kerja | Bulan -> Nama Bulan -> Jml Indikator, Tidak Sesuai Standart, Persentase).
 */
class UnitKerjaRekapPeriodExport implements FromArray, WithTitle, WithEvents
{
    private const HEADER_ROW_TOP = 5;

    private const HEADER_ROW_MONTH = 6;

    private const HEADER_ROW_METRIC = 7;

    private const DATA_START_ROW = 8;

    private const FILTER_LABELS = [
        'all' => 'Semua Status Capaian',
        'high' => 'Capaian Tinggi (>= 80%)',
        'medium' => 'Capaian Sedang (50% - 79%)',
        'low' => 'Capaian Rendah (< 50%)',
        'has_below' => 'Ada Tidak Sesuai Standar (> 0)',
        'has_empty' => 'Ada Indikator Kosong (> 0)',
        'all_standard' => 'Sesuai Standar 100%',
    ];

    /** @var array<int, array<string, mixed>> */
    protected array $unitRows = [];

    protected int $lastColIndex;

    protected int $dataRowCount = 0;

    protected bool $hasData = false;

    /**
     * @param  array<string, mixed>  $periodContext
     * @param  int[]  $months
     */
    public function __construct(
        protected array $periodContext,
        protected array $months,
        protected Collection $rows,
        protected string $filterLevel = 'all',
        protected string $sort = 'percentage_desc',
        protected string $categoryLabel = 'Semua Kategori',
        protected string $unitLabel = 'Semua Unit Kerja',
    ) {
        $this->months = array_values(array_filter(array_map('intval', $this->months)));
        if (empty($this->months)) {
            $this->months = [1];
        }

        $this->lastColIndex = 1 + (count($this->months) * 3);
        $this->prepareUnitRows();
    }

    public function title(): string
    {
        return 'Rekap Capaian Unit';
    }

    public function array(): array
    {
        $periodLabel = $this->periodContext['label'] ?? '-';
        $periodTypeLabel = $this->periodContext['periodTypeLabel'] ?? 'Bulan';
        $filterLabel = self::FILTER_LABELS[$this->filterLevel] ?? self::FILTER_LABELS['all'];

        $output = [];
        $output[] = ['REKAPITULASI CAPAIAN INDIKATOR MUTU (IMUT) PER UNIT KERJA'];
        $output[] = ["Periode: {$periodLabel} (Jenjang: {$periodTypeLabel})"];
        $output[] = ["Kategori IMUT: {$this->categoryLabel}"];
        $output[] = ["Filter Status: {$filterLabel} | Unit Kerja: {$this->unitLabel}"];

        // Row 5: Top Header (UNIT KERJA | BULAN ...)
        $topHeader = ['UNIT KERJA'];
        $monthHeader = [''];
        $metricHeader = [''];

        foreach ($this->months as $idx => $monthNumber) {
            $topHeader[] = $idx === 0 ? 'BULAN' : '';
            $topHeader[] = '';
            $topHeader[] = '';

            $monthName = ImutPeriodService::MONTH_NAMES[$monthNumber] ?? "Bulan {$monthNumber}";
            $monthHeader[] = $monthName;
            $monthHeader[] = '';
            $monthHeader[] = '';

            $metricHeader[] = 'Jml Indikator';
            $metricHeader[] = 'Tidak Sesuai Standart';
            $metricHeader[] = 'Persentase (%)';
        }

        $output[] = $topHeader;
        $output[] = $monthHeader;
        $output[] = $metricHeader;

        if (empty($this->unitRows)) {
            $emptyRow = array_fill(0, $this->lastColIndex, '');
            $emptyRow[0] = 'Tidak ada data untuk periode dan filter yang dipilih.';
            $output[] = $emptyRow;

            return $output;
        }

        $monthTotals = [];
        foreach ($this->months as $monthNumber) {
            $monthTotals[$monthNumber] = [
                'total' => 0,
                'meets' => 0,
                'below' => 0,
            ];
        }

        foreach ($this->unitRows as $unit) {
            $row = [$unit['unit_name']];

            foreach ($this->months as $monthNumber) {
                $mData = $unit['months'][$monthNumber] ?? null;

                if ($mData && $mData['total'] > 0) {
                    $row[] = $mData['total'];
                    $row[] = $mData['below'];
                    $row[] = round($mData['meets'] / $mData['total'], 4);

                    $monthTotals[$monthNumber]['total'] += $mData['total'];
                    $monthTotals[$monthNumber]['meets'] += $mData['meets'];
                    $monthTotals[$monthNumber]['below'] += $mData['below'];
                } else {
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                }
            }

            $output[] = $row;
        }

        // Baris Total / Rata-rata
        $totalRow = ['TOTAL / RATA-RATA'];
        foreach ($this->months as $monthNumber) {
            $tot = $monthTotals[$monthNumber];
            if ($tot['total'] > 0) {
                $totalRow[] = $tot['total'];
                $totalRow[] = $tot['below'];
                $totalRow[] = round($tot['meets'] / $tot['total'], 4);
            } else {
                $totalRow[] = '-';
                $totalRow[] = '-';
                $totalRow[] = '-';
            }
        }
        $output[] = $totalRow;

        return $output;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex($this->lastColIndex);

                // Merge judul & metadata (baris 1-4)
                for ($r = 1; $r <= 4; $r++) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                }

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A2:A4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['argb' => 'FF334155']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);

                // Merge 3-tier header cells
                $sheet->mergeCells('A' . self::HEADER_ROW_TOP . ':A' . self::HEADER_ROW_METRIC);
                $sheet->mergeCells('B' . self::HEADER_ROW_TOP . ":{$lastCol}" . self::HEADER_ROW_TOP);

                $colCursor = 2;
                foreach ($this->months as $unused) {
                    $startCol = Coordinate::stringFromColumnIndex($colCursor);
                    $endCol = Coordinate::stringFromColumnIndex($colCursor + 2);
                    $sheet->mergeCells("{$startCol}" . self::HEADER_ROW_MONTH . ":{$endCol}" . self::HEADER_ROW_MONTH);
                    $colCursor += 3;
                }

                // Header styles
                $sheet->getStyle('A' . self::HEADER_ROW_TOP . ":{$lastCol}" . self::HEADER_ROW_METRIC)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getStyle('A' . self::HEADER_ROW_TOP . ':A' . self::HEADER_ROW_METRIC)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
                $sheet->getStyle('B' . self::HEADER_ROW_TOP . ":{$lastCol}" . self::HEADER_ROW_TOP)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
                $sheet->getStyle('B' . self::HEADER_ROW_MONTH . ":{$lastCol}" . self::HEADER_ROW_MONTH)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
                $sheet->getStyle('B' . self::HEADER_ROW_METRIC . ":{$lastCol}" . self::HEADER_ROW_METRIC)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF334155');

                $sheet->getRowDimension(self::HEADER_ROW_TOP)->setRowHeight(22);
                $sheet->getRowDimension(self::HEADER_ROW_MONTH)->setRowHeight(22);
                $sheet->getRowDimension(self::HEADER_ROW_METRIC)->setRowHeight(28);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(34);
                $colCursor = 2;
                foreach ($this->months as $unused) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colCursor))->setWidth(14);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colCursor + 1))->setWidth(21);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colCursor + 2))->setWidth(15);
                    $colCursor += 3;
                }

                $tableEndRow = self::HEADER_ROW_METRIC;

                if (! $this->hasData) {
                    $emptyRowNum = self::DATA_START_ROW;
                    $tableEndRow = $emptyRowNum;
                    $sheet->mergeCells("A{$emptyRowNum}:{$lastCol}{$emptyRowNum}");
                    $sheet->getStyle("A{$emptyRowNum}")->applyFromArray([
                        'font' => ['italic' => true, 'color' => ['argb' => 'FF64748B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($emptyRowNum)->setRowHeight(26);
                } else {
                    $dataEndRow = self::DATA_START_ROW + $this->dataRowCount - 1;
                    $totalRowNum = $dataEndRow + 1;
                    $tableEndRow = $totalRowNum;

                    // Alignment & number formatting untuk data + total rows
                    $sheet->getStyle('A' . self::DATA_START_ROW . ":A{$dataEndRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                        'font' => ['size' => 10, 'color' => ['argb' => 'FF0F172A']],
                    ]);
                    $sheet->getStyle('B' . self::DATA_START_ROW . ":{$lastCol}{$totalRowNum}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'font' => ['size' => 10],
                    ]);

                    // Zebra striping data rows
                    for ($r = self::DATA_START_ROW; $r <= $dataEndRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(20);
                        if (($r - self::DATA_START_ROW) % 2 === 1) {
                            $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                        }
                    }

                    // Format angka & persentase per bulan
                    $colCursor = 2;
                    foreach ($this->months as $unused) {
                        $indCol = Coordinate::stringFromColumnIndex($colCursor);
                        $belowCol = Coordinate::stringFromColumnIndex($colCursor + 1);
                        $pctCol = Coordinate::stringFromColumnIndex($colCursor + 2);

                        $sheet->getStyle("{$indCol}" . self::DATA_START_ROW . ":{$belowCol}{$totalRowNum}")
                            ->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle("{$pctCol}" . self::DATA_START_ROW . ":{$pctCol}{$totalRowNum}")
                            ->getNumberFormat()->setFormatCode('0.0%');

                        $colCursor += 3;
                    }

                    // Total / Rata-rata row styling
                    $sheet->getRowDimension($totalRowNum)->setRowHeight(22);
                    $sheet->getStyle("A{$totalRowNum}:{$lastCol}{$totalRowNum}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFE2E8F0'],
                        ],
                    ]);
                    $sheet->getStyle("A{$totalRowNum}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Borders untuk seluruh tabel (baris 5..tableEndRow)
                $sheet->getStyle('A' . self::HEADER_ROW_TOP . ":{$lastCol}{$tableEndRow}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB('FFCBD5E1');

                // Freeze header & kolom unit kerja
                $sheet->freezePane('B' . self::DATA_START_ROW);

                // Page setup landscape fit to width
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }

    /**
     * Pivot hasil query bulanan menjadi daftar unit terurut beserta metrik tiap bulan,
     * lalu terapkan filter status capaian dan sorting.
     */
    protected function prepareUnitRows(): void
    {
        $grouped = [];

        foreach ($this->rows as $row) {
            $unitId = (int) ($row->unit_kerja_id ?? 0);
            if ($unitId <= 0) {
                continue;
            }

            if (! isset($grouped[$unitId])) {
                $grouped[$unitId] = [
                    'unit_kerja_id' => $unitId,
                    'unit_name' => $row->unit_name ?: "Unit #{$unitId}",
                    'months' => [],
                    'agg_total' => 0,
                    'agg_meets' => 0,
                    'agg_below' => 0,
                    'agg_empty' => 0,
                    'agg_pct' => 0.0,
                ];
            }

            $month = (int) ($row->report_month ?? 0);
            $total = (int) ($row->total_count ?? 0);
            $meets = (int) ($row->meets_standard_count ?? 0);
            $below = (int) ($row->below_standard_count ?? 0);
            $empty = (int) ($row->empty_count ?? 0);

            if ($month >= 1 && $month <= 12) {
                $grouped[$unitId]['months'][$month] = [
                    'total' => $total,
                    'meets' => $meets,
                    'below' => $below,
                    'empty' => $empty,
                ];
            }

            $grouped[$unitId]['agg_total'] += $total;
            $grouped[$unitId]['agg_meets'] += $meets;
            $grouped[$unitId]['agg_below'] += $below;
            $grouped[$unitId]['agg_empty'] += $empty;
        }

        // Hitung persentase agregat & filter status capaian
        $filtered = [];
        foreach ($grouped as $unit) {
            $aggTotal = $unit['agg_total'];
            $aggMeets = $unit['agg_meets'];
            $aggBelow = $unit['agg_below'];
            $aggEmpty = $unit['agg_empty'];
            $ratio = $aggTotal > 0 ? ($aggMeets / $aggTotal) : 0.0;
            $unit['agg_pct'] = round($ratio * 100, 2);

            $matches = match ($this->filterLevel) {
                'high' => $aggTotal > 0 && $ratio >= 0.8,
                'medium' => $aggTotal > 0 && $ratio >= 0.5 && $ratio < 0.8,
                'low' => $aggTotal > 0 && $ratio < 0.5,
                'has_below' => $aggBelow > 0,
                'has_empty' => $aggEmpty > 0,
                'all_standard' => $aggTotal > 0 && $aggMeets === $aggTotal,
                default => true,
            };

            if ($matches) {
                $filtered[] = $unit;
            }
        }

        usort($filtered, function (array $a, array $b): int {
            return match ($this->sort) {
                'unit_name_asc' => strcasecmp($a['unit_name'], $b['unit_name']),
                'unit_name_desc' => strcasecmp($b['unit_name'], $a['unit_name']),
                'percentage_asc' => $a['agg_pct'] <=> $b['agg_pct'],
                'below_desc' => $b['agg_below'] <=> $a['agg_below'],
                default => $b['agg_pct'] <=> $a['agg_pct'],
            };
        });

        $this->unitRows = $filtered;
        $this->dataRowCount = count($filtered);
        $this->hasData = $this->dataRowCount > 0;
    }
}
