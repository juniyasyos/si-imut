<?php

namespace App\Domains\Imut\Policies;

use App\Domains\Imut\Models\ImutPenilaian;
use App\Models\User;
use App\Support\Auth\Ability;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImutPenilaianPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Ability::resource(Ability::View, 'imut::penilaian', 'any'));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImutPenilaian $imutPenilaian): bool
    {
        return $user->can(Ability::resource(Ability::View, 'imut::penilaian'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_imut::penilaian');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ImutPenilaian $imutPenilaian): bool
    {
        return $user->can(Ability::resource(Ability::Update, 'imut::penilaian'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ImutPenilaian $imutPenilaian): bool
    {
        return $user->can(Ability::resource(Ability::Delete, 'imut::penilaian'));
    }

    protected function userCanAccessPenilaian(User $user, ImutPenilaian $penilaian): bool
    {
        $unitKerjaId = $penilaian->laporanUnitKerja?->unitKerja?->id;

        if (! $unitKerjaId) {
            return false;
        }

        return $user->unitKerjas()->where('unit_kerja.id', $unitKerjaId)->exists();
    }

    /**
     * Hak akses melihat detail penilaian milik unit kerjanya.
     */
    public function viewPenilaian(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can(Ability::resource(Ability::View, 'imut::penilaian', 'imut', 'penilaian'))
            && $this->userCanAccessPenilaian($user, $penilaian);
    }

    /**
     * Hak akses mengedit numerator & denominator untuk penilaian milik unit kerjanya.
     */
    public function updateNumeratorDenominator(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can(Ability::resource(Ability::Update, 'imut::penilaian', 'numerator', 'denominator'))
            && $this->userCanAccessPenilaian($user, $penilaian);
    }

    /**
     * Hak akses memperbarui profil penilaian (misal metadata, indikator, dsb).
     */
    public function updateProfile(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can(Ability::resource(Ability::Update, 'imut::penilaian', 'profile', 'penilaian'))
            && $this->userCanAccessPenilaian($user, $penilaian);
    }

    /**
     * Hak akses membuat rekomendasi dari hasil penilaian.
     */
    public function createRecommendation(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can('create_recommendation_penilaian_imut::penilaian')
            && $this->userCanAccessPenilaian($user, $penilaian);
    }

    /**
     * Hak akses edit paksa
     */
    public function forceEditable(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can('force_editable_imut::penilaian');
    }
}
