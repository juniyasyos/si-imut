<?php

namespace App\Repositories;

use App\Models\LaporanImut;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LaporanImutRepository implements LaporanImutRepositoryInterface
{
    protected LaporanImut $model;

    public function __construct(LaporanImut $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?LaporanImut
    {
        return $this->model->find($id);
    }

    public function create(array $data): LaporanImut
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $laporan = $this->find($id);

        if (!$laporan) {
            return false;
        }

        return $laporan->update($data);
    }

    public function delete(int $id): bool
    {
        $laporan = $this->find($id);

        if (!$laporan) {
            return false;
        }

        return $laporan->delete();
    }

    public function getLatest(): ?LaporanImut
    {
        $today = Carbon::today();

        return $this->model
            ->select(['id', 'assessment_period_start', 'status'])
            ->where('status', LaporanImut::STATUS_PROCESS)
            ->whereDate('assessment_period_start', '<=', $today)
            ->orderBy('assessment_period_start', 'desc')
            ->first();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function getByPeriod(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->whereBetween('assessment_period_start', [$startDate, $endDate])
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function isActive(int $id): bool
    {
        $laporan = $this->find($id);

        if (!$laporan) {
            return false;
        }

        return $laporan->status === LaporanImut::STATUS_PROCESS;
    }
}
