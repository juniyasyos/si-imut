<?php

use App\Domains\Imut\Models\ImutCategory;
use App\Domains\Imut\Models\ImutData;
use App\Domains\Imut\Models\ImutProfile;
use App\Domains\Organization\Models\UnitKerja;
use App\Domains\Reporting\Models\LaporanImut;
use App\Domains\Reporting\Models\LaporanUnitKerja;
use App\Domains\Imut\Models\ImutPenilaian;
use App\Services\Chart\UnitKerjaChartDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps chart percentage calculation equivalent after refactor', function () {
    $unitKerja = UnitKerja::factory()->create();
    $category = ImutCategory::factory()->create(['short_name' => 'Kategori A']);

    $imutData = ImutData::factory()->create([
        'imut_kategori_id' => $category->id,
        'status' => true,
    ]);

    $profile = ImutProfile::factory()->create([
        'imut_data_id' => $imutData->id,
    ]);

    $laporan = LaporanImut::factory()->create([
        'status' => LaporanImut::STATUS_PROCESS,
    ]);

    $laporanUnitKerja = LaporanUnitKerja::factory()->create([
        'laporan_imut_id' => $laporan->id,
        'unit_kerja_id' => $unitKerja->id,
    ]);

    ImutPenilaian::factory()
        ->for($profile, 'profile')
        ->for($laporanUnitKerja, 'laporanUnitKerja')
        ->create([
            'numerator_value' => 30,
            'denominator_value' => 40,
        ]);

    $laporans = LaporanImut::with([
        'laporanUnitKerjas.imutPenilaians.profile.imutData.imutCategory',
    ])->whereKey($laporan->id)->get();

    $service = app(UnitKerjaChartDataService::class);

    $series = $service->buildUnitKerjaChartSeries($laporans, [], collect([$category]));

    expect($series)->toHaveCount(1)
        ->and($series[0]['name'])->toBe('Kategori A')
        ->and($series[0]['data'])->toBe([75.0]);
});
