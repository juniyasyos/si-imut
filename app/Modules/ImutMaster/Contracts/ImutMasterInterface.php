<?php

namespace App\Modules\ImutMaster\Contracts;

interface ImutMasterInterface
{
    /**
     * Get single ImutData by ID
     */
    public function getImutDataById(int $id);

    /**
     * Get active ImutProfile for an indicator
     */
    public function getActiveProfile(int $imutDataId);
}
