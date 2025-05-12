<?php

namespace App\Policies;

use App\Models\CashReport;
use App\Models\User;

class CashReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view cash reports')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CashReport $cashReport): bool
    {
        if ($user->can('view cash reports')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('create cash reports')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CashReport $cashReport): bool
    {
        if ($user->can('edit cash reports')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CashReport $cashReport): bool
    {
        if ($user->can('delete cash reports')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CashReport $cashReport): bool
    {
        if ($user->can('delete cash reports')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CashReport $cashReport): bool
    {
        if ($user->can('delete cash reports')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        if ($user->can('delete cash reports')) {
            return true;
        } else {
            return false;
        }
    }
}
