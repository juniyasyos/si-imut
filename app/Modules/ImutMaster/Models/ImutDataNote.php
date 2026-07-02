<?php

namespace App\Modules\ImutMaster\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\LaporanImut;
use App\Models\User;

class ImutDataNote extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'imut_data_notes'; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imut_data_id',
        'note_name',
        'period_year',
        'period_quarter',
        'period_semester',
        'period_type',
        'recommendation',
        'analysis',
        'additional_notes',
        'priority',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that are guarded.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
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
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relasi ke ImutData
     */
    public function imutData(): BelongsTo
    {
        return $this->belongsTo(ImutData::class, 'imut_data_id');
    }

    /**
     * Relasi ke User (creator)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke LaporanImut (many-to-many)
     */
    public function laporanImuts(): BelongsToMany
    {
        return $this->belongsToMany(
            LaporanImut::class,
            'imut_data_note_laporan_imut',
            'imut_data_note_id',
            'laporan_imut_id'
        );
    }

    /**
     * Get laporan names from the related laporanImuts relationship.
     */
    public function getLaporanNamesAttribute(): string
    {
        $laporans = $this->laporanImuts->pluck('name')->toArray();

        if (empty($laporans)) {
            return '-';
        }

        return implode(', ', $laporans);
    }

    /**
     * Get formatted period display
     */
    public function getPeriodDisplayAttribute(): string
    {
        if (!$this->period_year) {
            return '-';
        }

        if ($this->period_type === 'tahunan') {
            return "Tahunan {$this->period_year}";
        }

        if ($this->period_type === 'semester') {
            $semesterNames = [
                'S1' => 'Semester I (Jan-Jun)',
                'S2' => 'Semester II (Jul-Des)',
            ];
            $semesterName = $semesterNames[$this->period_semester] ?? $this->period_semester;
            return "{$semesterName} {$this->period_year}";
        }

        // Triwulan
        $quarterNames = [
            'Q1' => 'Triwulan I (Jan-Mar)',
            'Q2' => 'Triwulan II (Apr-Jun)',
            'Q3' => 'Triwulan III (Jul-Sep)',
            'Q4' => 'Triwulan IV (Oct-Des)',
        ];

        $quarterName = $quarterNames[$this->period_quarter] ?? $this->period_quarter;
        return "{$quarterName} {$this->period_year}";
    }

    /**
     * Scope untuk filter berdasarkan IMUT Data
     */
    public function scopeForImutData($query, int $imutDataId)
    {
        return $query->where('imut_data_id', $imutDataId);
    }

    /**
     * Scope untuk filter hanya note aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('period_year', $year);
    }

    /**
     * Scope untuk filter berdasarkan triwulan
     */
    public function scopeByQuarter($query, string $quarter)
    {
        return $query->where('period_quarter', $quarter);
    }

    /**
     * Scope untuk filter berdasarkan semester
     */
    public function scopeBySemester($query, string $semester)
    {
        return $query->where('period_semester', $semester);
    }

    /**
     * Scope untuk filter berdasarkan tipe periode
     */
    public function scopeByPeriodType($query, string $type)
    {
        return $query->where('period_type', $type);
    }
}
