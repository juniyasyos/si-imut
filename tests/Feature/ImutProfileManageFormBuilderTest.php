<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\UnitKerja;
use App\Models\User;
use App\Filament\Resources\ImutProfileResource\Pages\ManageFormBuilder;

class ImutProfileManageFormBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function save_does_not_delete_existing_responses_even_for_super_admin()
    {
        // Arrange: create ImutData + profile + template + field + responses
        $imutData = ImutData::factory()->create(['title' => 'Kepatuhan Kebersihan Tangan']);
        $profile = ImutProfile::factory()->create(['imut_data_id' => $imutData->id, 'valid_from' => now()->subDay()->toDateString()]);

        // Ensure template exists (observer or seeder may create it) — create if missing
        $template = FormTemplate::where('imut_profile_id', $profile->id)->first() ?: FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => 'Test template',
            'description' => 'desc',
            'compliance_method' => 'auto_calculate',
        ]);

        $field = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'keep_me',
            'field_label' => 'Keep Me',
            'field_type' => 'text',
        ]);

        $unit = UnitKerja::factory()->create();
        $user = User::factory()->create();

        $report = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $user->id,
            'report_date' => now()->toDateString(),
        ]);

        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $field->id,
            'field_value' => ['foo'],
        ]);

        $this->assertDatabaseCount('daily_report_responses', 1);
        $this->assertDatabaseCount('field_responses', 1);

        // Act: mount page and call performSave (simulate super admin)
        $page = new ManageFormBuilder();
        $page->mount($profile);

        // Ensure mount detected existing responses
        $this->assertTrue($page->hasExistingResponses);

        // Simulate super-admin permission
        $page->canForceUpdate = true;

        // Call performSave (should NOT delete responses)
        $page->performSave();

        // Assert: responses still exist
        $this->assertDatabaseCount('daily_report_responses', 1);
        $this->assertDatabaseCount('field_responses', 1);
    }

    /** @test */
    public function reset_action_deletes_responses_and_clears_form_fields()
    {
        // Arrange
        $imutData = ImutData::factory()->create(['title' => 'Kepatuhan Kebersihan Tangan']);
        $profile = ImutProfile::factory()->create(['imut_data_id' => $imutData->id, 'valid_from' => now()->subDay()->toDateString()]);

        $template = FormTemplate::where('imut_profile_id', $profile->id)->first() ?: FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => 'Test template',
            'description' => 'desc',
            'compliance_method' => 'auto_calculate',
        ]);

        $initialFieldCount = EnhancedFormField::where('form_template_id', $template->id)->count();

        $f1 = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'a',
            'field_label' => 'A',
            'field_type' => 'text',
        ]);
        $f2 = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'b',
            'field_label' => 'B',
            'field_type' => 'text',
        ]);

        $unit = UnitKerja::factory()->create();
        $user = User::factory()->create();

        $report = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $user->id,
            'report_date' => now()->toDateString(),
        ]);

        FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $f1->id,
            'field_value' => ['x'],
        ]);

        $this->assertEquals($initialFieldCount + 2, EnhancedFormField::where('form_template_id', $template->id)->count());
        $this->assertDatabaseCount('daily_report_responses', 1);
        $this->assertDatabaseCount('field_responses', 1);

        // Act: mount page and call performReset as super admin
        $page = new ManageFormBuilder();
        $page->mount($profile);
        $page->canForceUpdate = true;

        $page->performReset();

        // Assert: responses removed and fields cleared for THIS template
        $this->assertDatabaseCount('daily_report_responses', 0);
        $this->assertDatabaseCount('field_responses', 0);
        $this->assertEquals(0, EnhancedFormField::where('form_template_id', $template->id)->count());
    }
}
