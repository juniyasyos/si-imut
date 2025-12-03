<?php

namespace App\Policies;

use App\Models\DailyReportEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyReportEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DailyReportEntry $dailyReportEntry): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return $user->hasRole('Unit Kerja')
            && in_array($dailyReportEntry->unit_kerja_id, $unitKerjaIds);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DailyReportEntry $dailyReportEntry): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return $user->hasRole('Unit Kerja')
            && in_array($dailyReportEntry->unit_kerja_id, $unitKerjaIds);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DailyReportEntry $dailyReportEntry): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return $user->hasRole('Unit Kerja')
            && in_array($dailyReportEntry->unit_kerja_id, $unitKerjaIds);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DailyReportEntry $dailyReportEntry): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return $user->hasRole('Unit Kerja')
            && in_array($dailyReportEntry->unit_kerja_id, $unitKerjaIds);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DailyReportEntry $dailyReportEntry): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return $user->hasRole('Unit Kerja')
            && $user->unit_kerja_id === $dailyReportEntry->unit_kerja_id;
    }
}
