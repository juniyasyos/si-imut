<?php

namespace App\Repositories;

use App\Models\ImutData;
use App\Models\User;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ImutDataRepository implements ImutDataRepositoryInterface
{
    protected ImutData $model;

    public function __construct(ImutData $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?ImutData
    {
        return $this->model->find($id);
    }

    public function create(array $data): ImutData
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $imutData = $this->find($id);

        if (!$imutData) {
            return false;
        }

        return $imutData->update($data);
    }

    public function delete(int $id): bool
    {
        $imutData = $this->find($id);

        if (!$imutData) {
            return false;
        }

        return $imutData->delete();
    }

    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return $this->model->whereHas('unitKerja', function ($query) use ($unitKerjaId) {
            $query->where('unit_kerja_id', $unitKerjaId);
        })->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function getByUserAccess(int $userId): Collection
    {
        $user = User::find($userId);

        if (!$user) {
            return collect();
        }

        // Check user permissions
        if ($user->can('view_all_data_imut::data')) {
            return $this->all();
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            return $this->getByUnitKerja($user->unit_kerja_id);
        }

        return collect();
    }

    public function search(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if ($value !== null) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        return $query->get();
    }
}
