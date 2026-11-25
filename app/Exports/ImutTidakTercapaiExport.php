<?php

namespace App\Exports;

use App\Exports\Concerns\HasSafeExcelValueBinder;
use App\Filament\Widgets\ImutTidakTercapai;
use App\Services\Chart\ImutPeriodService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Export Excel daftar indikator mutu tidak tercapai per Unit Kerja dengan kolom bulan terpisah
 * (3 tingkat header: BULAN -> Nama Bulan -> Numerator, Denominator, Capaian %).
 * Indikator yang tampil adalah indikator yang tidak tercapai minimal pada 1 bulan di periode terpilih,
 * dan bulan yang tercapai tetap ditampilkan dengan penanda "(Tercapai)" berwarna hijau.
 */
class ImutTidakTercapaiExport implements FromArray, WithTitle, WithEvents, WithCustomValueBinder, WithStrictNullComparison
{
    use HasSafeExcelValueBinder;

    private const FIXED_COLS_COUNT = 5;

    private const HEADER_ROW_TOP = 6;

    private const HEADER_ROW_MONTH = 7;

    private const HEADER_ROW_METRIC = 8;

    private const DATA_START_ROW = 9;

    /** @var array<int, array{start: int, end: int}> */
    protected array $unitMergeRanges = [];

    /** @var array<int, array{row: int, is_odd_group: bool}> */
    protected array $rowGroupShading = [];

    /** @var string[] Koordinat sel Capaian (%) yang berstatus Tercapai */
    protected array $achievedCapCells = [];

    /** @var string[] Koordinat sel Capaian (%) yang berstatus Tidak Tercapai */
    protected array $belowCapCells = [];

    /** @var string[] Koordinat sel bulan tanpa data ('-') */
    protected array $emptyMonthCells = [];

    protected int $lastColIndex;

    protected int $dataRowCount = 0;

    protected bool $hasData = false;

    /**
     * @param  array<string, mixed>  $periodContext
     * @param  int[]  $months
     * @param  array{total_units: int, total_indicators: int, by_category: Collection, below_per_month?: array<int, int>}  $summary
     */
    public function __construct(
        protected array $periodContext,
        protected string $periodeText,
        protected array $months,
        protected Collection $unitGroups,
        protected array $summary,
        protected string $categoryLabel = 'Semua Kategori',
        protected string $unitLabel = 'Semua Unit Kerja',
        protected string $search = '',
        protected bool $mergeUnits = true,
    ) {
        $this->months = array_values(array_filter(array_map('intval', $this->months)));
        if (empty($this->months)) {
            $this->months = [1];
        }

        $this->lastColIndex = self::FIXED_COLS_COUNT + (count($this->months) * 3);
    }

    public function title(): string
    {
        return 'IMUT Tidak Tercapai';
    }

    public function array(): array
    {
        $this->unitMergeRanges = [];
        $this->rowGroupShading = [];
        $this->achievedCapCells = [];
        $this->belowCapCells = [];
        $this->emptyMonthCells = [];
        $this->dataRowCount = 0;

        $reportName = $this->periodContext['reportName'] ?? '-';
        $periodTypeLabel = $this->periodContext['periodTypeLabel'] ?? 'Bulan';

        $catParts = collect($this->summary['by_category'] ?? [])
            ->map(fn (array $cat) => "{$cat['label']}: {$cat['count']}")
            ->implode(', ');

        $ikhtisarLine = sprintf(
            'Ikhtisar: %s Unit Kerja | %s Indikator Tidak Tercapai%s | Penanda: Hijau = Tercapai, Merah = Tidak Tercapai, "-" = Kosong',
            number_format((int) ($this->summary['total_units'] ?? 0)),
            number_format((int) ($this->summary['total_indicators'] ?? 0)),
            $catParts !== '' ? " ({$catParts})" : ''
        );

        $output = [];
        $output[] = ['LAPORAN DAFTAR INDIKATOR MUTU TIDAK TERCAPAI PER UNIT KERJA'];
        $output[] = ["Periode: {$reportName} (Jenjang: {$periodTypeLabel} | {$this->periodeText})"];
        $output[] = ["Kategori IMUT: {$this->categoryLabel}"];
        $output[] = ['Filter: Unit Kerja: ' . $this->unitLabel . ' | Kata Kunci: ' . (trim($this->search) !== '' ? $this->search : '-')];
        $output[] = [$ikhtisarLine];

        // 3 tingkat header (baris 6, 7, 8)
        $topHeader = ['No', 'Unit Kerja', 'Indikator Mutu', 'Kategori', 'Nilai Standard'];
        $monthHeader = ['', '', '', '', ''];
        $metricHeader = ['', '', '', '', ''];

        foreach ($this->months as $idx => $monthNumber) {
            $topHeader[] = $idx === 0 ? 'BULAN' : '';
            $topHeader[] = '';
            $topHeader[] = '';

            $monthName = ImutPeriodService::MONTH_NAMES[$monthNumber] ?? "Bulan {$monthNumber}";
            $monthHeader[] = $monthName;
            $monthHeader[] = '';
            $monthHeader[] = '';

            $metricHeader[] = 'Numerator';
            $metricHeader[] = 'Denominator';
            $metricHeader[] = 'Capaian (%)';
        }

        $output[] = $topHeader;
        $output[] = $monthHeader;
        $output[] = $metricHeader;

        if ($this->unitGroups->isEmpty()) {
            $this->hasData = false;
            $emptyRow = array_fill(0, $this->lastColIndex, '');
            $emptyRow[0] = 'Tidak ada indikator yang tidak tercapai pada periode dan filter yang dipilih.';
            $output[] = $emptyRow;

            return $output;
        }

        $this->hasData = true;
        $currentExcelRow = self::DATA_START_ROW;
        $flatNumber = 1;

        foreach ($this->unitGroups->values() as $unitIndex => $unit) {
            $items = $unit->indicators ?? collect();
            $itemCount = $items->count();
            if ($itemCount === 0) {
                continue;
            }

            $isOddGroup = ($unitIndex % 2) === 1;
            $groupStartRow = $currentExcelRow;

            foreach ($items as $itemIndex => $item) {
                $catLabel = $item->category_short_name ?: ($item->category_name ?: '-');
                $standard = ImutTidakTercapai::formatStandard($item->target_operator, $item->target_value);
                $standardText = $standard['badge'] !== '-'
                    ? "{$standard['badge']} ({$standard['label']})"
                    : '-';

                $unitCellText = $this->mergeUnits
                    ? ($itemIndex === 0 ? "{$unit->unit_name}\n({$itemCount} indikator tdk tercapai)" : '')
                    : $unit->unit_name;

                $noCell = $this->mergeUnits
                    ? ($itemIndex === 0 ? $unitIndex + 1 : '')
                    : $flatNumber++;

                $row = [
                    $noCell,
                    $unitCellText,
                    (string) ($item->indicator_title ?? '-'),
                    $catLabel,
                    $standardText,
                ];

                $colCursor = self::FIXED_COLS_COUNT + 1;
                foreach ($this->months as $monthNumber) {
                    $mData = $item->months[$monthNumber] ?? null;

                    $numCol = Coordinate::stringFromColumnIndex($colCursor);
                    $denCol = Coordinate::stringFromColumnIndex($colCursor + 1);
                    $capCol = Coordinate::stringFromColumnIndex($colCursor + 2);

                    if ($mData !== null) {
                        $row[] = (float) ($mData['numerator'] ?? 0);
                        $row[] = (float) ($mData['denominator'] ?? 0);
                        $row[] = round(((float) ($mData['capaian_percentage'] ?? 0)) / 100, 4);

                        if (! empty($mData['is_below'])) {
                            $this->belowCapCells[] = "{$capCol}{$currentExcelRow}";
                        } else {
                            $this->achievedCapCells[] = "{$capCol}{$currentExcelRow}";
                        }
                    } else {
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                        $this->emptyMonthCells[] = "{$numCol}{$currentExcelRow}";
                        $this->emptyMonthCells[] = "{$denCol}{$currentExcelRow}";
                        $this->emptyMonthCells[] = "{$capCol}{$currentExcelRow}";
                    }

                    $colCursor += 3;
                }

                $output[] = $row;
                $this->rowGroupShading[] = [
                    'row' => $currentExcelRow,
                    'is_odd_group' => $isOddGroup,
                ];

                $currentExcelRow++;
                $this->dataRowCount++;
            }

            $groupEndRow = $currentExcelRow - 1;
            if ($this->mergeUnits && $groupEndRow >= $groupStartRow) {
                $this->unitMergeRanges[] = [
                    'start' => $groupStartRow,
                    'end' => $groupEndRow,
                ];
            }
        }

        // Baris Footer: Jumlah indikator tidak tercapai per bulan
        $belowPerMonth = $this->summary['below_per_month'] ?? [];
        $footerRow = [
            sprintf(
                'JUMLAH TIDAK TERCAPAI PER BULAN (Total: %s Unit | %s Indikator)',
                number_format((int) ($this->summary['total_units'] ?? 0)),
                number_format((int) ($this->summary['total_indicators'] ?? 0))
            ),
            '',
            '',
            '',
            '',
        ];

        foreach ($this->months as $monthNumber) {
            $countBelow = (int) ($belowPerMonth[$monthNumber] ?? 0);
            $footerRow[] = "{$countBelow} tidak tercapai";
            $footerRow[] = '';
            $footerRow[] = '';
        }

        $output[] = $footerRow;

        return $output;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex($this->lastColIndex);

                // Merge judul, metadata & ikhtisar (baris 1-5)
                for ($r = 1; $r <= 5; $r++) {
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
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF991B1B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF2F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(20);

                // Merge 3-tier header cells
                foreach (['A', 'B', 'C', 'D', 'E'] as $colLetter) {
                    $sheet->mergeCells("{$colLetter}" . self::HEADER_ROW_TOP . ":{$colLetter}" . self::HEADER_ROW_METRIC);
                }
                $sheet->mergeCells('F' . self::HEADER_ROW_TOP . ":{$lastCol}" . self::HEADER_ROW_TOP);

                $colCursor = self::FIXED_COLS_COUNT + 1;
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
                $sheet->getStyle('A' . self::HEADER_ROW_TOP . ':E' . self::HEADER_ROW_METRIC)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
                $sheet->getStyle('F' . self::HEADER_ROW_TOP . ":{$lastCol}" . self::HEADER_ROW_TOP)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');
                $sheet->getStyle('F' . self::HEADER_ROW_MONTH . ":{$lastCol}" . self::HEADER_ROW_MONTH)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
                $sheet->getStyle('F' . self::HEADER_ROW_METRIC . ":{$lastCol}" . self::HEADER_ROW_METRIC)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF334155');

                $sheet->getRowDimension(self::HEADER_ROW_TOP)->setRowHeight(22);
                $sheet->getRowDimension(self::HEADER_ROW_MONTH)->setRowHeight(22);
                $sheet->getRowDimension(self::HEADER_ROW_METRIC)->setRowHeight(24);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(26);
                $sheet->getColumnDimension('C')->setWidth(42);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(17);

                $colCursor = self::FIXED_COLS_COUNT + 1;
                foreach ($this->months as $unused) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colCursor))->setWidth(11);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colCursor + 1))->setWidth(12);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colCursor + 2))->setWidth(18);
                    $colCursor += 3;
                }

                $tableEndRow = self::HEADER_ROW_METRIC;

                if (! $this->hasData || $this->dataRowCount === 0) {
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
                    $footerRowNum = $dataEndRow + 1;
                    $tableEndRow = $footerRowNum;

                    if ($this->mergeUnits) {
                        foreach ($this->unitMergeRanges as $range) {
                            if ($range['end'] > $range['start']) {
                                $sheet->mergeCells("A{$range['start']}:A{$range['end']}");
                                $sheet->mergeCells("B{$range['start']}:B{$range['end']}");
                            }
                        }
                    }

                    // Alternating background per unit group
                    foreach ($this->rowGroupShading as $info) {
                        $r = $info['row'];
                        if ($info['is_odd_group']) {
                            $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                        }
                    }

                    // Alignments fixed columns
                    $sheet->getStyle('A' . self::DATA_START_ROW . ":A{$dataEndRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF334155']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => $this->mergeUnits ? Alignment::VERTICAL_TOP : Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getStyle('B' . self::DATA_START_ROW . ":B{$dataEndRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => $this->mergeUnits ? Alignment::VERTICAL_TOP : Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                    $sheet->getStyle('C' . self::DATA_START_ROW . ":C{$dataEndRow}")->applyFromArray([
                        'font' => ['size' => 10, 'color' => ['argb' => 'FF0F172A']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                    $sheet->getStyle('D' . self::DATA_START_ROW . ":D{$dataEndRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF1E293B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle('E' . self::DATA_START_ROW . ":E{$dataEndRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF166534']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);

                    // Formatting kolom Numerator, Denominator, Capaian (%) per bulan
                    $colCursor = self::FIXED_COLS_COUNT + 1;
                    foreach ($this->months as $unused) {
                        $numCol = Coordinate::stringFromColumnIndex($colCursor);
                        $denCol = Coordinate::stringFromColumnIndex($colCursor + 1);
                        $capCol = Coordinate::stringFromColumnIndex($colCursor + 2);

                        $sheet->getStyle("{$numCol}" . self::DATA_START_ROW . ":{$denCol}{$dataEndRow}")->applyFromArray([
                            'font' => ['size' => 10],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        $sheet->getStyle("{$numCol}" . self::DATA_START_ROW . ":{$denCol}{$dataEndRow}")
                            ->getNumberFormat()->setFormatCode('#,##0');

                        $sheet->getStyle("{$capCol}" . self::DATA_START_ROW . ":{$capCol}{$dataEndRow}")->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);

                        $colCursor += 3;
                    }

                    // Penanda warna & format khusus sel Capaian: Merah = Tidak Tercapai, Hijau + "(Tercapai)" = Tercapai
                    foreach ($this->belowCapCells as $coord) {
                        $sheet->getStyle($coord)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF991B1B']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                        ]);
                        $sheet->getStyle($coord)->getNumberFormat()->setFormatCode('0.0%');
                    }

                    foreach ($this->achievedCapCells as $coord) {
                        $sheet->getStyle($coord)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF166534']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
                        ]);
                        $sheet->getStyle($coord)->getNumberFormat()->setFormatCode('0.0%" (Tercapai)"');
                    }

                    foreach ($this->emptyMonthCells as $coord) {
                        $sheet->getStyle($coord)->applyFromArray([
                            'font' => ['size' => 10, 'color' => ['argb' => 'FF64748B']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                    }

                    // Merge footer cells & styling
                    $sheet->mergeCells("A{$footerRowNum}:E{$footerRowNum}");
                    $colCursor = self::FIXED_COLS_COUNT + 1;
                    foreach ($this->months as $unused) {
                        $startCol = Coordinate::stringFromColumnIndex($colCursor);
                        $endCol = Coordinate::stringFromColumnIndex($colCursor + 2);
                        $sheet->mergeCells("{$startCol}{$footerRowNum}:{$endCol}{$footerRowNum}");
                        $colCursor += 3;
                    }

                    $sheet->getRowDimension($footerRowNum)->setRowHeight(22);
                    $sheet->getStyle("A{$footerRowNum}:{$lastCol}{$footerRowNum}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("A{$footerRowNum}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Borders seluruh tabel
                $sheet->getStyle('A' . self::HEADER_ROW_TOP . ":{$lastCol}{$tableEndRow}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB('FFCBD5E1');

                // Freeze kolom tetap (A..E) dan header (baris 1..8)
                $sheet->freezePane('F' . self::DATA_START_ROW);

                // Page setup A4 Landscape
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
