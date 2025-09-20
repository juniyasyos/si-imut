<?php

use App\Factories\LaporanImutFactory;
use App\Models\LaporanImut;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->factory = app(LaporanImutFactory::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('LaporanImutFactory', function () {
    it('can create laporan with required attributes', function () {
        $laporan = $this->factory->create([
            'name' => 'Test Laporan',
            'assessment_period_start' => '2024-01-01',
            'assessment_period_end' => '2024-01-31',
        ]);

        expect($laporan)->toBeInstanceOf(LaporanImut::class)
            ->and($laporan->name)->toBe('Test Laporan')
            ->and($laporan->status)->toBe(LaporanImut::STATUS_PROCESS)
            ->and($laporan->created_by)->toBe($this->user->id);
    });

    it('throws exception when required attributes are missing', function () {
        expect(fn() => $this->factory->create([]))
            ->toThrow(InvalidArgumentException::class, 'Laporan name is required');
    });

    it('validates date format', function () {
        $laporan = $this->factory->create([
            'name' => 'Test Laporan',
            'assessment_period_start' => Carbon::now(),
            'assessment_period_end' => Carbon::now()->addMonth(),
        ]);

        expect($laporan->assessment_period_start)->toBeInstanceOf(Carbon::class)
            ->and($laporan->assessment_period_end)->toBeInstanceOf(Carbon::class);
    });

    it('can create monthly laporan', function () {
        $date = Carbon::create(2024, 6, 15);
        Carbon::setTestNow($date);

        $laporan = $this->factory->createMonthlyLaporan($date);

        expect($laporan->name)->toBe('Laporan IMUT Periode 06/2024')
            ->and($laporan->assessment_period_start->format('Y-m-d'))->toBe('2024-06-01')
            ->and($laporan->assessment_period_end->format('Y-m-d'))->toBe('2024-06-30');
    });

    it('can create yearly laporan', function () {
        $laporan = $this->factory->createYearlyLaporan(2024);

        expect($laporan->name)->toBe('Laporan IMUT Tahunan 2024')
            ->and($laporan->assessment_period_start->format('Y-m-d'))->toBe('2024-01-01')
            ->and($laporan->assessment_period_end->format('Y-m-d'))->toBe('2024-12-31');
    });

    it('creates unit kerja relationships automatically', function () {
        // Create some unit kerjas first
        \App\Models\UnitKerja::factory(3)->create();

        $laporan = $this->factory->create([
            'name' => 'Test Laporan',
            'assessment_period_start' => '2024-01-01',
            'assessment_period_end' => '2024-01-31',
        ]);

        $relationshipsCount = \App\Models\LaporanUnitKerja::where('laporan_imut_id', $laporan->id)->count();
        expect($relationshipsCount)->toBe(3);
    });
});
