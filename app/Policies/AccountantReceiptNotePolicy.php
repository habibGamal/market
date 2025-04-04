<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AccountantReceiptNote;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountantReceiptNotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_accountant::receipt::note');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccountantReceiptNote $accountantReceiptNote): bool
    {
        return $user->can('view_accountant::receipt::note');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_accountant::receipt::note');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccountantReceiptNote $accountantReceiptNote): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccountantReceiptNote $accountantReceiptNote): bool
    {
        $createdToday = $accountantReceiptNote->created_at->isToday();
        // User cannot delete if the note wasn't created today, regardless of permissions
        if (!$createdToday) {
            return false;
        }

        return $user->can('delete_accountant::receipt::note');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_accountant::receipt::note');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, AccountantReceiptNote $accountantReceiptNote): bool
    {
        return $user->can('force_delete_accountant::receipt::note');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_accountant::receipt::note');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, AccountantReceiptNote $accountantReceiptNote): bool
    {
        return $user->can('{{ Restore }}');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, AccountantReceiptNote $accountantReceiptNote): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('{{ Reorder }}');
    }
}
