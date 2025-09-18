<?php

namespace App\Repositories\Interfaces;

use App\Models\LaporanImut;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LaporanImutRepositoryInterface
{
    /**
     * Get all LaporanImut records
     */
    public function all(): Collection;

    /**
     * Find LaporanImut by ID
     */
    public function find(int $id): ?LaporanImut;

    /**
     * Create new LaporanImut
     */
    public function create(array $data): LaporanImut;

    /**
     * Update LaporanImut
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete LaporanImut
     */
    public function delete(int $id): bool;

    /**
     * Get latest active laporan
     */
    public function getLatest(): ?LaporanImut;

    /**
     * Get laporan by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get laporan by period
     */
    public function getByPeriod(string $startDate, string $endDate): Collection;

    /**
     * Get laporan with pagination
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if laporan is active
     */
    public function isActive(int $id): bool;
}
