<?php

namespace App\Repositories\Interfaces;

use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface ImutDataRepositoryInterface
{
    public function create(array $data): ImutData;

    public function attachUnitKerjas(ImutData $record, array $unitKerjaIds, int $assignedBy): void;

    public function createWithUnitKerjas(array $data, array $unitKerjaIds, int $assignedBy): ImutData;

    public function getTableQueryForUser(User $user): Builder;

    public function countForNavigationBadge(User $user): int;

    public function findByIdWithRelations(int $id, array $relations = ['profiles', 'categories']): ?ImutData;

    public function findBySlugWithRelations(string $slug, array $relations = ['profiles', 'categories']): ?ImutData;

    public function findUnitKerjaOrFail(int $unitKerjaId): UnitKerja;

    public function getLatestCompletedLaporan(): ?LaporanImut;

    public function getLatestAnyLaporan(): ?LaporanImut;
}