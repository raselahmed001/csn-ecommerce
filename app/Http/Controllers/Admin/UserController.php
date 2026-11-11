<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ShopOwners\CreateShopOwner;
use App\Actions\ShopOwners\DeleteShopOwner;
use App\Actions\ShopOwners\UpdateShopOwner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexUserRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;

#[Authorize('viewAny', User::class, only: ['index'])]
#[Authorize('create', User::class, only: ['store'])]
#[Authorize('update', 'user', only: ['update'])]
#[Authorize('delete', 'user', only: ['destroy'])]
class UserController extends Controller
{
    public function index(IndexUserRequest $request): Response
    {
        $search = $request->validated('search', '');

        return Inertia::render('admin/users/Index', [
            'users' => User::query()->shopOwners()
                ->search($search)
                ->withCount('shops')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StoreUserRequest $request, CreateShopOwner $createOwner): RedirectResponse
    {
        $createOwner->handle($request->ownerData());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shop owner created.']);

        return to_route('admin.users.index');
    }

    public function update(UpdateUserRequest $request, User $user, UpdateShopOwner $updateOwner): RedirectResponse
    {
        $updateOwner->handle(User::query()->shopOwners()->findOrFail($user->id), $request->ownerData());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shop owner updated.']);

        return to_route('admin.users.index');
    }

    public function destroy(User $user, DeleteShopOwner $deleteOwner): RedirectResponse
    {
        $deleteOwner->handle(User::query()->shopOwners()->findOrFail($user->id));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shop owner deleted.']);

        return to_route('admin.users.index');
    }
}
