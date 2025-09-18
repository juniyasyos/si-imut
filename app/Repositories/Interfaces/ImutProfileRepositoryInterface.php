<?php

namespace App\Repositories\Interfaces;

use App\Models\ImutProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ImutProfileRepositoryInterface
{
    /**
     * Get all ImutProfile records
     */
    public function all(): Collection;

    /**
     * Find ImutProfile by ID
     */
    public function find(int $id): ?ImutProfile;

    /**
     * Create new ImutProfile
     */
    public function create(array $data): ImutProfile;

    /**
     * Update ImutProfile
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete ImutProfile
     */
    public function delete(int $id): bool;

    /**
     * Get profiles by category
     */
    public function getByCategory(int $categoryId): Collection;

    /**
     * Get active profiles
     */
    public function getActive(): Collection;

    /**
     * Get profiles with pagination
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search profiles by name
     */
    public function searchByName(string $name): Collection;
}
