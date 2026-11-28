<?php

namespace Tests\Feature\Exports;

use App\Domain\DailyReport\TableViewDomain;
use App\Exports\MonitoringExport;
use App\Models\DailyReportResponse;
use App\Models\EnhancedFormField;
use App\Models\FieldResponse;
use App\Models\FormFieldOption;
use App\Models\FormTemplate;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\UnitKerja;
use App\Models\User;
use App\Modules\DailyReport\Services\Exports\DailyReportMonitoringExport;
use App\Modules\DailyReport\Services\FieldResponseBuilderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MonitoringExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['iam.app_env' => 'local']);
        Permission::findOrCreate('view_all_data_imut::data');
    }

    public function test_monitoring_export_produces_valid_xlsx_without_array_values_across_all_field_types(): void
    {
        $unit = UnitKerja::factory()->create(['unit_name' => 'IGD']);
        $submitter = User::factory()->create(['name' => 'Budi']);
        $validator = User::factory()->create(['name' => 'Siti']);

        $imutData = ImutData::factory()->create(['title' => 'Audit / Kepatuhan: Hand Hygiene [2026]']);
        $profile = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
        $template = FormTemplate::factory()->create(['imut_profile_id' => $profile->id]);

        $fText = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'catatan',
            'field_label' => 'Catatan',
            'field_type' => 'text',
            'order_index' => 1,
        ]);

        $fNumber = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'jumlah',
            'field_label' => 'Jumlah',
            'field_type' => 'number',
            'order_index' => 2,
        ]);

        $fDate = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'tgl_kejadian',
            'field_label' => 'Tanggal Kejadian',
            'field_type' => 'date',
            'order_index' => 3,
        ]);

        $fBool = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'patuh',
            'field_label' => 'Patuh',
            'field_type' => 'boolean',
            'order_index' => 4,
        ]);

        $fSingle = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'metode',
            'field_label' => 'Metode',
            'field_type' => 'single_select',
            'order_index' => 5,
        ]);
        FormFieldOption::create([
            'enhanced_form_field_id' => $fSingle->id,
            'option_text' => 'Hand Rub',
            'option_value' => 'hand_rub',
            'order_index' => 1,
        ]);

        $fMulti = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'momen',
            'field_label' => 'Momen',
            'field_type' => 'multi_select',
            'order_index' => 6,
        ]);
        FormFieldOption::create([
            'enhanced_form_field_id' => $fMulti->id,
            'option_text' => 'Sebelum Kontak',
            'option_value' => 'm1',
            'order_index' => 1,
        ]);
        FormFieldOption::create([
            'enhanced_form_field_id' => $fMulti->id,
            'option_text' => 'Sesudah Kontak',
            'option_value' => 'm2',
            'order_index' => 2,
        ]);

        $fDuration = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'durasi',
            'field_label' => 'Durasi',
            'field_type' => 'time_duration',
            'order_index' => 7,
        ]);

        $fRange = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'jam_lapor',
            'field_label' => 'Jam Lapor',
            'field_type' => 'time_range',
            'validation_config' => [
                'default_start_time' => '08:00',
                'default_end_time' => '17:00',
            ],
            'order_index' => 8,
        ]);

        $report = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $submitter->id,
            'report_date' => '2026-07-10',
            'total_score' => 100,
            'compliance_status' => true,
            'validation_status' => 'valid',
            'validated_by' => $validator->id,
            'validated_at' => now(),
        ]);

        // Simulate legacy/Edit wrapped-array and empty-array DB shapes
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fText->id,
            'field_value' => ['Observasi shift pagi'],
            'compliance_score' => 100,
        ]);
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fNumber->id,
            'field_value' => [25],
            'compliance_score' => 100,
        ]);
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fDate->id,
            'field_value' => ['2026-07-10'],
            'compliance_score' => 100,
        ]);
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fBool->id,
            'field_value' => ['1'],
            'compliance_score' => 100,
        ]);
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fSingle->id,
            'field_value' => ['hand_rub'],
            'compliance_score' => 100,
        ]);
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fMulti->id,
            'field_value' => ['m1', 'm2'],
            'compliance_score' => 100,
        ]);
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fDuration->id,
            'field_value' => [
                'type' => 'time_duration',
                'start_time' => '2026-07-10 08:00',
                'end_time' => '2026-07-10 08:15',
                'valid_indicator' => '1',
            ],
            'compliance_score' => 100,
        ]);
        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $fRange->id,
            'field_value' => [
                'type' => 'time_range',
                'input_value' => '09:30',
                'start_time' => null,
                'end_time' => null,
                'valid_indicator' => '0',
            ],
            'compliance_score' => 100,
        ]);

        // Second row with empty arrays [] to ensure no [] or crashes
        $emptyReport = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $submitter->id,
            'report_date' => '2026-07-11',
            'total_score' => 0,
            'compliance_status' => false,
            'validation_status' => 'pending',
        ]);
        foreach ([$fText, $fNumber, $fDate, $fBool, $fSingle, $fMulti, $fDuration, $fRange] as $f) {
            FieldResponse::create([
                'daily_report_response_id' => $emptyReport->id,
                'form_field_id' => $f->id,
                'field_value' => [],
                'compliance_score' => 0,
            ]);
        }

        $loadedTemplate = FormTemplate::with([
            'imutProfile.imutData',
            'formFields' => fn ($q) => $q->orderBy('order_index'),
            'formFields.options',
            'dailyReportResponses' => fn ($q) => $q
                ->with(['submittedBy', 'validator', 'unitKerja', 'fieldResponses'])
                ->orderBy('report_date'),
        ])->findOrFail($template->id);

        $export = new MonitoringExport($loadedTemplate, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31'));
        $rawBinary = Excel::raw($export, ExcelFormat::XLSX);

        $tmpPath = tempnam(sys_get_temp_dir(), 'mon_export_') . '.xlsx';
        file_put_contents($tmpPath, $rawBinary);

        try {
            $spreadsheet = IOFactory::load($tmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, false, false);

            $this->assertSame([
                'Tanggal',
                'Unit Kerja',
                'Pengumpul Data',
                'Validator',
                'Status Validasi',
                'Catatan',
                'Jumlah',
                'Tanggal Kejadian',
                'Patuh',
                'Metode',
                'Momen',
                'Durasi',
                'Jam Lapor',
            ], $rows[0]);

            $this->assertSame('10/07/2026', $rows[1][0]);
            $this->assertSame('IGD', $rows[1][1]);
            $this->assertSame('Budi', $rows[1][2]);
            $this->assertSame('Siti', $rows[1][3]);
            $this->assertSame('Valid', $rows[1][4]);
            $this->assertSame('Observasi shift pagi', $rows[1][5]);
            $this->assertEquals(25, $rows[1][6]);
            $this->assertSame('10/07/2026', $rows[1][7]);
            $this->assertSame('Ya', $rows[1][8]);
            $this->assertSame('Hand Rub', $rows[1][9]);
            $this->assertSame('Sebelum Kontak, Sesudah Kontak', $rows[1][10]);
            $this->assertSame('2026-07-10 08:00 - 2026-07-10 08:15 (15 menit, Sesuai)', $rows[1][11]);
            $this->assertSame('09:30 (Sesuai, rentang 08:00 - 17:00)', $rows[1][12]);

            $this->assertSame('Belum Divalidasi', $rows[2][4]);
            for ($col = 5; $col <= 12; $col++) {
                $this->assertNull($rows[2][$col]);
            }

            // Ensure DailyReportMonitoringExport produces identical rows
            $serviceExport = new DailyReportMonitoringExport($loadedTemplate);
            $this->assertSame($export->collection()->toArray(), $serviceExport->collection()->toArray());
            $this->assertSame($export->headings(), $serviceExport->headings());
            $this->assertSame($export->title(), $serviceExport->title());

            // Ensure TableViewDomain unwraps wrapped scalar arrays on regular fields
            $tableRows = app(TableViewDomain::class)->transformEntriesToTableData(
                $loadedTemplate->dailyReportResponses,
                $loadedTemplate
            );
            $this->assertSame('Observasi shift pagi', $tableRows[0]['catatan']);
            $this->assertSame(25, $tableRows[0]['jumlah']);
            $this->assertSame('2026-07-10', $tableRows[0]['tgl_kejadian']);
            $this->assertNull($tableRows[1]['catatan']);
            $this->assertNull($tableRows[1]['jumlah']);

            // Ensure export.monitoring HTTP route succeeds for authorized unit user
            $submitter->unitKerjas()->attach($unit->id);
            $httpResponse = $this->withoutMiddleware(\App\Http\Middleware\EnsureUserIsActive::class)
                ->actingAs($submitter)
                ->get(route('export.monitoring', $template->id) . '?month=2026-07');
            $httpResponse->assertStatus(200);
        } finally {
            @unlink($tmpPath);
        }
    }

    public function test_field_response_builder_normalizes_time_range_using_validation_config_defaults(): void
    {
        $unit = UnitKerja::factory()->create(['unit_name' => 'Lab']);
        $user = User::factory()->create();
        $imutData = ImutData::factory()->create();
        $profile = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
        $template = FormTemplate::factory()->create(['imut_profile_id' => $profile->id]);

        $field = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'waktu_lapor',
            'field_label' => 'Waktu Lapor',
            'field_type' => 'time_range',
            'validation_config' => [
                'default_start_time' => '08:00',
                'default_end_time' => '17:00',
            ],
            'order_index' => 1,
        ]);

        $report = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $user->id,
            'report_date' => '2026-07-15',
            'total_score' => 100,
            'compliance_status' => true,
        ]);

        $fieldResponse = app(FieldResponseBuilderService::class)->buildWithScore(
            $report,
            $field,
            ['waktu_lapor_input_value' => '10:15'],
            100.0
        );

        $this->assertSame([
            'type' => 'time_range',
            'input_value' => '10:15',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'valid_indicator' => '1',
        ], $fieldResponse->field_value);
    }
}
