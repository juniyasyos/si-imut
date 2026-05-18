<?php

namespace Tests\Unit\Services;

use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use App\Services\Reporting\DailyReportAggregationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportAggregationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DailyReportAggregationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DailyReportAggregationService();
    }

    /** @test */
    public function it_calculates_numerator_and_denominator_correctly()
    {
        // Setup: Create necessary models
        $unitKerja = UnitKerja::factory()->create();
        $formTemplate = FormTemplate::factory()->create();
        $imutProfile = ImutProfile::factory()->create();
        $imutProfile->formTemplates()->attach($formTemplate->id);

        $laporan = LaporanImut::factory()->create([
            'assessment_period_start' => Carbon::parse('2025-01-01'),
            'assessment_period_end' => Carbon::parse('2025-01-10'),
        ]);

        $laporanUnitKerja = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $penilaian = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
            'imut_profil_id' => $imutProfile->id,
        ]);

        // Create daily reports: 5 dengan 100%, 3 dengan < 100%
        $perfectDates = ['2025-01-01', '2025-01-02', '2025-01-05', '2025-01-08', '2025-01-10'];
        $imperfectDates = ['2025-01-03', '2025-01-04', '2025-01-07'];

        foreach ($perfectDates as $date) {
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse($date),
                'total_score' => 100,
                'compliance_status' => true,
                'calculation_details' => ['total_score' => 100, 'compliance_status' => true],
            ]);
        }

        foreach ($imperfectDates as $date) {
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse($date),
                'total_score' => 95,
                'compliance_status' => false,
                'calculation_details' => ['total_score' => 95, 'compliance_status' => false],
            ]);
        }


        // Act
        $result = $this->service->calculateForPenilaian($penilaian);

        // Assert
        $this->assertEquals(5, $result['numerator']); // 5 hari perfect
        $this->assertEquals(8, $result['denominator']); // 8 hari total
        $this->assertEquals(62.5, $result['percentage']); // (5/8) * 100
        $this->assertArrayHasKey('calculation_metadata', $result);
    }

    /** @test */
    public function it_handles_empty_daily_reports()
    {
        // Setup
        $unitKerja = UnitKerja::factory()->create();
        $formTemplate = FormTemplate::factory()->create();
        $imutProfile = ImutProfile::factory()->create();
        $imutProfile->formTemplates()->attach($formTemplate->id);

        $laporan = LaporanImut::factory()->create([
            'assessment_period_start' => Carbon::parse('2025-01-01'),
            'assessment_period_end' => Carbon::parse('2025-01-10'),
        ]);

        $laporanUnitKerja = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $penilaian = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
            'imut_profil_id' => $imutProfile->id,
        ]);

        // No daily reports created

        // Act
        $result = $this->service->calculateForPenilaian($penilaian);

        // Assert
        $this->assertEquals(0, $result['numerator']);
        $this->assertEquals(0, $result['denominator']);
        $this->assertEquals(0, $result['percentage']);
    }

    /** @test */
    public function it_handles_all_perfect_compliance()
    {
        // Setup
        $unitKerja = UnitKerja::factory()->create();
        $formTemplate = FormTemplate::factory()->create();
        $imutProfile = ImutProfile::factory()->create();
        $imutProfile->formTemplates()->attach($formTemplate->id);

        $laporan = LaporanImut::factory()->create([
            'assessment_period_start' => Carbon::parse('2025-01-01'),
            'assessment_period_end' => Carbon::parse('2025-01-05'),
        ]);

        $laporanUnitKerja = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $penilaian = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
            'imut_profil_id' => $imutProfile->id,
        ]);

        // Create 5 daily reports, all 100%
        for ($i = 1; $i <= 5; $i++) {
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse("2025-01-0{$i}"),
                'total_score' => 100,
                'compliance_status' => true,
                'calculation_details' => ['total_score' => 100, 'compliance_status' => true],
            ]);
        }

        // Act
        $result = $this->service->calculateForPenilaian($penilaian);

        // Assert
        $this->assertEquals(5, $result['numerator']);
        $this->assertEquals(5, $result['denominator']);
        $this->assertEquals(100, $result['percentage']); // Perfect!
    }

    /** @test */
    public function it_handles_no_perfect_compliance()
    {
        // Setup
        $unitKerja = UnitKerja::factory()->create();
        $formTemplate = FormTemplate::factory()->create();
        $imutProfile = ImutProfile::factory()->create();
        $imutProfile->formTemplates()->attach($formTemplate->id);

        $laporan = LaporanImut::factory()->create([
            'assessment_period_start' => Carbon::parse('2025-01-01'),
            'assessment_period_end' => Carbon::parse('2025-01-05'),
        ]);

        $laporanUnitKerja = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $penilaian = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
            'imut_profil_id' => $imutProfile->id,
        ]);

        // Create 5 daily reports, none 100%
        $scores = [95, 88, 92, 85, 97];
        for ($i = 0; $i < 5; $i++) {
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse("2025-01-0" . ($i + 1)),
                'total_score' => $scores[$i],
                'compliance_status' => false,
                'calculation_details' => ['total_score' => $scores[$i], 'compliance_status' => false],
            ]);
        }


        // Act
        $result = $this->service->calculateForPenilaian($penilaian);

        // Assert
        $this->assertEquals(0, $result['numerator']); // No perfect days
        $this->assertEquals(5, $result['denominator']);
        $this->assertEquals(0, $result['percentage']);
    }

    /** @test */
    public function it_identifies_missing_dates_correctly()
    {
        // Setup
        $unitKerja = UnitKerja::factory()->create();
        $formTemplate = FormTemplate::factory()->create();
        $imutProfile = ImutProfile::factory()->create();
        $imutProfile->formTemplates()->attach($formTemplate->id);

        $laporan = LaporanImut::factory()->create([
            'assessment_period_start' => Carbon::parse('2025-01-01'),
            'assessment_period_end' => Carbon::parse('2025-01-10'),
        ]);

        $laporanUnitKerja = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $penilaian = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
            'imut_profil_id' => $imutProfile->id,
        ]);

        // Create reports only for: 01, 05, 10 (7 dates missing)
        $reportedDates = ['2025-01-01', '2025-01-05', '2025-01-10'];
        foreach ($reportedDates as $date) {
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse($date),
                'total_score' => 100,
                'compliance_status' => true,
                'calculation_details' => ['total_score' => 100, 'compliance_status' => true],
            ]);
        }


        // Act
        $result = $this->service->calculateForPenilaian($penilaian);

        // Assert
        $this->assertArrayHasKey('missing_dates', $result['calculation_metadata']);
        $missingDates = $result['calculation_metadata']['missing_dates'];
        $this->assertCount(7, $missingDates); // 10 days - 3 reported = 7 missing
        $this->assertContains('2025-01-02', $missingDates);
        $this->assertContains('2025-01-03', $missingDates);
        $this->assertContains('2025-01-04', $missingDates);
        $this->assertContains('2025-01-06', $missingDates);
        $this->assertContains('2025-01-07', $missingDates);
        $this->assertContains('2025-01-08', $missingDates);
        $this->assertContains('2025-01-09', $missingDates);
    }

    /** @test */
    public function it_updates_penilaian_with_calculated_values()
    {
        // Setup
        $unitKerja = UnitKerja::factory()->create();
        $formTemplate = FormTemplate::factory()->create();
        $imutProfile = ImutProfile::factory()->create();
        $imutProfile->formTemplates()->attach($formTemplate->id);

        $laporan = LaporanImut::factory()->create([
            'assessment_period_start' => Carbon::parse('2025-01-01'),
            'assessment_period_end' => Carbon::parse('2025-01-05'),
        ]);

        $laporanUnitKerja = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $penilaian = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
            'imut_profil_id' => $imutProfile->id,
            'numerator_value' => null,
            'denominator_value' => null,
            'is_auto_calculated' => false,
        ]);

        // Create 3 perfect days out of 5
        for ($i = 1; $i <= 5; $i++) {
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse("2025-01-0{$i}"),
                'total_score' => $i <= 3 ? 100 : 90,
                'compliance_status' => $i <= 3,
                'calculation_details' => ['total_score' => $i <= 3 ? 100 : 90, 'compliance_status' => $i <= 3],
            ]);
        }


        // Act
        $success = $this->service->updatePenilaian($penilaian);

        // Assert
        $this->assertTrue($success);
        $penilaian->refresh();
        $this->assertEquals(3, $penilaian->numerator_value);
        $this->assertEquals(5, $penilaian->denominator_value);
        $this->assertTrue($penilaian->is_auto_calculated);
        $this->assertNotNull($penilaian->calculation_metadata);
    }

    /** @test */
    public function it_calculates_for_entire_laporan()
    {
        // Setup: 2 unit kerja, each with 1 penilaian
        $unitKerja1 = UnitKerja::factory()->create();
        $unitKerja2 = UnitKerja::factory()->create();
        $formTemplate = FormTemplate::factory()->create();
        $imutProfile = ImutProfile::factory()->create();
        $imutProfile->formTemplates()->attach($formTemplate->id);

        $laporan = LaporanImut::factory()->create([
            'assessment_period_start' => Carbon::parse('2025-01-01'),
            'assessment_period_end' => Carbon::parse('2025-01-05'),
        ]);

        // Unit Kerja 1
        $laporanUnitKerja1 = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja1->id,
        ]);
        $penilaian1 = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja1->id,
            'imut_profil_id' => $imutProfile->id,
        ]);

        // Unit Kerja 2
        $laporanUnitKerja2 = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unitKerja2->id,
        ]);
        $penilaian2 = ImutPenilaian::factory()->create([
            'laporan_unit_kerja_id' => $laporanUnitKerja2->id,
            'imut_profil_id' => $imutProfile->id,
        ]);

        // Create daily reports for both
        for ($i = 1; $i <= 5; $i++) {
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja1->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse("2025-01-0{$i}"),
                'total_score' => 100,
                'compliance_status' => true,
                'calculation_details' => ['total_score' => 100, 'compliance_status' => true],
            ]);
            DailyReportResponse::create([
                'unit_kerja_id' => $unitKerja2->id,
                'form_template_id' => $formTemplate->id,
                'report_date' => Carbon::parse("2025-01-0{$i}"),
                'total_score' => 100,
                'compliance_status' => true,
                'calculation_details' => ['total_score' => 100, 'compliance_status' => true],
            ]);
        }


        // Act
        $result = $this->service->calculateForLaporan($laporan);

        // Assert
        $this->assertEquals(2, $result['total_penilaians']);
        $this->assertEquals(2, $result['calculated']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);
    }
}
