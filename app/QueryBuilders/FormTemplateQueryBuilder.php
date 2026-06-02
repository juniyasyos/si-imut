<?php

namespace App\QueryBuilders;

use App\Models\FormTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query Builder for FormTemplate
 * 
 * Encapsulates all complex query building logic related to form templates.
 * Separates query construction from business logic layer.
 */
class FormTemplateQueryBuilder
{
    private Builder $query;
    private array $unitKerjaIds = [];
    private ?Carbon $validDate = null;
    private bool $monthlyOnly = false;
    private bool $activeOnly = false;

    public function __construct()
    {
        $this->query = FormTemplate::query();
        $this->validDate = now();
    }

    /**
     * Filter templates for given unit kerja IDs using optimized JOINs
     */
    public function forUnitKerjas(array $unitKerjaIds): self
    {
        $this->unitKerjaIds = $unitKerjaIds;
        return $this;
    }

    /**
     * Filter templates valid at a specific date
     */
    public function validAt(Carbon $date): self
    {
        $this->validDate = $date;
        return $this;
    }

    /**
     * Filter to only monthly indicators
     */
    public function monthlyOnly(): self
    {
        $this->monthlyOnly = true;
        return $this;
    }

    /**
     * Filter to only active templates
     */
    public function activeOnly(): self
    {
        $this->activeOnly = true;
        return $this;
    }

    /**
     * Build query for monitoring (combines all optimizations)
     */
    public function buildMonitoringQuery(): Builder
    {
        return $this->applyUnitKerjaFilter()
            ->applyValidityFilter()
            ->applyMonthlyFilter()
            ->applyActiveFilter()
            ->getQuery();
    }

    /**
     * Apply unit kerja filter using optimized JOINs
     */
    private function applyUnitKerjaFilter(): self
    {
        if (empty($this->unitKerjaIds)) {
            return $this;
        }

        $this->query
            ->distinct()
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->join('unit_kerja', 'imut_data_unit_kerja.unit_kerja_id', '=', 'unit_kerja.id')
            ->whereIn('unit_kerja.id', $this->unitKerjaIds)
            ->whereNull('unit_kerja.deleted_at');

        return $this;
    }

    /**
     * Apply validity date filter
     */
    private function applyValidityFilter(): self
    {
        if (!$this->validDate) {
            return $this;
        }

        if (!empty($this->unitKerjaIds)) {
            // When using JOINs, check imutProfile directly
            $this->query
                ->where('imut_profil.valid_from', '<=', $this->validDate)
                ->where(function ($q) {
                    $q->whereNull('imut_profil.valid_until')
                      ->orWhere('imut_profil.valid_until', '>=', $this->validDate);
                })
                ->whereNull('imut_profil.deleted_at');
        } else {
            // When not using JOINs, use whereHas
            $this->query->whereHas('imutProfile', function ($q) {
                $q->where('valid_from', '<=', $this->validDate)
                  ->where(function ($subQ) {
                      $subQ->whereNull('valid_until')
                           ->orWhere('valid_until', '>=', $this->validDate);
                  });
            });
        }

        return $this;
    }

    /**
     * Apply monthly indicator filter
     */
    private function applyMonthlyFilter(): self
    {
        if (!$this->monthlyOnly) {
            return $this;
        }

        if (!empty($this->unitKerjaIds)) {
            // When using JOINs
            $this->query
                ->where('imut_data.status', true)
                ->where('imut_data.is_monthly', true)
                ->whereNull('imut_data.deleted_at');
        } else {
            // When not using JOINs
            $this->query->whereHas('imutProfile.imutData', function ($q) {
                $q->where('status', true)
                  ->where('is_monthly', true);
            });
        }

        return $this;
    }

    /**
     * Apply active filter
     */
    private function applyActiveFilter(): self
    {
        if (!$this->activeOnly) {
            return $this;
        }

        $this->query->where('form_templates.is_active', true);
        return $this;
    }

    /**
     * Get the constructed query
     */
    public function getQuery(): Builder
    {
        // Add SELECT clause if not already present
        if (empty($this->query->getQuery()->columns)) {
            $this->query->select('form_templates.*');
        }

        return $this->query;
    }

    /**
     * Get results as collection
     */
    public function get()
    {
        return $this->buildMonitoringQuery()->get();
    }

    /**
     * Count results
     */
    public function count(): int
    {
        return $this->buildMonitoringQuery()->count();
    }

    /**
     * Reset the builder
     */
    public function reset(): self
    {
        $this->query = FormTemplate::query();
        $this->unitKerjaIds = [];
        $this->validDate = now();
        $this->monthlyOnly = false;
        $this->activeOnly = false;
        return $this;
    }
}
