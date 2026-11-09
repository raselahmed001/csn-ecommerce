<?php

namespace App\Http\Controllers\ShopOwner;

use App\Actions\Shops\ListProductInShop;
use App\Actions\Shops\RemoveProductFromShop;
use App\Actions\Shops\UpdateShopProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShopOwner\ShopProductRequest;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;

#[Authorize('viewAny', [ShopProduct::class, 'shop'], only: ['index'])]
#[Authorize('create', [ShopProduct::class, 'shop'], only: ['store'])]
#[Authorize('update', 'shopProduct', only: ['update'])]
#[Authorize('delete', 'shopProduct', only: ['destroy'])]
class ShopProductController extends Controller
{
    public function index(Shop $shop): Response
    {
        return Inertia::render('owner/products/Index', [
            'shop' => $shop->only(['id', 'name']),
            'listings' => $shop->shopProducts()
                ->withLiveProduct()
                ->with('product')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(ShopProductRequest $request, Shop $shop, ListProductInShop $listProduct): RedirectResponse
    {
        $listProduct->handle($shop, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Product added to your shop.',
        ]);

        return to_route('owner.shops.products.index', $shop);
    }

    public function update(ShopProductRequest $request, Shop $shop, ShopProduct $shopProduct, UpdateShopProduct $updateListing): RedirectResponse
    {
        $updateListing->handle($shopProduct, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Shop product updated.',
        ]);

        return to_route('owner.shops.products.index', $shop);
    }

    public function destroy(Shop $shop, ShopProduct $shopProduct, RemoveProductFromShop $removeProduct): RedirectResponse
    {
        $removeProduct->handle($shopProduct);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Product removed from your shop.',
        ]);

        return to_route('owner.shops.products.index', $shop);
    }
}
