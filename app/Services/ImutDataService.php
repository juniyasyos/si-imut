<?php

namespace App\Services;

use App\Models\ImutData;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ImutDataService
{
    public function __construct(
        private ImutDataRepositoryInterface $repository
    ) {}

    public function getAllImutData(): Collection
    {
        return $this->repository->all();
    }

    public function findImutData(int $id): ?ImutData
    {
        return $this->repository->find($id);
    }

    public function createImutData(array $data): ImutData
    {
        // Business logic: Set created_by jika tidak ada
        if (!isset($data['created_by'])) {
            $data['created_by'] = Auth::id();
        }

        // Business logic: Set status default
        if (!isset($data['status'])) {
            $data['status'] = true;
        }

        return $this->repository->create($data);
    }

    public function updateImutData(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteImutData(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getImutDataWithPagination(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getImutDataByCategory(int $categoryId): Collection
    {
        return $this->repository->getByCategory($categoryId);
    }

    public function getActiveImutData(): Collection
    {
        return $this->repository->getActive();
    }
}
