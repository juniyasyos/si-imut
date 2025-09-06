<?php

use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

describe('Database Schema', function () {
    it('has laporan_unit_kerjas table with expected columns', function () {
        expect(Schema::hasTable('laporan_unit_kerjas'))->toBeTrue();

        $columns = Schema::getColumnListing('laporan_unit_kerjas');
        expect($columns)->toContain('id', 'laporan_imut_id', 'unit_kerja_id', 'created_at', 'updated_at');
    });

    it('cascades delete from laporan_imuts to laporan_unit_kerjas', function () {
        $laporan = LaporanImut::factory()->create();
        $unit = UnitKerja::factory()->create();

        $rel = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unit->id,
        ]);

        $this->assertDatabaseHas('laporan_unit_kerjas', ['id' => $rel->id]);

        $laporan->delete();

        $this->assertDatabaseMissing('laporan_unit_kerjas', ['id' => $rel->id]);
    });

    it('cascades delete from unit_kerja to laporan_unit_kerjas', function () {
        $laporan = LaporanImut::factory()->create();
        $unit = UnitKerja::factory()->create();

        $rel = LaporanUnitKerja::factory()->create([
            'laporan_imut_id' => $laporan->id,
            'unit_kerja_id' => $unit->id,
        ]);

        $this->assertDatabaseHas('laporan_unit_kerjas', ['id' => $rel->id]);

        $unit->delete();

        $this->assertDatabaseMissing('laporan_unit_kerjas', ['id' => $rel->id]);
    });
});

