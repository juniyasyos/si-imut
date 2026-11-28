<?php

namespace App\Exports;

use App\Exports\Concerns\HasSafeExcelValueBinder;
use App\Modules\FormEngine\Models\EnhancedFormField;
use App\Modules\FormEngine\Models\FormTemplate;
use App\Support\DailyReportFieldValueFormatter;
use Carbon\Carbon;
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

class MonitoringExport implements FromArray, WithEvents, WithTitle, WithCustomValueBinder, WithStrictNullComparison
{
    use HasSafeExcelValueBinder;

    private const FIXED_HEADERS = [
        'Tanggal',
        'Unit Kerja',
        'Pengumpul Data',
        'Validator',
        'Status Validasi',
    ];

    /** @var array<int, array{start: int, end: int}> */
    protected array $horizontalHeaderMerges = [];

    /** @var int[] 1-based column indices for vertical merge across 2 header rows */
    protected array $verticalHeaderMerges = [];

    /** @var int[] 1-based column widths */
    protected array $columnWidths = [];

    protected bool $hasMultiLevelHeaders = false;

    protected int $lastColIndex = 5;

    protected int $totalRows = 1;

    public function __construct(
        protected FormTemplate $template,
        protected ?Carbon $startDate = null,
        protected ?Carbon $endDate = null,
    ) {}

    public function array(): array
    {
        $this->horizontalHeaderMerges = [];
        $this->verticalHeaderMerges = [];
        $this->columnWidths = [];

        $orderedFields = $this->getOrderedFields();
        $responses = $this->template->dailyReportResponses;
        $this->hasMultiLevelHeaders = $orderedFields->contains(
            fn (EnhancedFormField $field) => in_array($field->field_type, ['time_duration', 'time_range'], true)
        );

        [$topHeader, $subHeader] = $this->buildHeaderRows($orderedFields, $responses);

        $output = [$topHeader];
        if ($this->hasMultiLevelHeaders) {
            $output[] = $subHeader;
        }

        foreach ($responses as $response) {
            $row = [
                $response->report_date?->format('d/m/Y') ?? '',
                $response->unitKerja->unit_name ?? '',
                $response->submittedBy->name ?? '',
                $response->validator->name ?? '',
                DailyReportFieldValueFormatter::formatValidationStatus($response->validation_status),
            ];

            foreach ($orderedFields as $field) {
                $fieldResponse = $response->fieldResponses->firstWhere('form_field_id', $field->id);
                $rawValue = $fieldResponse?->field_value;

                if ($field->field_type === 'time_duration') {
                    $cols = $fieldResponse
                        ? DailyReportFieldValueFormatter::formatTimeDurationColumns($rawValue)
                        : ['start' => '', 'end' => '', 'duration' => '', 'status' => ''];

                    $row[] = $cols['start'];
                    $row[] = $cols['end'];
                    $row[] = $cols['duration'];
                    $row[] = $cols['status'];

                    continue;
                }

                if ($field->field_type === 'time_range') {
                    $cols = $fieldResponse
                        ? DailyReportFieldValueFormatter::formatTimeRangeColumns($field, $rawValue)
                        : ['input_value' => '', 'status' => ''];

                    $row[] = $cols['input_value'];
                    $row[] = $cols['status'];

                    continue;
                }

                $row[] = $fieldResponse
                    ? DailyReportFieldValueFormatter::formatForExport($field, $rawValue)
                    : '';
            }

            $output[] = $row;
        }

        $this->lastColIndex = count($topHeader);
        $this->totalRows = count($output);

        return $output;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex($this->lastColIndex);
                $headerEndRow = $this->hasMultiLevelHeaders ? 2 : 1;

                if ($this->hasMultiLevelHeaders) {
                    foreach ($this->verticalHeaderMerges as $colIdx) {
                        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                        $sheet->mergeCells("{$colLetter}1:{$colLetter}2");
                    }

                    foreach ($this->horizontalHeaderMerges as $range) {
                        $startLetter = Coordinate::stringFromColumnIndex($range['start']);
                        $endLetter = Coordinate::stringFromColumnIndex($range['end']);
                        $sheet->mergeCells("{$startLetter}1:{$endLetter}1");
                    }

                    $sheet->getRowDimension(1)->setRowHeight(24);
                    $sheet->getRowDimension(2)->setRowHeight(22);

                    $sheet->getStyle("A1:{$lastCol}1")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF1E293B');
                    $sheet->getStyle("A2:{$lastCol}2")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF334155');
                } else {
                    $sheet->getRowDimension(1)->setRowHeight(24);
                    $sheet->getStyle("A1:{$lastCol}1")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF1E293B');
                }

                $sheet->getStyle("A1:{$lastCol}{$headerEndRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle("A1:{$lastCol}{$this->totalRows}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB('FFCBD5E1');

                foreach ($this->columnWidths as $colIdx => $width) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->getColumnDimension($colLetter)->setWidth($width);
                }

                $sheet->freezePane('A' . ($headerEndRow + 1));
            },
        ];
    }

    public function title(): string
    {
        $rawTitle = $this->template->imutProfile?->imutData?->title ?? $this->template->title;

        return DailyReportFieldValueFormatter::sanitizeSheetTitle($rawTitle);
    }

    protected function getOrderedFields(): Collection
    {
        return $this->template->formFields->sortBy('order_index')->values();
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function buildHeaderRows(Collection $orderedFields, iterable $responses): array
    {
        $topHeader = [];
        $subHeader = [];
        $colIndex = 1;

        $fixedWidths = [14, 22, 20, 20, 18];
        foreach (self::FIXED_HEADERS as $i => $label) {
            $topHeader[] = $label;
            $subHeader[] = '';
            $this->verticalHeaderMerges[] = $colIndex;
            $this->columnWidths[$colIndex] = $fixedWidths[$i] ?? 18;
            $colIndex++;
        }

        foreach ($orderedFields as $field) {
            if ($field->field_type === 'time_duration') {
                $topHeader[] = $field->field_label;
                $topHeader[] = '';
                $topHeader[] = '';
                $topHeader[] = '';

                array_push($subHeader, 'Mulai', 'Selesai', 'Waktu Selisih', 'Kesesuaian');

                $this->horizontalHeaderMerges[] = ['start' => $colIndex, 'end' => $colIndex + 3];
                $this->columnWidths[$colIndex] = 18;
                $this->columnWidths[$colIndex + 1] = 18;
                $this->columnWidths[$colIndex + 2] = 16;
                $this->columnWidths[$colIndex + 3] = 15;
                $colIndex += 4;

                continue;
            }

            if ($field->field_type === 'time_range') {
                $bounds = DailyReportFieldValueFormatter::resolveTimeRangeBoundaryLabel($field, $responses);
                $groupLabel = $bounds !== null ? "{$field->field_label} ({$bounds})" : $field->field_label;

                $topHeader[] = $groupLabel;
                $topHeader[] = '';

                array_push($subHeader, 'Waktu', 'Kesesuaian');

                $this->horizontalHeaderMerges[] = ['start' => $colIndex, 'end' => $colIndex + 1];
                $this->columnWidths[$colIndex] = 14;
                $this->columnWidths[$colIndex + 1] = 15;
                $colIndex += 2;

                continue;
            }

            $topHeader[] = $field->field_label;
            $subHeader[] = '';
            $this->verticalHeaderMerges[] = $colIndex;
            $this->columnWidths[$colIndex] = 22;
            $colIndex++;
        }

        return [$topHeader, $subHeader];
    }
}
