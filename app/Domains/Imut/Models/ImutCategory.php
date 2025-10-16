<?php

namespace App\Domains\Imut\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ImutCategoryFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasUniqueWithSoftDeletes;

class ImutCategory extends Model
{
    use SoftDeletes, LogsActivity, HasFactory, HasUniqueWithSoftDeletes;

    /**
     * Table terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'imut_kategori';

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_name',
        'scope',
        'short_name',
        'description',
        'is_use_global',
        'is_benchmark_category',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke model ImutData.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imutData(): HasMany
    {
        return $this->hasMany(ImutData::class, 'imut_kategori_id');
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
            'category_name' => ['required', 'string', 'max:100', $this->uniqueRule('category_name', $ignoreId)],
        ];
    }

    /**
     * Mendapatkan pengaturan log aktivitas untuk model ini.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    protected static function newFactory(): ImutCategoryFactory
    {
        return ImutCategoryFactory::new();
    }
}
