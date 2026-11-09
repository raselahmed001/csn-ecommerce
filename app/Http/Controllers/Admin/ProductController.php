<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;

#[Authorize('viewAny', Product::class, only: ['index'])]
#[Authorize('create', Product::class, only: ['store'])]
#[Authorize('update', 'product', only: ['update'])]
#[Authorize('delete', 'product', only: ['destroy'])]
class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/products/Index', [
            'products' => Product::query()->latest()->paginate(10),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Catalog product created.',
        ]);

        return to_route('admin.products.index');
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Catalog product updated.',
        ]);

        return to_route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Soft delete: existing shop listings stay intact, but the product can no longer be added to a shop
        $product->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Catalog product removed.',
        ]);

        return to_route('admin.products.index');
    }
}
