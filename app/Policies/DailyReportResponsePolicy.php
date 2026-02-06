<?php

namespace App\Policies;

use App\Models\DailyReportResponse;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DailyReportResponsePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->unitKerjas()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DailyReportResponse $dailyReportResponse): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return in_array($dailyReportResponse->unit_kerja_id, $unitKerjaIds);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->unitKerjas()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DailyReportResponse $dailyReportResponse): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return in_array($dailyReportResponse->unit_kerja_id, $unitKerjaIds);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DailyReportResponse $dailyReportResponse): bool
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return in_array($dailyReportResponse->unit_kerja_id, $unitKerjaIds);
    }

    /**
     * Determine whether the user can validate the model.
     */
    public function validate(User $user, DailyReportResponse $dailyReportResponse): bool
    {
        // Only users with validator_pic permission can validate reports
        return $user->can('validate_reports');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DailyReportResponse $dailyReportResponse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DailyReportResponse $dailyReportResponse): bool
    {
        return false;
    }
}
