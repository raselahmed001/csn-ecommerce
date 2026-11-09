<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('permission:view_shop_products')]
class ShopController extends Controller
{
    /**
     * The shop owner's own shops (nobody else's shops appear here).
     */
    public function index(Request $request): Response
    {
        return Inertia::render('owner/shops/Index', [
            'shops' => $request->user()
                ->shops()
                ->withCount('shopProducts')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }
}
