<?php

namespace Tests\Feature\Services\Form;

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
use App\Services\FormBuilder\FormPersistenceService;

class HandwashingTemplateEditPreservesResponsesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function editing_handwashing_template_keeps_historical_field_responses()
    {
        // Buat ImutData yang judulnya cocok dengan konfigurasi JSON sehingga observer akan membuat template
        $imutData = ImutData::factory()->create(["title" => 'Kepatuhan Kebersihan Tangan']);

        // Buat profil yang aktif sekarang (observer akan auto-create FormTemplate dari JSON)
        $profile = ImutProfile::factory()->create([
            'imut_data_id' => $imutData->id,
            'valid_from' => now()->subDay()->toDateString(),
        ]);

        // Pastikan FormTemplate auto-terbuat
        $template = FormTemplate::where('imut_profile_id', $profile->id)->first();
        $this->assertNotNull($template, 'FormTemplate handwashing harus dibuat oleh observer');

        // Ambil field hand_hygiene_indication dari template JSON
        $field = EnhancedFormField::where('form_template_id', $template->id)
            ->where('field_key', 'hand_hygiene_indication')
            ->first();
        $this->assertNotNull($field, 'Field hand_hygiene_indication harus ada di template dari JSON');

        // Buat DailyReportResponse + FieldResponse (historical)
        $unit = UnitKerja::factory()->create(['unit_name' => 'IGD']);
        $user = User::factory()->create();

        $report = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $user->id,
            'report_date' => now()->toDateString(),
            'auto_calculated' => true,
        ]);

        $fr = FieldResponse::create([
            'daily_report_response_id' => $report->id,
            'form_field_id' => $field->id,
            'field_value' => ['sebelum_kontak_pasien'],
        ]);

        $beforeCount = FieldResponse::where('form_field_id', $field->id)->count();
        $this->assertEquals(1, $beforeCount);

        // Siapkan payload edit yang TIDAK MENG-include 'hand_hygiene_indication' (seharusnya tidak di-delete)
        $payload = [
            'title' => $template->title,
            'description' => $template->description,
            'compliance_method' => $template->compliance_method,
            'auto_fail_on_critical' => $template->auto_fail_on_critical,
            'fields' => [
                [
                    'field_key' => 'hand_hygiene_method',
                    'field_label' => 'Metode Kebersihan Tangan (diedit)',
                    'field_type' => 'single_select',
                    'options' => [
                        ['value' => 'hand_rub', 'label' => 'Hand Rub', 'is_correct' => true],
                        ['value' => 'air_sabun', 'label' => 'Air + Sabun', 'is_correct' => true],
                        ['value' => 'tidak_cuci_tangan', 'label' => 'Tidak cuci tangan', 'is_correct' => false],
                    ],
                ],
                [
                    'field_key' => 'six_steps_compliance',
                    'field_label' => '6 Langkah (tetap)',
                    'field_type' => 'multi_select',
                ],
                [
                    'field_key' => 'observer_notes_new',
                    'field_label' => 'Catatan Pengamat (baru)',
                    'field_type' => 'short_text',
                ],
            ],
        ];

        // Jalankan update melalui service (seperti UI)
        app(FormPersistenceService::class)->saveFormData($profile, $payload);

        // Field yang punya historical responses harus tetap ada
        $fieldAfter = EnhancedFormField::where('form_template_id', $template->id)
            ->where('field_key', 'hand_hygiene_indication')
            ->first();

        $this->assertNotNull($fieldAfter, 'Field dengan historical responses tidak boleh dihapus');

        $afterCount = FieldResponse::where('form_field_id', $fieldAfter->id)->count();
        $this->assertEquals($beforeCount, $afterCount, 'Jumlah FieldResponse historis harus tetap sama');

        // Field baru harus dibuat
        $this->assertDatabaseHas('enhanced_form_fields', ['field_key' => 'observer_notes_new']);
    }
}
