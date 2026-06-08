<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'imut_profile_id',
        'version',
        'is_active',
        'valid_from',
        'valid_until',
        'created_by_user_id',
        'parent_template_id',
        'title',
        'description',
        'compliance_method',
        'auto_fail_on_critical',
        'scoring_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'auto_fail_on_critical' => 'boolean',
        'scoring_config' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                $model->validateSingleActiveTemplate();
            }

            if (empty($model->version)) {
                $model->version = $model->generateNextVersion();
            }
        });
    }

    public function imutProfile(): BelongsTo
    {
        return $this->belongsTo(ImutProfile::class);
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(EnhancedFormField::class)->orderBy('order_index');
    }

    public function fields(): HasMany
    {
        return $this->formFields();
    }

    public function dailyReportResponses(): HasMany
    {
        return $this->hasMany(DailyReportResponse::class);
    }

    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'parent_template_id');
    }

    public function childTemplates(): HasMany
    {
        return $this->hasMany(FormTemplate::class, 'parent_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    // Query Scopes
    public function scopeForUserUnits(Builder $query, User $user): Builder
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        return $query->whereHas('imutProfile.imutData.unitKerja', function ($q) use ($unitKerjaIds) {
            $q->whereIn('unit_kerja.id', $unitKerjaIds);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForProfile(Builder $query, int $profileId): Builder
    {
        return $query->where('imut_profile_id', $profileId);
    }

    public function scopeByVersion(Builder $query, string $version): Builder
    {
        return $query->where('version', $version);
    }

    /**
     * Scope: Filter templates active at current date
     * Use MonitoringTemplateService + FormTemplateRepository for complex monitoring queries
     */
    public function scopeActiveForCurrentDate(Builder $query): Builder
    {
        $now = now();
        return $query->where('is_active', true)
            ->whereHas('imutProfile', function ($q) use ($now) {
                $q->where('valid_from', '<=', $now)
                    ->where(function ($subQ) use ($now) {
                        $subQ->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $now);
                    });
            });
    }

    /**
     * Scope: Filter monthly indicators only
     * Use MonitoringTemplateService + FormTemplateRepository for complex monitoring queries
     */
    public function scopeMonthlyIndicators(Builder $query): Builder
    {
        return $query->whereHas('imutProfile.imutData', function ($q) {
            $q->where('status', true)
                ->where('is_monthly', true);
        });
    }

    /**
     * Scope: Filter templates for given unit kerja IDs
     * Use MonitoringTemplateService + FormTemplateRepository for complex monitoring queries
     */
    public function scopeForUserUnitKerjas(Builder $query, array $unitKerjaIds): Builder
    {
        return $query->whereHas('imutProfile.imutData.unitKerja', function ($q) use ($unitKerjaIds) {
            $q->whereIn('unit_kerja.id', $unitKerjaIds);
        });
    }

    public function calculateCompliance(array $fieldResponses): array
    {
        // Delegate to UnifiedComplianceService to ensure single source of truth
        $service = app(\App\Services\DailyReport\UnifiedComplianceService::class);
        $result = $service->calculate($this, $fieldResponses);

        // Keep backward-compatible shape expected by callers
        return [
            'total_score' => $result['total_score'] ?? ($result['calculation_details']['weighted_percentage'] ?? 0),
            'compliance_status' => $result['compliance_status'] ?? false,
            'critical_failed' => $result['critical_failed'] ?? false,
            'calculation_details' => $result['calculation_details'] ?? [],
        ];
    }

    /**
     * Determine if field contributes to compliance scoring
     * Only select, multi-select, and time fields have scoring values
     * Text fields are for supplementary data only
     */
    private function fieldContributesToScoring(EnhancedFormField $field): bool
    {
        return in_array($field->field_type, [
            'boolean',
            'single_select',
            'multi_select',
            'rating_scale',
            'time_duration',
            'time_range',
            'conditional_trigger',
            'compliance_checker'
        ]) && $field->compliance_weight > 0;
    }

    // Versioning Methods

    /**
     * Validate that only one template can be active per profile
     */
    public function validateSingleActiveTemplate(): void
    {
        if (!$this->is_active || !$this->imut_profile_id) {
            return;
        }

        $existingActive = static::where('imut_profile_id', $this->imut_profile_id)
            ->where('is_active', true)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();

        if ($existingActive) {
            throw new \Exception(
                'Only one form template can be active per profile at a time. ' .
                'Please deactivate the current active template first.'
            );
        }
    }

    /**
     * Generate the next version number for this profile
     */
    public function generateNextVersion(): string
    {
        if (!$this->imut_profile_id) {
            return 'v1.0';
        }

        $lastTemplate = static::where('imut_profile_id', $this->imut_profile_id)
            ->whereRaw('version REGEXP "^v[0-9]+\\.[0-9]+$"') // Only valid version formats
            ->orderByRaw('CAST(SUBSTRING(version, 2, LOCATE(".", version) - 2) AS UNSIGNED) DESC') // Order by major version
            ->orderByRaw('CAST(SUBSTRING(version, LOCATE(".", version) + 1) AS UNSIGNED) DESC') // Then by minor version
            ->first();

        if (!$lastTemplate) {
            return 'v1.0';
        }

        // Extract version number and increment
        if (preg_match('/v(\d+)\.(\d+)/', $lastTemplate->version, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2] + 1;

            return "v{$major}.{$minor}";
        }

        // Fallback if version format is unexpected
        return 'v1.0';
    }

    /**
     * Activate this template version (deactivates others)
     */
    public function activate(): bool
    {
        return \DB::transaction(function () {
            // Deactivate other templates for this profile
            static::where('imut_profile_id', $this->imut_profile_id)
                ->where('id', '!=', $this->id)
                ->update([
                    'is_active' => false,
                    'valid_until' => now()->toDateString()
                ]);

            // Activate this template
            return $this->update([
                'is_active' => true,
                'valid_from' => now()->toDateString(),
                'valid_until' => null
            ]);
        });
    }

    /**
     * Create a new version based on this template
     */
    public function createNewVersion(array $data = []): FormTemplate
    {
        return \DB::transaction(function () use ($data) {
            // Load relationships needed for replication
            $this->load('formFields.options');

            $newTemplate = $this->replicate();
            $newTemplate->parent_template_id = $this->id;
            $newTemplate->version = $this->generateNextVersion();
            $newTemplate->is_active = false;
            $newTemplate->created_by_user_id = auth()->id();
            $newTemplate->valid_from = now()->toDateString();
            $newTemplate->valid_until = null;

            // Override with provided data
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at' && $key !== 'updated_at') {
                    $newTemplate->$key = $value;
                }
            }

            $newTemplate->save();

            // Replicate form fields
            $this->formFields->each(function ($field) use ($newTemplate) {
                $newField = $field->replicate();
                $newField->form_template_id = $newTemplate->id;
                $newField->save();

                // Replicate field options if they exist
                if ($field->relationLoaded('options')) {
                    $field->options->each(function ($option) use ($newField) {
                        $newOption = $option->replicate();
                        $newOption->enhanced_form_field_id = $newField->id;
                        $newOption->save();
                    });
                }
            });

            return $newTemplate;
        });
    }

    /**
     * Check if this template is currently active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this template is valid for a given date
     */
    public function isValidOnDate(\Carbon\Carbon $date): bool
    {
        if ($this->valid_from && $date->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $date->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Get version hierarchy (parent and children)
     */
    public function getVersionHierarchy(): array
    {
        $hierarchy = [];

        // Get root template
        $root = $this;
        while ($root->parent_template_id) {
            $root = $root->parentTemplate;
        }

        // Build hierarchy from root
        $hierarchy[] = $root;
        $this->buildChildHierarchy($root, $hierarchy);

        return $hierarchy;
    }

    /**
     * Recursively build child hierarchy
     */
    private function buildChildHierarchy(FormTemplate $template, array &$hierarchy): void
    {
        $children = $template->childTemplates()->orderBy('created_at')->get();

        foreach ($children as $child) {
            $hierarchy[] = $child;
            $this->buildChildHierarchy($child, $hierarchy);
        }
    }
}
