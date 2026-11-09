<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;

/**
 * The numbers shown on the dashboard. Each role only sees the data it manages.
 */
class DashboardStats
{
    /**
     * @return array<int, array{label: string, value: int, href: string}>
     */
    public function for(User $user): array
    {
        return $user->hasRole('admin') ? $this->admin() : $this->shopOwner($user);
    }

    /**
     * @return array<int, array{label: string, value: int, href: string}>
     */
    private function admin(): array
    {
        return [
            ['label' => 'Shop owners', 'value' => User::query()->shopOwners()->count(), 'href' => route('admin.users.index')],
            ['label' => 'Shops', 'value' => Shop::count(), 'href' => route('admin.shops.index')],
            ['label' => 'Catalog products', 'value' => Product::count(), 'href' => route('admin.products.index')],
            ['label' => 'Products listed in shops', 'value' => ShopProduct::count(), 'href' => route('admin.shops.index')],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, href: string}>
     */
    private function shopOwner(User $owner): array
    {
        return [
            ['label' => 'My shops', 'value' => $owner->shops()->count(), 'href' => route('owner.shops.index')],
            [
                'label' => 'Products listed in my shops',
                // Only listings of this owner's own shops are counted
                'value' => ShopProduct::whereIn('shop_id', $owner->shops()->select('id'))->count(),
                'href' => route('owner.shops.index'),
            ],
        ];
    }
}
