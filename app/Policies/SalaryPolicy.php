<?php

namespace App\Policies;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalaryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view salaries')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Salary $salary): bool
    {
        if ($user->can('view salaries')) {
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
        if ($user->can('create salaries')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Salary $salary): bool
    {
        if ($user->can('update salaries')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Salary $salary): bool
    {
        if ($user->can('delete salaries')) {
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
        if ($user->can('delete salaries')) {
            return true;
        } else {
            return false;
        }
    }
}
