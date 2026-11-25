<?php

namespace App\Exports;

use App\Exports\Concerns\HasSafeExcelValueBinder;
use App\Filament\Widgets\ImutTidakTercapai;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Export Excel daftar indikator mutu tidak tercapai per Unit Kerja
 * (mengikuti struktur tabel laporan cetak dengan Numerator & Denominator terpisah).
 */
class ImutTidakTercapaiExport implements FromArray, WithTitle, WithEvents, WithCustomValueBinder, WithStrictNullComparison
{
    use HasSafeExcelValueBinder;

    private const HEADER_ROW = 6;

    private const DATA_START_ROW = 7;

    /** @var array<int, array{start: int, end: int, is_odd_group: bool}> */
    protected array $unitMergeRanges = [];

    /** @var array<int, array{row: int, is_odd_group: bool}> */
    protected array $rowGroupShading = [];

    protected int $dataRowCount = 0;

    protected bool $hasData = false;

    /**
     * @param  array<string, mixed>  $periodContext
     * @param  array{total_units: int, total_indicators: int, by_category: Collection}  $summary
     */
    public function __construct(
        protected array $periodContext,
        protected string $periodeText,
        protected Collection $unitGroups,
        protected array $summary,
        protected string $categoryLabel = 'Semua Kategori',
        protected string $unitLabel = 'Semua Unit Kerja',
        protected string $search = '',
        protected bool $mergeUnits = true,
    ) {}

    public function title(): string
    {
        return 'IMUT Tidak Tercapai';
    }

    public function array(): array
    {
        $this->unitMergeRanges = [];
        $this->rowGroupShading = [];
        $this->dataRowCount = 0;

        $reportName = $this->periodContext['reportName'] ?? '-';
        $periodTypeLabel = $this->periodContext['periodTypeLabel'] ?? 'Bulan';

        $catParts = collect($this->summary['by_category'] ?? [])
            ->map(fn (array $cat) => "{$cat['label']}: {$cat['count']}")
            ->implode(', ');

        $ikhtisarLine = sprintf(
            'Ikhtisar: %s Unit Kerja | %s Indikator Tidak Tercapai%s',
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

        // Row 6: Table Header
        $output[] = [
            'No',
            'Unit Kerja',
            'Indikator Mutu',
            'Kategori',
            'Numerator',
            'Denominator',
            'Capaian (%)',
            'Nilai Standard',
        ];

        if ($this->unitGroups->isEmpty()) {
            $this->hasData = false;
            $output[] = ['Tidak ada indikator yang tidak tercapai pada periode dan filter yang dipilih.', '', '', '', '', '', '', ''];

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
                $capaianRatio = round(((float) ($item->capaian_percentage ?? 0)) / 100, 4);
                $standard = ImutTidakTercapai::formatStandard($item->target_operator, $item->target_value);
                $standardText = $standard['badge'] !== '-'
                    ? "{$standard['badge']} ({$standard['label']})"
                    : '-';

                $indicatorTitle = (string) ($item->indicator_title ?? '-');
                $monthsCount = (int) ($item->months_count ?? 1);
                if ($monthsCount > 1) {
                    $indicatorTitle .= "\n(Akumulasi {$monthsCount} bln)";
                }

                $unitCellText = $this->mergeUnits
                    ? ($itemIndex === 0 ? "{$unit->unit_name}\n({$itemCount} indikator tidak tercapai)" : '')
                    : $unit->unit_name;

                $noCell = $this->mergeUnits
                    ? ($itemIndex === 0 ? $unitIndex + 1 : '')
                    : $flatNumber++;

                $output[] = [
                    $noCell,
                    $unitCellText,
                    $indicatorTitle,
                    $catLabel,
                    (float) ($item->numerator_value ?? 0),
                    (float) ($item->denominator_value ?? 0),
                    $capaianRatio,
                    $standardText,
                ];

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
                    'is_odd_group' => $isOddGroup,
                ];
            }
        }

        // Baris Total / Ringkasan
        $output[] = [
            sprintf(
                'TOTAL / RINGKASAN: %s Unit Kerja | %s Indikator Tidak Tercapai',
                number_format((int) ($this->summary['total_units'] ?? 0)),
                number_format((int) ($this->summary['total_indicators'] ?? 0))
            ),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        return $output;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                // Merge judul, metadata & ikhtisar (baris 1-5)
                for ($r = 1; $r <= 5; $r++) {
                    $sheet->mergeCells("A{$r}:H{$r}");
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

                // Table header style (row 6)
                $sheet->getStyle('A' . self::HEADER_ROW . ':H' . self::HEADER_ROW)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getRowDimension(self::HEADER_ROW)->setRowHeight(24);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(7);
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(46);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(13);
                $sheet->getColumnDimension('F')->setWidth(13);
                $sheet->getColumnDimension('G')->setWidth(13);
                $sheet->getColumnDimension('H')->setWidth(18);

                $tableEndRow = self::HEADER_ROW;

                if (! $this->hasData || $this->dataRowCount === 0) {
                    $emptyRowNum = self::DATA_START_ROW;
                    $tableEndRow = $emptyRowNum;
                    $sheet->mergeCells("A{$emptyRowNum}:H{$emptyRowNum}");
                    $sheet->getStyle("A{$emptyRowNum}")->applyFromArray([
                        'font' => ['italic' => true, 'color' => ['argb' => 'FF64748B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($emptyRowNum)->setRowHeight(26);
                } else {
                    $dataEndRow = self::DATA_START_ROW + $this->dataRowCount - 1;
                    $totalRowNum = $dataEndRow + 1;
                    $tableEndRow = $totalRowNum;

                    // Merge unit cells per group jika opsi merge aktif
                    if ($this->mergeUnits) {
                        foreach ($this->unitMergeRanges as $range) {
                            if ($range['end'] > $range['start']) {
                                $sheet->mergeCells("A{$range['start']}:A{$range['end']}");
                                $sheet->mergeCells("B{$range['start']}:B{$range['end']}");
                            }
                        }
                    }

                    // Alternating background per unit group + wrap text
                    foreach ($this->rowGroupShading as $info) {
                        $r = $info['row'];
                        if ($info['is_odd_group']) {
                            $sheet->getStyle("A{$r}:H{$r}")
                                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                        }
                    }

                    // Column alignments
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
                    $sheet->getStyle('E' . self::DATA_START_ROW . ":F{$dataEndRow}")->applyFromArray([
                        'font' => ['size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle('E' . self::DATA_START_ROW . ":F{$dataEndRow}")
                        ->getNumberFormat()->setFormatCode('#,##0');

                    $sheet->getStyle('G' . self::DATA_START_ROW . ":G{$dataEndRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF991B1B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle('G' . self::DATA_START_ROW . ":G{$dataEndRow}")
                        ->getNumberFormat()->setFormatCode('0.0%');

                    $sheet->getStyle('H' . self::DATA_START_ROW . ":H{$dataEndRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF166534']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);

                    // Footer summary row
                    $sheet->mergeCells("A{$totalRowNum}:H{$totalRowNum}");
                    $sheet->getRowDimension($totalRowNum)->setRowHeight(22);
                    $sheet->getStyle("A{$totalRowNum}:H{$totalRowNum}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                // Table borders
                $sheet->getStyle('A' . self::HEADER_ROW . ":H{$tableEndRow}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB('FFCBD5E1');

                // Freeze panes below header
                $sheet->freezePane('A' . self::DATA_START_ROW);

                // Page setup A4 Landscape
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
