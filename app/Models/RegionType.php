<?php

namespace App\Models;

use App\Domains\Imut\Models\ImutBenchmarking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|ImutBenchmarking[] $benchmarkings
 */
class RegionType extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['type'];

    /**
     * The attributes that are hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at'];


    /**
     * Get all benchmarking records that use this region type.
     *
     * @return HasMany
     */
    public function benchmarkings(): HasMany
    {
        return $this->hasMany(ImutBenchmarking::class);
    }
}
