<?php

namespace App\Repositories\Interfaces;

use App\Domains\Organization\Models\UnitKerja;

interface UnitKerjaFolderRepositoryInterface
{
    public function createFolder(UnitKerja $unitKerja): void;

    public function updateFolder(UnitKerja $unitKerja): void;

    public function markFolderAsDeleted(UnitKerja $unitKerja): void;

    public function restoreFolder(UnitKerja $unitKerja): void;
}
