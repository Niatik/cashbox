<?php

namespace App\Policies;

use App\Models\ProductOrder;
use App\Models\User;

class ProductOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view orders');
    }

    public function view(User $user, ProductOrder $productOrder): bool
    {
        return $user->can('view orders');
    }

    public function create(User $user): bool
    {
        return $user->can('create orders');
    }

    public function update(User $user, ProductOrder $productOrder): bool
    {
        return $user->can('edit orders');
    }

    public function delete(User $user, ProductOrder $productOrder): bool
    {
        return $user->can('delete orders');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete orders');
    }
}
