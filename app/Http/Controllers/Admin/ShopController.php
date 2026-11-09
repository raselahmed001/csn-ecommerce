<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Shops\DeleteShop;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;

#[Authorize('viewAny', Shop::class, only: ['index'])]
#[Authorize('create', Shop::class, only: ['store'])]
#[Authorize('update', 'shop', only: ['update'])]
#[Authorize('delete', 'shop', only: ['destroy'])]
class ShopController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/shops/Index', [
            'shops' => Shop::with('owner:id,name,email')->latest()->paginate(10),
            // Owners for the form's dropdown
            'owners' => User::query()->shopOwners()->select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    public function store(ShopRequest $request): RedirectResponse
    {
        Shop::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shop created.']);

        return to_route('admin.shops.index');
    }

    public function update(ShopRequest $request, Shop $shop): RedirectResponse
    {
        $shop->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shop updated.']);

        return to_route('admin.shops.index');
    }

    public function destroy(Shop $shop, DeleteShop $deleteShop): RedirectResponse
    {
        $deleteShop->handle($shop);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shop deleted.']);

        return to_route('admin.shops.index');
    }
}
