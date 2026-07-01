<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Juniyasyos\IamClient\Models\UnitKerja as IamUnitKerja;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasUniqueWithSoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class UnitKerja
 *
 * @property int $id
 * @property string $unit_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ImutData[] $imutData
 * @property-read Folder|null $folder
 * @property-read \App\Models\LaporanImut|null $laporanImut
 */
class UnitKerja extends IamUnitKerja
{
    use HasFactory, LogsActivity, HasUniqueWithSoftDeletes;



    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Get related users with pivot.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_unit_kerja', 'unit_kerja_id', 'user_id')->withTimestamps();
    }

    /**
     * Get related imut data with pivot.
     */
    public function imutData(): BelongsToMany
    {
        return $this->belongsToMany(ImutData::class, 'imut_data_unit_kerja')
            ->using(ImutDataUnitKerja::class)
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function laporanUnitKerjas(): HasMany
    {
        return $this->hasMany(\App\Models\LaporanUnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Get validation rules for unique fields with soft deletes
     *
     * @param int|null $ignoreId
     * @return array
     */
    public function getUniqueValidationRules(?int $ignoreId = null): array
    {
        return [
            'unit_name' => ['required', 'string', 'max:100', $this->uniqueRule('unit_name', $ignoreId)],
        ];
    }
}
