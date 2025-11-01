<?php

namespace App\Models;

use App\Models\ImutBenchmarking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $type
 * @property string|null $display_color
 * @property string $chart_type
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
    protected $fillable = ['type', 'display_color', 'chart_type'];

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

    /**
     * Get default region name for this region type.
     * Returns null if no default (user must input manually).
     *
     * @return string|null
     */
    public function getDefaultRegionName(): ?string
    {
        $type = strtolower(trim($this->type));

        // Remove emoji and extra spaces
        $type = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $type);
        $type = trim($type);

        return match (true) {
            str_contains($type, 'nasional') || str_contains($type, 'national') => 'Indonesia',
            str_contains($type, 'provinsi') || str_contains($type, 'province') => 'Jawa Timur',
            default => null,
        };
    }

    /**
     * Check if this region type has a default region name.
     *
     * @return bool
     */
    public function hasDefaultRegionName(): bool
    {
        return $this->getDefaultRegionName() !== null;
    }

    /**
     * Get display color with fallback to default based on type name.
     *
     * @return string
     */
    public function getDisplayColorWithFallback(): string
    {
        // Jika ada display_color, gunakan itu
        if ($this->display_color) {
            return $this->display_color;
        }

        // Fallback ke default color berdasarkan nama type
        $type = strtolower(trim($this->type));
        $type = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $type);
        $type = trim($type);

        return match (true) {
            str_contains($type, 'nasional') || str_contains($type, 'national') => '#10b981', // Green
            str_contains($type, 'provinsi') || str_contains($type, 'province') => '#8b5cf6', // Purple
            str_contains($type, 'rumah sakit') || str_contains($type, 'hospital') => '#ef4444', // Red
            default => '#3b82f6', // Default Blue
        };
    }

    /**
     * Get chart type with fallback to column.
     *
     * @return string
     */
    public function getChartTypeWithFallback(): string
    {
        return $this->chart_type ?? 'column';
    }

    /**
     * Get available chart types for dropdown.
     *
     * @return array
     */
    public static function getChartTypes(): array
    {
        return [
            'line' => 'Line (Garis)',
            'column' => 'Column (Batang)',
        ];
    }
}
