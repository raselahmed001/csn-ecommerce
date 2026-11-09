<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;

#[Authorize('viewAny', [ShopProduct::class, 'shop'])]
class CatalogController extends Controller
{
    /**
     * Shared Global Catalog: products that are not yet listed in this shop.
     */
    public function index(Shop $shop): Response
    {
        $products = Product::query()
            ->notListedIn($shop)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('owner/catalog/Index', [
            'shop' => $shop->only(['id', 'name']),
            'products' => $products,
        ]);
    }
}
