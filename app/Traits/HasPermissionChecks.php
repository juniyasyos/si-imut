<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasPermissionChecks
{
    /**
     * Check if the current user can edit a record.
     * User can edit if:
     * - Record is new (null)
     * - User created the record
     * - User has the force edit permission
     *
     * @param  Model|null  $record
     * @param  string  $permission
     * @return bool
     */
    protected function canEditByCreator(?Model $record, string $permission = 'force_editable_imut::profile'): bool
    {
        if (! $record) {
            return true;
        }

        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Check if user is the creator
        if (property_exists($record, 'created_by') && $record->created_by === $user->id) {
            return true;
        }

        // Check if user has force edit permission
        return $user->can($permission);
    }

    /**
     * Determine if a field should be disabled for the current user.
     * This is the inverse of canEditByCreator.
     *
     * @param  Model|null  $record
     * @param  string  $permission
     * @return bool
     */
    protected function shouldDisableForCreator(?Model $record, string $permission = 'force_editable_imut::profile'): bool
    {
        return ! $this->canEditByCreator($record, $permission);
    }

    /**
     * Check if the current user has a specific permission.
     *
     * @param  string  $permission
     * @return bool
     */
    protected static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    /**
     * Check if the current user does NOT have a specific permission.
     *
     * @param  string  $permission
     * @return bool
     */
    protected static function userCannot(string $permission): bool
    {
        return ! static::userCan($permission);
    }

    /**
     * Check if the current user has force edit permission for IMUT penilaian.
     *
     * @return bool
     */
    protected static function hasForceEdit(): bool
    {
        return static::userCan('force_editable_imut::penilaian');
    }

    /**
     * Check if user can access penilaian based on unit kerja.
     * Used in policies for access control.
     *
     * @param  Model  $penilaian
     * @return bool
     */
    protected function userCanAccessPenilaian(Model $penilaian): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Admin or super admin can access everything
        if ($user->hasRole(['super_admin', 'panel_user'])) {
            return true;
        }

        // Check if user's unit kerja matches penilaian's unit kerja
        if (! $user->unitKerjas || $user->unitKerjas->isEmpty()) {
            return false;
        }

        $userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
        $penilaianUnitKerjaId = $penilaian->unitKerja?->id ?? $penilaian->unit_kerja_id;

        return in_array($penilaianUnitKerjaId, $userUnitKerjaIds);
    }
}
