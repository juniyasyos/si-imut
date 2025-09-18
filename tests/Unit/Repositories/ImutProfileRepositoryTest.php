<?php

use App\Models\ImutProfile;
use App\Repositories\Interfaces\ImutProfileRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ImutProfileRepositoryInterface::class);
    $this->seed();
});

describe('ImutProfileRepository', function () {
    it('can create imut profile', function () {
        $data = [
            'name' => 'Test Profile',
            'imut_category_id' => 1,
            'is_active' => true,
        ];

        $profile = $this->repository->create($data);

        expect($profile)->toBeInstanceOf(ImutProfile::class)
            ->and($profile->name)->toBe('Test Profile')
            ->and($profile->is_active)->toBe(true);
    });

    it('can find profile by id', function () {
        $profile = ImutProfile::factory()->create();

        $found = $this->repository->find($profile->id);

        expect($found)->toBeInstanceOf(ImutProfile::class)
            ->and($found->id)->toBe($profile->id);
    });

    it('can update profile', function () {
        $profile = ImutProfile::factory()->create(['name' => 'Old Name']);

        $updated = $this->repository->update($profile->id, ['name' => 'New Name']);

        expect($updated)->toBe(true);

        $profile->refresh();
        expect($profile->name)->toBe('New Name');
    });

    it('can delete profile', function () {
        $profile = ImutProfile::factory()->create();

        $deleted = $this->repository->delete($profile->id);

        expect($deleted)->toBe(true);
        expect(ImutProfile::find($profile->id))->toBeNull();
    });

    it('can get profiles by category', function () {
        $categoryId = 1;

        ImutProfile::factory()->count(2)->create(['imut_category_id' => $categoryId]);
        ImutProfile::factory()->create(['imut_category_id' => 2]);

        $results = $this->repository->getByCategory($categoryId);

        expect($results)->toHaveCount(2);
        $results->each(function ($profile) use ($categoryId) {
            expect($profile->imut_category_id)->toBe($categoryId);
        });
    });

    it('can get active profiles', function () {
        ImutProfile::factory()->count(2)->create(['is_active' => true]);
        ImutProfile::factory()->create(['is_active' => false]);

        $results = $this->repository->getActive();

        expect($results)->toHaveCount(2);
        $results->each(function ($profile) {
            expect($profile->is_active)->toBe(true);
        });
    });

    it('can search profiles by name', function () {
        ImutProfile::factory()->create(['name' => 'Test Profile One']);
        ImutProfile::factory()->create(['name' => 'Test Profile Two']);
        ImutProfile::factory()->create(['name' => 'Another Profile']);

        $results = $this->repository->searchByName('Test');

        expect($results)->toHaveCount(2);
        $results->each(function ($profile) {
            expect($profile->name)->toContain('Test');
        });
    });

    it('can paginate profiles', function () {
        ImutProfile::factory()->count(20)->create();

        $paginated = $this->repository->paginate(10);

        expect($paginated->perPage())->toBe(10)
            ->and($paginated->total())->toBe(20)
            ->and($paginated->count())->toBe(10);
    });

    it('handles edge cases gracefully', function () {
        // Test with non-existent ID
        expect($this->repository->find(999))->toBeNull();
        expect($this->repository->update(999, ['name' => 'Test']))->toBe(false);
        expect($this->repository->delete(999))->toBe(false);

        // Test with empty search
        $results = $this->repository->searchByName('NonExistent');
        expect($results)->toHaveCount(0);

        // Test with non-existent category
        $results = $this->repository->getByCategory(999);
        expect($results)->toHaveCount(0);
    });
});
