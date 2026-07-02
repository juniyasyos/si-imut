<?php

namespace App\Modules\ImutMaster\Services;

use App\Modules\ImutMaster\Contracts\ImutMasterInterface;
use App\Models\ImutData;
use App\Models\ImutProfile;

class ImutMasterService implements ImutMasterInterface
{
    public function getImutDataById(int $id)
    {
        return ImutData::find($id);
    }

    public function getActiveProfile(int $imutDataId)
    {
        return ImutProfile::where('imut_data_id', $imutDataId)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->first();
    }
}
