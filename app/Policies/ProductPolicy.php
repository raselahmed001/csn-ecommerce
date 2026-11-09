<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_catalog_products');
    }

    public function create(User $user): bool
    {
        return $user->can('create_catalog_products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('edit_catalog_products');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete_catalog_products');
    }
}
