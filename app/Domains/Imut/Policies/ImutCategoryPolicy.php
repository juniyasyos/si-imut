<?php

namespace App\Domains\Imut\Policies;

use App\Domains\Imut\Models\ImutCategory;
use App\Models\User;
use App\Support\Auth\Ability;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImutCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Ability::resource(Ability::View, 'imut::category', 'any'));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can(Ability::resource(Ability::View, 'imut::category'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_imut::category');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can(Ability::resource(Ability::Update, 'imut::category'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can(Ability::resource(Ability::Delete, 'imut::category'));
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can(Ability::resource(Ability::Delete, 'imut::category', 'any'));
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can('force_delete_imut::category');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_imut::category');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can('restore_imut::category');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_imut::category');
    }
}
