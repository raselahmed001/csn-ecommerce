<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;

/**
 * Shop Owner listing authorization: the user needs the permission AND must own the shop
 * (Business rule: a shop owner cannot access another shop's data).
 */
class ShopProductPolicy
{
    public function viewAny(User $user, Shop $shop): bool
    {
        return $user->can('view_shop_products') && $this->ownsShop($user, $shop->id);
    }

    public function create(User $user, Shop $shop): bool
    {
        return $user->can('add_shop_products') && $this->ownsShop($user, $shop->id);
    }

    public function update(User $user, ShopProduct $shopProduct): bool
    {
        return $user->can('edit_shop_products') && $this->ownsShop($user, $shopProduct->shop_id);
    }

    public function delete(User $user, ShopProduct $shopProduct): bool
    {
        return $user->can('remove_shop_products') && $this->ownsShop($user, $shopProduct->shop_id);
    }

    private function ownsShop(User $user, int $shopId): bool
    {
        return $user->shops()->whereKey($shopId)->exists();
    }
}
