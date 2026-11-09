<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\StorefrontController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShopOwner\CatalogController;
use App\Http\Controllers\ShopOwner\ShopController as OwnerShopController;
use App\Http\Controllers\ShopOwner\ShopProductController;
use Illuminate\Support\Facades\Route;

// The home page is the customer view: a list of shops (Browse shops)
Route::get('/', [StorefrontController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified', 'permission:view_dashboard'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

// Admin panel: CRUD for shops, catalog products and shop owner accounts
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Permission checks live in each controller's #[Authorize] attribute and the policies
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('shops', ShopController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
    });

// Shop owner panel: add products from the shared catalog, create own products, change price/details/thumbnail
Route::middleware(['auth', 'verified', 'role:shop_owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        // Permission + shop ownership checks live in the controllers' #[Authorize] attributes and the policies
        Route::get('shops', [OwnerShopController::class, 'index'])->name('shops.index');
        Route::get('shops/{shop}/catalog', [CatalogController::class, 'index'])->name('shops.catalog');
        Route::resource('shops.products', ShopProductController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->scoped()
            ->parameters(['products' => 'shopProduct']);
    });

// Customer view (no login needed): only products assigned to a shop are visible
Route::get('shops', [StorefrontController::class, 'index'])->name('shops.index');
Route::get('shops/{shop:slug}', [StorefrontController::class, 'show'])->name('shops.show');
Route::get('shops/{shop:slug}/products/{shopProduct}', [StorefrontController::class, 'showProduct'])->name('shops.products.show');

require __DIR__.'/settings.php';
