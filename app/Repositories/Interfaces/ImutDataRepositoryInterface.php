<?php

namespace App\Repositories\Interfaces;

use App\Models\ImutData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ImutDataRepositoryInterface
{
    /**
     * Get all ImutData records
     */
    public function all(): Collection;

    /**
     * Find ImutData by ID
     */
    public function find(int $id): ?ImutData;

    /**
     * Create new ImutData
     */
    public function create(array $data): ImutData;

    /**
     * Update ImutData
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete ImutData
     */
    public function delete(int $id): bool;

    /**
     * Get ImutData by Unit Kerja
     */
    public function getByUnitKerja(int $unitKerjaId): Collection;

    /**
     * Get ImutData with pagination
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get ImutData by user access level
     */
    public function getByUserAccess(int $userId): Collection;

    /**
     * Search ImutData by criteria
     */
    public function search(array $criteria): Collection;
}
