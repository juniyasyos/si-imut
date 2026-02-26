<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormTemplate;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows user whose unit kerjanya has the imut data to open create page', function () {
    $unit = UnitKerja::factory()->create();
    $user = User::factory()->create();
    $user->unitKerjas()->attach($unit->id);

    $imutData = ImutData::factory()->create();
    $imutData->unitKerja()->attach($unit->id);

    $profile = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
    $template = FormTemplate::factory()->create(['imut_profile_id' => $profile->id]);

    $this->actingAs($user);

    $url = DailyReportEntryResource::getUrl('create') . '?indicator=' . $template->id;
    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('Laporan Harian');
});

it('denies access when the user unit has no relation to the imut data', function () {
    $unit1 = UnitKerja::factory()->create();
    $unit2 = UnitKerja::factory()->create();
    $user = User::factory()->create();
    $user->unitKerjas()->attach($unit1->id);

    $imutData = ImutData::factory()->create();
    $imutData->unitKerja()->attach($unit2->id);

    $profile = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
    $template = FormTemplate::factory()->create(['imut_profile_id' => $profile->id]);

    $this->actingAs($user);
    $url = DailyReportEntryResource::getUrl('create') . '?indicator=' . $template->id;

    $response = $this->get($url);

    $response->assertStatus(403);
});

it('allows creating a report via Livewire when indicator query present', function () {
    $unit = UnitKerja::factory()->create();
    $user = User::factory()->create();
    $user->unitKerjas()->attach($unit->id);

    $imutData = ImutData::factory()->create();
    $imutData->unitKerja()->attach($unit->id);

    $profile = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
    $template = FormTemplate::factory()->create(['imut_profile_id' => $profile->id]);

    $this->actingAs($user);

    Livewire::test(\App\Filament\Resources\DailyReportEntryResource\Pages\CreateDailyReportEntry::class, [
        'indicator' => $template->id,
        'date' => now()->format('Y-m-d'),
    ])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('daily_report_responses', [
        'form_template_id' => $template->id,
    ]);
});
