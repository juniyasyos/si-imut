<?php

namespace App\Repositories;

use App\Models\ImutProfile;
use App\Repositories\Interfaces\ImutProfileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ImutProfileRepository implements ImutProfileRepositoryInterface
{
    protected ImutProfile $model;

    public function __construct(ImutProfile $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?ImutProfile
    {
        return $this->model->find($id);
    }

    public function create(array $data): ImutProfile
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $profile = $this->find($id);

        if (!$profile) {
            return false;
        }

        return $profile->update($data);
    }

    public function delete(int $id): bool
    {
        $profile = $this->find($id);

        if (!$profile) {
            return false;
        }

        return $profile->delete();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model->where('imut_category_id', $categoryId)->get();
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function searchByName(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }
}
