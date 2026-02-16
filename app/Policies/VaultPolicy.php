<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Auth\Access\HandlesAuthorization;

class VaultPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_vault');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vault $vault): bool
    {
        return $user->can('view_vault');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_vault');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vault $vault): bool
    {
        return $user->can('update_vault');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vault $vault): bool
    {
        // Cannot delete the default cash vault (id = 1)
        if ($vault->id === 1) {
            return false;
        }

        // Cannot delete vault with balance
        if ($vault->balance > 0) {
            return false;
        }

        return $user->can('delete_vault');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_vault');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Vault $vault): bool
    {
        return $user->can('force_delete_vault');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_vault');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Vault $vault): bool
    {
        return $user->can('restore_vault');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_vault');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Vault $vault): bool
    {
        return $user->can('replicate_vault');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_vault');
    }
}
