<?php

namespace App\Services\Authorization;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImutDataPermissionService
{
    /**
     * Check if user can edit IMUT data based on ownership and permissions
     */
    public function canEditImutData(?int $recordCreatedBy = null): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Force edit permission bypasses all restrictions
        if ($user->can('force_editable_imut::profile')) {
            return true;
        }

        // User can edit if they created the record
        if ($recordCreatedBy && $recordCreatedBy === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can view unit kerja information
     */
    public function canViewUnitKerjaInfo(): bool
    {
        $user = Auth::user();

        if (!$user || $user->unitKerjas->isEmpty()) {
            return false;
        }

        return $user->can('view_unit::kerja') ||
               !$user->can('attach_imut_data_to_unit_kerja_unit::kerja');
    }

    /**
     * Check if user can manage IMUT categories
     */
    public function canManageImutCategories(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->can('create_imut::category') &&
               $user->can('update_imut::category');
    }

    /**
     * Get filtered IMUT categories based on user permissions
     */
    public function getAvailableImutCategories(): \Illuminate\Database\Eloquent\Collection
    {
        $query = \App\Domains\Imut\Models\ImutCategory::query();

        if (!$this->canManageImutCategories()) {
            $query->where('is_use_global', true);
        }

        return $query->get();
    }

    /**
     * Check if user can create IMUT profiles
     */
    public function canCreateImutProfile(): bool
    {
        $user = Auth::user();

        return $user && $user->can('create_imut::profile');
    }

    /**
     * Check if user can update IMUT profiles
     */
    public function canUpdateImutProfile(): bool
    {
        $user = Auth::user();

        return $user && $user->can('update_imut::profile');
    }

    /**
     * Check if user can delete IMUT profiles
     */
    public function canDeleteImutProfile(): bool
    {
        $user = Auth::user();

        return $user && $user->can('delete_imut::profile');
    }

    /**
     * Check if user can manage IMUT data attachments to unit kerja
     */
    public function canAttachImutDataToUnitKerja(): bool
    {
        $user = Auth::user();

        return $user && $user->can('attach_imut_data_to_unit_kerja_unit::kerja');
    }

    /**
     * Get user's unit kerja information for display
     */
    public function getUserUnitKerjaInfo(): string
    {
        $user = Auth::user();

        if (!$user || $user->unitKerjas->isEmpty()) {
            return 'Tidak ada unit kerja yang terkait dengan akun ini.';
        }

        return $user->unitKerjas->map(function ($unit) {
            $nama = $unit->unit_name;
            $deskripsi = $unit->description ?? '-';
            return "• {$nama} — {$deskripsi}";
        })->implode("\n");
    }

    /**
     * Get permission-based visibility rules for form fields
     */
    public function getFieldVisibilityRules(): array
    {
        return [
            'unit_kerja_info_visible' => $this->canViewUnitKerjaInfo(),
            'can_edit_title' => $this->canUpdateImutProfile(),
            'can_create_profiles' => $this->canCreateImutProfile(),
            'can_manage_categories' => $this->canManageImutCategories(),
            'can_attach_to_unit_kerja' => $this->canAttachImutDataToUnitKerja(),
        ];
    }

    /**
     * Get comprehensive permission check for a specific user and record
     */
    public function getUserPermissions(?User $user = null, ?int $recordCreatedBy = null): array
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return [
                'can_view' => false,
                'can_edit' => false,
                'can_delete' => false,
                'can_create' => false,
                'has_force_edit' => false,
            ];
        }

        return [
            'can_view' => $user->can('view_imut::data'),
            'can_edit' => $this->canEditImutData($recordCreatedBy),
            'can_delete' => $this->canDeleteImutProfile(),
            'can_create' => $this->canCreateImutProfile(),
            'has_force_edit' => $user->can('force_editable_imut::profile'),
            'can_manage_categories' => $this->canManageImutCategories(),
        ];
    }
}
