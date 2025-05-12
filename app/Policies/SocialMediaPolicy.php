<?php

namespace App\Policies;

use App\Models\SocialMedia;
use App\Models\User;

class SocialMediaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('view social media')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SocialMedia $socialMedia): bool
    {
        if ($user->can('view social media')) {
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
        if ($user->can('create social media')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SocialMedia $socialMedia): bool
    {
        if ($user->can('edit social media')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SocialMedia $socialMedia): bool
    {
        if ($user->can('delete social media')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SocialMedia $socialMedia): bool
    {
        if ($user->can('delete social media')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SocialMedia $socialMedia): bool
    {
        if ($user->can('delete social media')) {
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
        if ($user->can('delete social media')) {
            return true;
        } else {
            return false;
        }
    }
}
