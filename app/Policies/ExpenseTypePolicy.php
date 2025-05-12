<?php

namespace App\Policies;

use App\Models\ExpenseType;
use App\Models\User;

class ExpenseTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view expense types')) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExpenseType $expenseType): bool
    {
        if ($user->can('view expense types')) {
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
        if ($user->can('create expense types')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExpenseType $expenseType): bool
    {
        if ($user->can('edit expense types')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExpenseType $expenseType): bool
    {
        if ($user->can('delete expense types')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExpenseType $expenseType): bool
    {
        if ($user->can('delete expense types')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExpenseType $expenseType): bool
    {
        if ($user->can('delete expense types')) {
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
        if ($user->can('delete expense types')) {
            return true;
        } else {
            return false;
        }
    }
}
