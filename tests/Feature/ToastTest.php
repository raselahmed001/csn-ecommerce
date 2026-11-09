<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Every create / update / delete must flash a toast the frontend can show.
 * The frontend listens to Inertia's flash data (Inertia::flash), NOT to the plain session
 * (->with('toast')), which silently showed nothing before.
 */
class ToastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(PermissionSeeder::class);
    }

    public function test_admin_shop_owner_actions_flash_a_toast(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $this->post(route('admin.users.store'), [
            'name' => 'Owner', 'email' => 'owner@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Shop owner created.']);

        $owner = User::where('email', 'owner@example.com')->firstOrFail();

        $this->put(route('admin.users.update', $owner), [
            'name' => 'Owner Two', 'email' => 'owner@example.com',
        ])->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Shop owner updated.']);

        $this->delete(route('admin.users.destroy', $owner))
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Shop owner deleted.']);
    }

    public function test_admin_shop_actions_flash_a_toast(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $owner = $this->userWithRole('shop_owner');

        $this->post(route('admin.shops.store'), [
            'owner_id' => $owner->id, 'name' => 'Shop', 'slug' => 'shop',
        ])->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Shop created.']);

        $shop = Shop::firstOrFail();

        $this->put(route('admin.shops.update', $shop), [
            'owner_id' => $owner->id, 'name' => 'Shop 2', 'slug' => 'shop',
        ])->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Shop updated.']);

        $this->delete(route('admin.shops.destroy', $shop))
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Shop deleted.']);
    }

    public function test_admin_catalog_product_actions_flash_a_toast(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $this->post(route('admin.products.store'), ['name' => 'Mouse', 'slug' => 'mouse', 'sku' => 'M-1'])
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Catalog product created.']);

        $product = Product::firstOrFail();

        $this->put(route('admin.products.update', $product), ['name' => 'Mouse 2', 'slug' => 'mouse', 'sku' => 'M-1'])
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Catalog product updated.']);

        $this->delete(route('admin.products.destroy', $product))
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Catalog product removed.']);
    }

    public function test_shop_owner_listing_actions_flash_a_toast(): void
    {
        $owner = $this->userWithRole('shop_owner');
        $shop = Shop::create(['owner_id' => $owner->id, 'name' => 'Mine', 'slug' => 'mine']);
        $product = Product::create(['name' => 'Mouse', 'slug' => 'mouse', 'sku' => 'M-1']);
        $this->actingAs($owner);

        $this->post(route('owner.shops.products.store', $shop), ['product_id' => $product->id, 'price' => 10])
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Product added to your shop.']);

        $listing = ShopProduct::firstOrFail();

        $this->put(route('owner.shops.products.update', [$shop, $listing]), ['price' => 12])
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Shop product updated.']);

        $this->delete(route('owner.shops.products.destroy', [$shop, $listing]))
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Product removed from your shop.']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole(Role::findByName($role, 'web'));

        return $user;
    }
}
