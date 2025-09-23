<?php

use App\Observers\ImutDataObserver;
use App\Models\ImutData;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->observer = new ImutDataObserver();
    $this->imutData = Mockery::mock(ImutData::class);
});

afterEach(function () {
    Mockery::close();
});

describe('ImutDataObserver', function () {
    it('clears cache when imut data is created', function () {
        // Mock laporanUnitKerja
        $laporanUnitKerja = Mockery::mock();
        $laporanUnitKerja->laporan_imut_id = 1;
        $laporanUnitKerja->unit_kerja_id = 1;

        $this->imutData
            ->shouldReceive('getAttribute')
            ->with('laporanUnitKerja')
            ->andReturn($laporanUnitKerja);

        // Mock Cache facade
        Cache::shouldReceive('forget')
            ->times(5) // 3 basic + 2 with laporan
            ->andReturn(true);

        $this->observer->created($this->imutData);

        expect(true)->toBe(true); // Test passes if no exceptions
    });

    it('clears cache when imut data is updated', function () {
        $this->imutData
            ->shouldReceive('getAttribute')
            ->with('laporanUnitKerja')
            ->andReturn(null);

        Cache::shouldReceive('forget')
            ->times(3) // Only basic cache
            ->andReturn(true);

        $this->observer->updated($this->imutData);

        expect(true)->toBe(true);
    });

    it('clears cache when imut data is deleted', function () {
        $this->imutData
            ->shouldReceive('getAttribute')
            ->with('laporanUnitKerja')
            ->andReturn(null);

        Cache::shouldReceive('forget')
            ->times(3)
            ->andReturn(true);

        $this->observer->deleted($this->imutData);

        expect(true)->toBe(true);
    });

    it('clears cache when imut data is restored', function () {
        $this->imutData
            ->shouldReceive('getAttribute')
            ->with('laporanUnitKerja')
            ->andReturn(null);

        Cache::shouldReceive('forget')
            ->times(3)
            ->andReturn(true);

        $this->observer->restored($this->imutData);

        expect(true)->toBe(true);
    });
});
