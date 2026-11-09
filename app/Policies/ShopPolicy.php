<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_shops');
    }

    public function create(User $user): bool
    {
        return $user->can('create_shops');
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->can('edit_shops');
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->can('delete_shops');
    }
}
