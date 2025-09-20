<?php

use App\Adapters\Filament\LaporanImutFilamentAdapter;
use App\Models\LaporanImut;
use App\Models\User;
use App\Models\UnitKerja;

describe('LaporanImut Filament Adapter', function () {

    beforeEach(function () {
        $this->adapter = app(LaporanImutFilamentAdapter::class);
        $this->user = User::factory()->create();
    });

    describe('getTableQuery', function () {

        it('returns a query builder', function () {
            $query = $this->adapter->getTableQuery();

            expect($query)->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);
        });

        it('applies filters to query', function () {
            LaporanImut::factory()->create(['status' => 'process']);
            LaporanImut::factory()->create(['status' => 'complete']);

            $filters = [
                ['field' => 'status', 'value' => 'process', 'operator' => '=']
            ];

            $query = $this->adapter->getTableQuery($filters);
            $results = $query->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->status)->toBe('process');
        });

        it('applies sorting to query', function () {
            LaporanImut::factory()->create(['name' => 'B Laporan']);
            LaporanImut::factory()->create(['name' => 'A Laporan']);

            $sorting = ['field' => 'name', 'direction' => 'asc'];

            $query = $this->adapter->getTableQuery([], $sorting);
            $results = $query->get();

            expect($results->first()->name)->toBe('A Laporan');
            expect($results->last()->name)->toBe('B Laporan');
        });
    });

    describe('getFormData', function () {

        it('returns default data for new record', function () {
            $this->actingAs($this->user);

            $data = $this->adapter->getFormData();

            expect($data)->toHaveKey('status');
            expect($data)->toHaveKey('created_by');
            expect($data['status'])->toBe('process');
            expect($data['created_by'])->toBe($this->user->id);
        });

        it('returns existing record data', function () {
            $unitKerja = UnitKerja::factory()->create();
            $laporan = LaporanImut::factory()->create([
                'name' => 'Test Laporan',
                'status' => 'complete'
            ]);
            $laporan->unitKerjas()->attach($unitKerja->id);

            $data = $this->adapter->getFormData($laporan);

            expect($data['name'])->toBe('Test Laporan');
            expect($data['status'])->toBe('complete');
            expect($data['unit_kerja_ids'])->toContain($unitKerja->id);
        });
    });

    describe('createRecord', function () {

        it('creates record with business logic', function () {
            $this->actingAs($this->user);

            $data = [
                'name' => 'New Laporan',
                'status' => 'process',
                'assessment_period_start' => '2024-01-01',
                'assessment_period_end' => '2024-01-31',
            ];

            $record = $this->adapter->createRecord($data);

            expect($record)->toBeInstanceOf(LaporanImut::class);
            expect($record->name)->toBe('New Laporan');
            expect($record->created_by)->toBe($this->user->id);
        });
    });

    describe('updateRecord', function () {

        it('updates record with business logic', function () {
            $laporan = LaporanImut::factory()->create(['name' => 'Original']);

            $data = [
                'name' => 'Updated',
                'status' => 'complete',
                'assessment_period_start' => $laporan->assessment_period_start,
                'assessment_period_end' => $laporan->assessment_period_end,
            ];

            $updated = $this->adapter->updateRecord($laporan, $data);

            expect($updated->name)->toBe('Updated');
            expect($updated->status)->toBe('complete');
        });
    });

    describe('deleteRecord', function () {

        it('deletes record with business logic', function () {
            $laporan = LaporanImut::factory()->create();

            $result = $this->adapter->deleteRecord($laporan);

            expect($result)->toBeTrue();
            expect(LaporanImut::find($laporan->id))->toBeNull();
        });
    });

    describe('getWidgetData', function () {

        it('returns empty array for invalid laporan', function () {
            $data = $this->adapter->getWidgetData(['laporan_id' => 99999]);

            expect($data)->toBe([]);
        });

        it('returns dashboard statistics for valid laporan', function () {
            $laporan = LaporanImut::factory()->create();

            $data = $this->adapter->getWidgetData(['laporan_id' => $laporan->id]);

            expect($data)->toHaveKey('totalIndikator');
            expect($data)->toHaveKey('tercapai');
            expect($data)->toHaveKey('unitMelapor');
            expect($data)->toHaveKey('belumDinilai');
            expect($data)->toHaveKey('achievementRate');
        });
    });
});
