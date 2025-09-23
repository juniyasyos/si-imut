<?php

namespace App\Repositories;

use App\Models\ImutData;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ImutDataRepository implements ImutDataRepositoryInterface
{
    public function __construct(
        private ImutData $model
    ) {}

    public function all(): Collection
    {
        return $this->model->with('categories')->get();
    }

    public function find(int $id): ?ImutData
    {
        return $this->model->with('categories')->find($id);
    }

    public function create(array $data): ImutData
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $imutData = $this->model->find($id);

        if (!$imutData) {
            return false;
        }

        return $imutData->update($data);
    }

    public function delete(int $id): bool
    {
        $imutData = $this->model->find($id);

        if (!$imutData) {
            return false;
        }

        return $imutData->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('categories')->paginate($perPage);
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model->with('categories')
            ->where('imut_kategori_id', $categoryId)
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->model->with('categories')
            ->where('status', true)
            ->get();
    }
}
