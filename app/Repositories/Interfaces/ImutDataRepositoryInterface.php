<?php

namespace App\Repositories\Interfaces;

use App\Models\ImutData;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ImutDataRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?ImutData;

    public function create(array $data): ImutData;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getByCategory(int $categoryId): Collection;

    public function getActive(): Collection;
}
