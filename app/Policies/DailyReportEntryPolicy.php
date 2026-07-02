<?php

namespace App\Policies;

use App\Modules\FormEngine\Models\DailyReportEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyReportEntryPolicy
{
    use HandlesAuthorization;

    // /**
    //  * Determine whether the user can view any models.
    //  */
    // public function viewAny(User $user): bool
    // {
    //     return $user->unitKerjas()->exists();
    // }

    /**
     * Determine whether the user can create models.
     *
     * Similar logic to the Filament resource's `canCreate` method –
     * check for an `indicator` parameter (query or form input) and
     * verify the associated IMUT data is assigned to one of the user’s
     * units.  Global view permission bypasses the restriction.
     */
    public function create(User $user): bool
    {
        if (! $user->unitKerjas()->exists()) {
            return false;
        }

        $indicatorId = request()->query('indicator') ?? request()->input('indicator');
        if (! $indicatorId) {
            return false;
        }

        $template = \App\Models\FormTemplate::with('imutProfile.imutData.unitKerja')
            ->find($indicatorId);

        if (! $template) {
            return false;
        }

        if ($user->can('view_all_data_imut::data')) {
            return true;
        }

        $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        $hasAccess = $template->imutProfile
            && $template->imutProfile->imutData
            && $template->imutProfile->imutData->unitKerja()
            ->whereIn('unit_kerja_id', $userUnitIds)
            ->exists();

        return $hasAccess && $user->can('view_by_unit_kerja_imut::data');
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

    // /**
    //  * Determine whether the user can create models.
    //  */
    // public function create(User $user): bool
    // {
    //     return $user->unitKerjas()->exists();
    // }

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
