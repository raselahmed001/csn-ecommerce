<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopProduct;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Customer view (no login needed): only products assigned to a shop are visible.
 */
class StorefrontController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('store/shops/Index', [
            'shops' => Shop::query()
                ->withCount(['shopProducts' => fn ($query) => $query->withLiveProduct()])
                ->orderBy('name')
                ->paginate(12),
        ]);
    }

    public function show(Shop $shop): Response
    {
        return Inertia::render('store/shops/Show', [
            'shop' => $shop->only(['id', 'name', 'slug', 'description']),
            // A product listed in `shop_products` is the shop's product; soft-deleted Global Products are excluded
            'products' => $shop->shopProducts()
                ->withLiveProduct()
                ->with('product')
                ->orderBy('id')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function showProduct(Shop $shop, ShopProduct $shopProduct): Response
    {
        // 404 unless this is a listing of this shop (another shop's or unassigned products are never shown)
        $listing = $shop->shopProducts()
            ->whereKey($shopProduct->id)
            ->withLiveProduct()
            ->with('product')
            ->firstOrFail();

        return Inertia::render('store/products/Show', [
            'shop' => $shop->only(['id', 'name', 'slug']),
            'listing' => $listing,
        ]);
    }
}
