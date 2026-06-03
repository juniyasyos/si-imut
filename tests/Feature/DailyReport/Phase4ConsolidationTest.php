<?php

namespace Tests\Feature\DailyReport;

use App\Models\DailyReportResponse;
use App\Models\EnhancedFormField;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\DailyReport\DailyReportService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Phase 4 Consolidation Test: Unified Daily Report Service
 * 
 * Tests that:
 * 1. DailyReportService consolidates authorization + creation
 * 2. Template is loaded once (optimization)
 * 3. Authorization checks work correctly
 * 4. Field responses created with pre-calculated scores
 */
class Phase4ConsolidationTest extends TestCase
{
    private DailyReportService $dailyReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dailyReportService = app(DailyReportService::class);
    }

    /**
     * Test successful report creation with authorization
     */
    public function test_create_report_with_authorization_succeeds()
    {
        // Setup
        $user = User::factory()->create();
        $unitKerja = UnitKerja::factory()->create();
        $user->assignUnitKerja($unitKerja);
        
        // Grant permission
        $user->givePermissionTo('view_by_unit_kerja_imut::data');

        $template = FormTemplate::factory()
            ->has(EnhancedFormField::factory()->count(3), 'formFields')
            ->create();

        $template->imutProfile->imutData->unitKerjas()->attach($unitKerja->id);

        // Prepare form data
        $formData = [];
        foreach ($template->formFields as $field) {
            $formData[$field->field_key] = $this->getTestValueForFieldType($field->field_type);
        }

        // Execute
        $report = $this->dailyReportService->createWithAuthorization(
            $user,
            $template->id,
            Carbon::now()->format('Y-m-d'),
            $formData,
            $unitKerja->id
        );

        // Assert
        $this->assertInstanceOf(DailyReportResponse::class, $report);
        $this->assertNotNull($report->id);
        $this->assertEquals($template->id, $report->form_template_id);
        $this->assertEquals($unitKerja->id, $report->unit_kerja_id);
        $this->assertEquals($user->id, $report->submitted_by);
        $this->assertEquals('pending', $report->compliance_status);
    }

    /**
     * Test authorization fails for user without permission
     */
    public function test_create_report_fails_without_permission()
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::factory()->create();
        $user->assignUnitKerja($unitKerja);
        // Don't grant permission

        $template = FormTemplate::factory()
            ->has(EnhancedFormField::factory()->count(1), 'formFields')
            ->create();

        $formData = [];
        foreach ($template->formFields as $field) {
            $formData[$field->field_key] = 'test';
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('izin');

        $this->dailyReportService->createWithAuthorization(
            $user,
            $template->id,
            Carbon::now()->format('Y-m-d'),
            $formData,
            $unitKerja->id
        );
    }

    /**
     * Test authorization fails for user accessing unauthorized unit_kerja
     */
    public function test_create_report_fails_unauthorized_unit_kerja()
    {
        $user = User::factory()->create();
        $userUnitKerja = UnitKerja::factory()->create();
        $unauthorizedUnitKerja = UnitKerja::factory()->create();
        
        $user->assignUnitKerja($userUnitKerja);
        $user->givePermissionTo('view_by_unit_kerja_imut::data');

        $template = FormTemplate::factory()
            ->has(EnhancedFormField::factory()->count(1), 'formFields')
            ->create();
        
        $template->imutProfile->imutData->unitKerjas()->attach($unauthorizedUnitKerja->id);

        $formData = [];
        foreach ($template->formFields as $field) {
            $formData[$field->field_key] = 'test';
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('akses ke unit kerja');

        $this->dailyReportService->createWithAuthorization(
            $user,
            $template->id,
            Carbon::now()->format('Y-m-d'),
            $formData,
            $unauthorizedUnitKerja->id
        );
    }

    /**
     * Test global permission bypasses unit restrictions
     */
    public function test_global_permission_bypasses_unit_restrictions()
    {
        $user = User::factory()->create();
        $otherUnitKerja = UnitKerja::factory()->create();
        
        // User not assigned to other unit kerja
        // But has global permission
        $user->givePermissionTo('view_all_data_imut::data');

        $template = FormTemplate::factory()
            ->has(EnhancedFormField::factory()->count(1), 'formFields')
            ->create();
        
        $template->imutProfile->imutData->unitKerjas()->attach($otherUnitKerja->id);

        $formData = [];
        foreach ($template->formFields as $field) {
            $formData[$field->field_key] = 'test';
        }

        // Should succeed with global permission
        $report = $this->dailyReportService->createWithAuthorization(
            $user,
            $template->id,
            Carbon::now()->format('Y-m-d'),
            $formData,
            $otherUnitKerja->id
        );

        $this->assertNotNull($report->id);
    }

    /**
     * Test field responses created with pre-calculated scores
     */
    public function test_field_responses_have_precalculated_scores()
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::factory()->create();
        $user->assignUnitKerja($unitKerja);
        $user->givePermissionTo('view_by_unit_kerja_imut::data');

        $template = FormTemplate::factory()
            ->has(EnhancedFormField::factory()->state(['field_type' => 'boolean'])->count(1), 'formFields')
            ->has(EnhancedFormField::factory()->state(['field_type' => 'rating_scale'])->count(1), 'formFields')
            ->create();

        $template->imutProfile->imutData->unitKerjas()->attach($unitKerja->id);

        $formData = [];
        foreach ($template->formFields as $field) {
            $formData[$field->field_key] = $field->field_type === 'rating_scale' ? 5 : true;
        }

        $report = $this->dailyReportService->createWithAuthorization(
            $user,
            $template->id,
            Carbon::now()->format('Y-m-d'),
            $formData,
            $unitKerja->id
        );

        // Verify field responses exist and have scores
        $fieldResponses = $report->fieldResponses;
        $this->assertCount($template->formFields->count(), $fieldResponses);

        foreach ($fieldResponses as $response) {
            $this->assertIsNumeric($response->compliance_score);
            $this->assertGreaterThanOrEqual(0, $response->compliance_score);
            $this->assertLessThanOrEqual(100, $response->compliance_score);
        }
    }

    /**
     * Test invalid date format throws exception
     */
    public function test_invalid_date_format_throws_exception()
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::factory()->create();
        $user->assignUnitKerja($unitKerja);
        $user->givePermissionTo('view_by_unit_kerja_imut::data');

        $template = FormTemplate::factory()
            ->has(EnhancedFormField::factory()->count(1), 'formFields')
            ->create();

        $template->imutProfile->imutData->unitKerjas()->attach($unitKerja->id);

        $formData = [];
        foreach ($template->formFields as $field) {
            $formData[$field->field_key] = 'test';
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Format tanggal');

        $this->dailyReportService->createWithAuthorization(
            $user,
            $template->id,
            'invalid-date',  // Invalid format
            $formData,
            $unitKerja->id
        );
    }

    /**
     * Get test value for field type
     */
    private function getTestValueForFieldType(string $fieldType)
    {
        return match ($fieldType) {
            'text', 'textarea', 'email', 'url' => 'Test value',
            'number' => 42,
            'boolean' => true,
            'select', 'radio', 'single_select' => 'option1',
            'multi_select' => ['option1', 'option2'],
            'rating_scale' => 5,
            'time_duration' => 120,
            'time_range' => ['start' => '09:00', 'end' => '17:00'],
            default => null,
        };
    }
}
