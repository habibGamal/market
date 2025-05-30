<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AccountantIssueNote;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountantIssueNotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_accountant::issue::note');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccountantIssueNote $accountantIssueNote): bool
    {
        return $user->can('view_accountant::issue::note');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_accountant::issue::note');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccountantIssueNote $accountantIssueNote): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccountantIssueNote $accountantIssueNote): bool
    {
        $createdToday = $accountantIssueNote->created_at->isToday();
        // User cannot delete if the note wasn't created today, regardless of permissions
        if (!$createdToday) {
            return false;
        }

        return $user->can('delete_accountant::issue::note');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_accountant::issue::note');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, AccountantIssueNote $accountantIssueNote): bool
    {
        return $user->can('force_delete_accountant::issue::note');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_accountant::issue::note');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, AccountantIssueNote $accountantIssueNote): bool
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
    public function replicate(User $user, AccountantIssueNote $accountantIssueNote): bool
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
