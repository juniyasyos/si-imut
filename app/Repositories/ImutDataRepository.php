<?php

namespace App\Repositories;

use App\Models\ImutData;
use App\Models\ImutDataUnitKerja;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Models\User;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ImutDataRepository implements ImutDataRepositoryInterface
{
    public function create(array $data): ImutData
    {
        return ImutData::create($data);
    }

    public function attachUnitKerjas(ImutData $record, array $unitKerjaIds, int $assignedBy): void
    {
        $unitKerjaIds = array_values(array_unique(array_filter($unitKerjaIds)));

        foreach ($unitKerjaIds as $unitKerjaId) {
            ImutDataUnitKerja::firstOrCreate([
                'imut_data_id' => $record->id,
                'unit_kerja_id' => $unitKerjaId,
            ], [
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
            ]);
        }
    }

    public function createWithUnitKerjas(array $data, array $unitKerjaIds, int $assignedBy): ImutData
    {
        return DB::transaction(function () use ($data, $unitKerjaIds, $assignedBy) {
            $record = $this->create($data);
            $this->attachUnitKerjas($record, $unitKerjaIds, $assignedBy);

            return $record;
        });
    }

    public function getTableQueryForUser(User $user): Builder
    {
        if ($user->can('view_all_data_imut::data')) {
            return ImutData::query();
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            $unitKerjaIds = $user->unitKerjas->pluck('id')->toArray();

            return ImutData::query()
                ->whereHas('unitKerja', function ($query) use ($unitKerjaIds) {
                    $query->whereIn('unit_kerja.id', $unitKerjaIds);
                })->orWhere('created_by', $user->id);
        }

        return ImutData::query()->whereRaw('1 = 0');
    }

    public function countForNavigationBadge(User $user): int
    {
        return $this->getTableQueryForUser($user)->count();
    }

    public function findByIdWithRelations(int $id, array $relations = ['profiles', 'categories']): ?ImutData
    {
        return ImutData::with($relations)->find($id);
    }

    public function findBySlugWithRelations(string $slug, array $relations = ['profiles', 'categories']): ?ImutData
    {
        return ImutData::with($relations)->where('slug', $slug)->first();
    }

    public function findUnitKerjaOrFail(int $unitKerjaId): UnitKerja
    {
        return UnitKerja::findOrFail($unitKerjaId);
    }

    public function getLatestCompletedLaporan(): ?LaporanImut
    {
        return LaporanImut::where('status', LaporanImut::STATUS_COMPLETE)
            ->latest('assessment_period_end')
            ->first();
    }

    public function getLatestAnyLaporan(): ?LaporanImut
    {
        return LaporanImut::latest('assessment_period_end')->first();
    }

    public function getAvailableUnitKerjaOptionsForAttach(ImutData $record): array
    {
        $relatedIds = $record->unitKerja()->pluck('id')->toArray();

        return UnitKerja::whereNotIn('id', $relatedIds)
            ->pluck('unit_name', 'id')
            ->toArray();
    }

    public function detachUnitKerjas(ImutData $record, array $unitKerjaIds): void
    {
        $unitKerjaIds = array_values(array_unique(array_filter($unitKerjaIds)));

        if (empty($unitKerjaIds)) {
            return;
        }

        $record->unitKerja()->detach($unitKerjaIds);
    }

    public function getAllUnitKerjaOptions(): array
    {
        return UnitKerja::pluck('unit_name', 'id')->toArray();
    }

    public function getUserUnitKerjaIds(User $user): array
    {
        return $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
    }
}