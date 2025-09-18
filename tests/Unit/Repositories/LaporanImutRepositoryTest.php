<?php

use App\Models\LaporanImut;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(LaporanImutRepositoryInterface::class);
    $this->seed();
});

describe('LaporanImutRepository', function () {
    it('can create laporan imut', function () {
        $data = [
            'name' => 'Test Laporan',
            'assessment_period_start' => '2024-01-01',
            'assessment_period_end' => '2024-01-31',
            'status' => LaporanImut::STATUS_PROCESS,
        ];

        $laporan = $this->repository->create($data);

        expect($laporan)->toBeInstanceOf(LaporanImut::class)
            ->and($laporan->name)->toBe('Test Laporan')
            ->and($laporan->status)->toBe(LaporanImut::STATUS_PROCESS);
    });

    it('can find laporan by id', function () {
        $laporan = LaporanImut::factory()->create();

        $found = $this->repository->find($laporan->id);

        expect($found)->toBeInstanceOf(LaporanImut::class)
            ->and($found->id)->toBe($laporan->id);
    });

    it('can update laporan', function () {
        $laporan = LaporanImut::factory()->create(['name' => 'Old Name']);

        $updated = $this->repository->update($laporan->id, ['name' => 'New Name']);

        expect($updated)->toBe(true);

        $laporan->refresh();
        expect($laporan->name)->toBe('New Name');
    });

    it('can delete laporan', function () {
        $laporan = LaporanImut::factory()->create();

        $deleted = $this->repository->delete($laporan->id);

        expect($deleted)->toBe(true);
        expect(LaporanImut::find($laporan->id))->toBeNull();
    });

    it('can get latest active laporan', function () {
        // Create old active laporan
        LaporanImut::factory()->create([
            'status' => LaporanImut::STATUS_PROCESS,
            'assessment_period_start' => Carbon::yesterday(),
        ]);

        // Create latest active laporan
        $latest = LaporanImut::factory()->create([
            'status' => LaporanImut::STATUS_PROCESS,
            'assessment_period_start' => Carbon::today(),
        ]);

        // Create inactive laporan (should not be returned)
        LaporanImut::factory()->create([
            'status' => LaporanImut::STATUS_COMPLETE,
            'assessment_period_start' => Carbon::today(),
        ]);

        $result = $this->repository->getLatest();

        expect($result)->toBeInstanceOf(LaporanImut::class)
            ->and($result->id)->toBe($latest->id);
    });

    it('returns null when no active laporan exists', function () {
        LaporanImut::factory()->create(['status' => LaporanImut::STATUS_COMPLETE]);

        $result = $this->repository->getLatest();

        expect($result)->toBeNull();
    });

    it('can get laporan by status', function () {
        LaporanImut::factory()->count(2)->create(['status' => LaporanImut::STATUS_PROCESS]);
        LaporanImut::factory()->create(['status' => LaporanImut::STATUS_COMPLETE]);

        $results = $this->repository->getByStatus(LaporanImut::STATUS_PROCESS);

        expect($results)->toHaveCount(2);
        $results->each(function ($laporan) {
            expect($laporan->status)->toBe(LaporanImut::STATUS_PROCESS);
        });
    });

    it('can get laporan by period', function () {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        LaporanImut::factory()->create(['assessment_period_start' => '2024-01-15']);
        LaporanImut::factory()->create(['assessment_period_start' => '2024-01-20']);
        LaporanImut::factory()->create(['assessment_period_start' => '2024-02-01']); // Outside period

        $results = $this->repository->getByPeriod($startDate, $endDate);

        expect($results)->toHaveCount(2);
    });

    it('can check if laporan is active', function () {
        $activeLaporan = LaporanImut::factory()->create(['status' => LaporanImut::STATUS_PROCESS]);
        $inactiveLaporan = LaporanImut::factory()->create(['status' => LaporanImut::STATUS_COMPLETE]);

        expect($this->repository->isActive($activeLaporan->id))->toBe(true);
        expect($this->repository->isActive($inactiveLaporan->id))->toBe(false);
        expect($this->repository->isActive(999))->toBe(false);
    });

    it('can paginate laporan', function () {
        LaporanImut::factory()->count(15)->create();

        $paginated = $this->repository->paginate(10);

        expect($paginated->perPage())->toBe(10)
            ->and($paginated->total())->toBe(15)
            ->and($paginated->count())->toBe(10);
    });
});
