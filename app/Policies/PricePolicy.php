<?php

namespace App\Policies;

use App\Models\Price;
use App\Models\User;

class PricePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view prices')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Price $price): bool
    {
        if ($user->can('view prices')) {
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
        if ($user->can('create prices')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Price $price): bool
    {
        if ($user->can('edit prices')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Price $price): bool
    {
        if ($user->can('delete prices')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Price $price): bool
    {
        if ($user->can('delete prices')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Price $price): bool
    {
        if ($user->can('delete prices')) {
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
        if ($user->can('delete prices')) {
            return true;
        } else {
            return false;
        }
    }
}
