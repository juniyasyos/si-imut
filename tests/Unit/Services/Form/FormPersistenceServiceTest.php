<?php

namespace Tests\Unit\Services\Form;

use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\FormTemplate;
use App\Models\ImutProfile;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\FormBuilder\FormPersistenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class FormPersistenceServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function updating_template_preserves_existing_field_responses()
    {
        $profile = ImutProfile::factory()->create();

        // create template + field (observer may have auto-created a template for the profile)
        $template = FormTemplate::where('imut_profile_id', $profile->id)->first() ?: FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => 'T1',
            'description' => 'desc',
            'compliance_method' => 'auto_calculate',
        ]);

        $field = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'f_a',
            'field_label' => 'Field A',
            'field_type' => 'single_select',
            'order_index' => 1,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $field->id,
            'option_text' => 'Yes',
            'option_value' => 'yes',
            'is_correct' => true,
            'compliance_value' => 100,
            'order_index' => 1,
        ]);

        // create a DailyReportResponse + FieldResponse (historical response)
        $unit = UnitKerja::factory()->create();
        $user = User::factory()->create();

        $response = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $user->id,
            'report_date' => Carbon::now()->toDateString(),
        ]);

        $fieldResponse = FieldResponse::create([
            'daily_report_response_id' => $response->id,
            'form_field_id' => $field->id,
            'field_value' => 'yes',
            'compliance_score' => 100,
        ]);

        // Prepare new form payload that changes the field label (same field_key) and adds a new field
        $payload = [
            'title' => 'T1-updated',
            'description' => 'updated',
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => false,
            'fields' => [
                [
                    'field_key' => 'f_a',
                    'field_label' => 'Field A (updated)',
                    'field_type' => 'single_select',
                    'options' => [
                        ['value' => 'yes', 'label' => 'Yes', 'is_correct' => true],
                        ['value' => 'no', 'label' => 'No', 'is_correct' => false],
                    ],
                    'compliance_weight' => 5,
                ],
                [
                    'field_key' => 'f_new',
                    'field_label' => 'New Field',
                    'field_type' => 'text',
                ],
            ],
        ];

        $service = new FormPersistenceService();
        $service->saveFormData($profile, $payload);

        // existing FieldResponse must still exist and still refer to the same field id
        $this->assertDatabaseHas('field_responses', [
            'id' => $fieldResponse->id,
            'form_field_id' => $field->id,
        ]);

        // the existing EnhancedFormField should have been updated (label changed)
        $this->assertDatabaseHas('enhanced_form_fields', [
            'id' => $field->id,
            'field_label' => 'Field A (updated)',
        ]);

        // new field created
        $this->assertDatabaseHas('enhanced_form_fields', [
            'field_key' => 'f_new',
            'field_label' => 'New Field',
        ]);

        // option 'no' should have been created and existing option 'yes' preserved
        $this->assertDatabaseHas('form_field_options', [
            'enhanced_form_field_id' => $field->id,
            'option_value' => 'no',
        ]);
        $this->assertDatabaseHas('form_field_options', [
            'enhanced_form_field_id' => $field->id,
            'option_value' => 'yes',
        ]);
    }

    /** @test */
    public function removed_field_without_responses_is_deleted_but_field_with_responses_is_preserved()
    {
        $profile = ImutProfile::factory()->create();

        $template = FormTemplate::where('imut_profile_id', $profile->id)->first() ?: FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => 'T2',
            'description' => 'desc',
            'compliance_method' => 'auto_calculate',
        ]);

        // field with responses
        $fieldHasResponses = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'keep_me',
            'field_label' => 'Keep Me',
            'field_type' => 'text',
        ]);

        // field without responses
        $fieldNoResponses = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'delete_me',
            'field_label' => 'Delete Me',
            'field_type' => 'text',
        ]);

        $unit = UnitKerja::factory()->create();
        $user = User::factory()->create();

        $response = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $user->id,
            'report_date' => Carbon::now()->toDateString(),
        ]);

        FieldResponse::create([
            'daily_report_response_id' => $response->id,
            'form_field_id' => $fieldHasResponses->id,
            'field_value' => 'some text',
        ]);

        // Incoming payload removes 'delete_me' field entirely
        $payload = [
            'title' => 'T2-updated',
            'description' => 'desc',
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => false,
            'fields' => [
                [
                    'field_key' => 'keep_me',
                    'field_label' => 'Keep Me (still)',
                    'field_type' => 'text',
                ],
            ],
        ];

        $service = new FormPersistenceService();
        $service->saveFormData($profile, $payload);

        // field that had responses must still exist
        $this->assertDatabaseHas('enhanced_form_fields', [
            'id' => $fieldHasResponses->id,
            'field_key' => 'keep_me',
        ]);

        // field without responses should be deleted from DB
        $this->assertDatabaseMissing('enhanced_form_fields', [
            'field_key' => 'delete_me',
        ]);
    }
}
