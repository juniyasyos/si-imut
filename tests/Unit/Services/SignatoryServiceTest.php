<?php

namespace Tests\Unit\Services;

use App\Models\UnitKerja;
use App\Models\User;
use App\Models\DailyReportResponse;
use App\Services\Support\SignatoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class SignatoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SignatoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SignatoryService();
    }

    /** @test */
    public function it_prefers_unit_pengumpul_when_no_entries()
    {
        $unit = UnitKerja::factory()->create();

        $pengumpul = User::factory()->create();
        $pengumpul->assignRole('pengumpul_data');
        $pengumpul->unitKerjas()->attach($unit->id);

        $validator = User::factory()->create();
        $validator->assignRole('validator_pic');
        $validator->unitKerjas()->attach($unit->id);

        $result = $this->service->pickForUnit($unit);

        $this->assertNotNull($result['pengumpul']);
        $this->assertEquals($pengumpul->id, $result['pengumpul']->id);
        $this->assertNotNull($result['validator']);
        $this->assertEquals($validator->id, $result['validator']->id);
    }

    /** @test */
    public function it_excludes_validator_pic_from_becoming_pengumpul_and_prefers_submitter_pengumpul()
    {
        $unit = UnitKerja::factory()->create();

        $pengumpul = User::factory()->create();
        $pengumpul->assignRole('pengumpul_data');
        $pengumpul->unitKerjas()->attach($unit->id);

        $validatorAsSubmitter = User::factory()->create();
        $validatorAsSubmitter->assignRole('validator_pic');
        $validatorAsSubmitter->unitKerjas()->attach($unit->id);

        // Create entries: validatorAsSubmitter submitted once, pengumpul submitted twice
        DailyReportResponse::create([
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $validatorAsSubmitter->id,
            'report_date' => Carbon::now()->toDateString(),
        ]);

        DailyReportResponse::create([
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $pengumpul->id,
            'report_date' => Carbon::now()->subDay()->toDateString(),
        ]);

        DailyReportResponse::create([
            'unit_kerja_id' => $unit->id,
            'submitted_by' => $pengumpul->id,
            'report_date' => Carbon::now()->subDays(2)->toDateString(),
        ]);

        $entries = DailyReportResponse::where('unit_kerja_id', $unit->id)->get();

        $result = $this->service->pickForUnit($unit, $entries);

        // pengumpul harus tetap user dengan role pengumpul_data (pengumpul), bukan validator_pic
        $this->assertEquals($pengumpul->id, $result['pengumpul']->id);
    }

    /** @test */
    public function it_prefers_validator_pic_when_selecting_validator_from_entries()
    {
        $unit = UnitKerja::factory()->create();

        $validatorPic = User::factory()->create();
        $validatorPic->assignRole('validator_pic');
        $validatorPic->unitKerjas()->attach($unit->id);

        $validator = User::factory()->create();
        $validator->assignRole('validator');
        $validator->unitKerjas()->attach($unit->id);

        // create entries validated by both users (validatorPic validates 2 entries)
        DailyReportResponse::create([
            'unit_kerja_id' => $unit->id,
            'validated_by' => $validatorPic->id,
            'report_date' => Carbon::now()->toDateString(),
        ]);
        DailyReportResponse::create([
            'unit_kerja_id' => $unit->id,
            'validated_by' => $validatorPic->id,
            'report_date' => Carbon::now()->subDay()->toDateString(),
        ]);
        DailyReportResponse::create([
            'unit_kerja_id' => $unit->id,
            'validated_by' => $validator->id,
            'report_date' => Carbon::now()->subDays(2)->toDateString(),
        ]);

        $entries = DailyReportResponse::where('unit_kerja_id', $unit->id)->get();

        $result = $this->service->pickForUnit($unit, $entries);

        $this->assertNotNull($result['validator']);
        $this->assertEquals($validatorPic->id, $result['validator']->id);
    }

    /** @test */
    public function it_prefers_public_ttd_when_s3_missing_or_file_not_found()
    {
        Storage::fake('s3');
        Storage::fake('public');

        $user = User::factory()->create(['ttd_url' => 'ttd/test-public.png']);
        // put file only on public disk
        Storage::disk('public')->put('ttd/test-public.png', 'data');

        $service = new SignatoryService();
        $url = $service->getTtdUrl($user);

        $this->assertEquals('/storage/ttd/test-public.png', $url);
    }

    /** @test */
    public function it_prefers_s3_ttd_when_file_exists_on_s3()
    {
        Storage::fake('s3');
        Storage::fake('public');

        $user = User::factory()->create(['ttd_url' => 'ttd/test-s3.png']);
        Storage::disk('s3')->put('ttd/test-s3.png', 'data');
        // also put on public to ensure s3 is preferred
        Storage::disk('public')->put('ttd/test-s3.png', 'data');

        $service = new SignatoryService();
        $url = $service->getTtdUrl($user);

        $this->assertEquals(Storage::disk('s3')->url('ttd/test-s3.png'), $url);
    }
}
