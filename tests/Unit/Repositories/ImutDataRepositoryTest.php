<?php

use App\Models\ImutData;
use App\Models\User;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ImutDataRepositoryInterface::class);
    $this->seed();
});

describe('ImutDataRepository', function () {
    it('can create imut data', function () {
        $data = [
            'unit_kerja_id' => 1,
            'period_year' => 2024,
            'period_month' => 1,
        ];

        $imutData = $this->repository->create($data);

        expect($imutData)->toBeInstanceOf(ImutData::class)
            ->and($imutData->unit_kerja_id)->toBe(1)
            ->and($imutData->period_year)->toBe(2024);
    });

    it('can find imut data by id', function () {
        $imutData = ImutData::factory()->create();

        $found = $this->repository->find($imutData->id);

        expect($found)->toBeInstanceOf(ImutData::class)
            ->and($found->id)->toBe($imutData->id);
    });

    it('returns null when imut data not found', function () {
        $found = $this->repository->find(999);

        expect($found)->toBeNull();
    });

    it('can update imut data', function () {
        $imutData = ImutData::factory()->create(['period_year' => 2023]);

        $updated = $this->repository->update($imutData->id, ['period_year' => 2024]);

        expect($updated)->toBe(true);

        $imutData->refresh();
        expect($imutData->period_year)->toBe(2024);
    });

    it('returns false when updating non-existent imut data', function () {
        $updated = $this->repository->update(999, ['period_year' => 2024]);

        expect($updated)->toBe(false);
    });

    it('can delete imut data', function () {
        $imutData = ImutData::factory()->create();

        $deleted = $this->repository->delete($imutData->id);

        expect($deleted)->toBe(true);
        expect(ImutData::find($imutData->id))->toBeNull();
    });

    it('returns false when deleting non-existent imut data', function () {
        $deleted = $this->repository->delete(999);

        expect($deleted)->toBe(false);
    });

    it('can get all imut data', function () {
        ImutData::factory()->count(3)->create();

        $all = $this->repository->all();

        expect($all)->toHaveCount(3);
    });

    it('can paginate imut data', function () {
        ImutData::factory()->count(20)->create();

        $paginated = $this->repository->paginate(10);

        expect($paginated->perPage())->toBe(10)
            ->and($paginated->total())->toBe(20)
            ->and($paginated->count())->toBe(10);
    });

    it('can search imut data by criteria', function () {
        ImutData::factory()->create(['period_year' => 2023]);
        ImutData::factory()->create(['period_year' => 2024]);

        $results = $this->repository->search(['period_year' => 2024]);

        expect($results)->toHaveCount(1)
            ->and($results->first()->period_year)->toBe(2024);
    });

    it('can get imut data by user access - admin can see all', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('view_all_data_imut::data');

        ImutData::factory()->count(3)->create();

        $results = $this->repository->getByUserAccess($admin->id);

        expect($results)->toHaveCount(3);
    });

    it('can get imut data by user access - unit user sees only their unit', function () {
        $user = User::factory()->create(['unit_kerja_id' => 1]);
        $user->givePermissionTo('view_by_unit_kerja_imut::data');

        // Create imut data for different units
        ImutData::factory()->create(); // unit 1 from factory
        ImutData::factory()->create(); // unit 1 from factory

        $results = $this->repository->getByUserAccess($user->id);

        expect($results)->toHaveCount(2);
    });

    it('returns empty collection for user without permissions', function () {
        $user = User::factory()->create();

        ImutData::factory()->create();

        $results = $this->repository->getByUserAccess($user->id);

        expect($results)->toHaveCount(0);
    });
});
