<?php

use App\Commands\LaporanImut\CreateLaporanImutCommand;
use App\Commands\LaporanImut\UpdateLaporanImutCommand;
use App\Commands\LaporanImut\DeleteLaporanImutCommand;
use App\Models\LaporanImut;
use App\Models\User;
use Illuminate\Validation\ValidationException;

describe('LaporanImut Commands', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('CreateLaporanImutCommand', function () {

        it('can create laporan with valid data', function () {
            $data = [
                'name' => 'Test Laporan',
                'status' => 'process',
                'assessment_period_start' => '2024-01-01',
                'assessment_period_end' => '2024-01-31',
                'created_by' => $this->user->id,
            ];

            $laporan = CreateLaporanImutCommand::createWithValidation($data);

            expect($laporan)->toBeInstanceOf(LaporanImut::class);
            expect($laporan->name)->toBe('Test Laporan');
            expect($laporan->status)->toBe('process');
        });

        it('throws validation exception for invalid data', function () {
            $data = [
                'name' => '', // Invalid: empty name
                'status' => 'invalid_status',
                'created_by' => $this->user->id,
            ];

            expect(fn() => CreateLaporanImutCommand::createWithValidation($data))
                ->toThrow(ValidationException::class);
        });

        it('validates unique name constraint', function () {
            LaporanImut::factory()->create(['name' => 'Existing Laporan']);

            $data = [
                'name' => 'Existing Laporan', // Duplicate name
                'status' => 'process',
                'assessment_period_start' => '2024-01-01',
                'assessment_period_end' => '2024-01-31',
                'created_by' => $this->user->id,
            ];

            expect(fn() => CreateLaporanImutCommand::createWithValidation($data))
                ->toThrow(ValidationException::class);
        });

        it('validates assessment period order', function () {
            $data = [
                'name' => 'Test Laporan',
                'status' => 'process',
                'assessment_period_start' => '2024-01-31', // After end date
                'assessment_period_end' => '2024-01-01',
                'created_by' => $this->user->id,
            ];

            expect(fn() => CreateLaporanImutCommand::createWithValidation($data))
                ->toThrow(ValidationException::class);
        });
    });

    describe('UpdateLaporanImutCommand', function () {

        it('can update laporan with valid data', function () {
            $laporan = LaporanImut::factory()->create([
                'name' => 'Original Name',
                'status' => 'process'
            ]);

            $data = [
                'name' => 'Updated Name',
                'status' => 'complete',
                'assessment_period_start' => $laporan->assessment_period_start,
                'assessment_period_end' => $laporan->assessment_period_end,
            ];

            $updatedLaporan = UpdateLaporanImutCommand::updateWithValidation($laporan->id, $data);

            expect($updatedLaporan->name)->toBe('Updated Name');
            expect($updatedLaporan->status)->toBe('complete');
        });

        it('allows same name for same record', function () {
            $laporan = LaporanImut::factory()->create(['name' => 'Test Name']);

            $data = [
                'name' => 'Test Name', // Same name, should be allowed
                'status' => 'complete',
                'assessment_period_start' => $laporan->assessment_period_start,
                'assessment_period_end' => $laporan->assessment_period_end,
            ];

            $updatedLaporan = UpdateLaporanImutCommand::updateWithValidation($laporan->id, $data);

            expect($updatedLaporan->name)->toBe('Test Name');
        });
    });

    describe('DeleteLaporanImutCommand', function () {

        it('can delete laporan', function () {
            $laporan = LaporanImut::factory()->create();

            $result = DeleteLaporanImutCommand::deleteById($laporan->id);

            expect($result)->toBeTrue();
            expect(LaporanImut::find($laporan->id))->toBeNull();
        });

        it('can force delete laporan', function () {
            $laporan = LaporanImut::factory()->create();
            $laporan->delete(); // Soft delete first

            $result = DeleteLaporanImutCommand::deleteById($laporan->id, true);

            expect($result)->toBeTrue();
            expect(LaporanImut::withTrashed()->find($laporan->id))->toBeNull();
        });

        it('throws exception for non-existent laporan', function () {
            expect(fn() => DeleteLaporanImutCommand::deleteById(99999))
                ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        });
    });
});
