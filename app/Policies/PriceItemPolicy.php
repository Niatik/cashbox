<?php

namespace App\Policies;

use App\Models\PriceItem;
use App\Models\User;

class PriceItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view price items')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PriceItem $priceItem): bool
    {
        if ($user->can('view price items')) {
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
        if ($user->can('create price items')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PriceItem $priceItem): bool
    {
        if ($user->can('edit price items')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PriceItem $priceItem): bool
    {
        if ($user->can('delete price items')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PriceItem $priceItem): bool
    {
        if ($user->can('delete price items')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PriceItem $priceItem): bool
    {
        if ($user->can('delete price items')) {
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
        if ($user->can('delete price items')) {
            return true;
        } else {
            return false;
        }
    }
}
