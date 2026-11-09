<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_installs_roles_and_demo_shop_data(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'admin', 'guard_name' => 'web']);
        $this->assertDatabaseHas('shops', ['slug' => 'demo-online-shop']);
        $this->assertTrue(User::where('email', 'owner@example.com')->firstOrFail()->hasRole('shop_owner'));

        $this->assertSame(4, Shop::count());
        $this->assertSame(15, Product::count());

        // every listing has an image and the file was actually copied to storage
        $this->assertSame(16, ShopProduct::count());
        ShopProduct::all()->each(function (ShopProduct $listing): void {
            $this->assertNotNull($listing->thumbnail);
            Storage::disk('public')->assertExists($listing->thumbnail);
        });

        // the same product has a different price in different shops
        $mouse = Product::where('sku', 'ELEC-001')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            ['520.00', '550.00'],
            ShopProduct::where('product_id', $mouse->id)->pluck('price')->all(),
        );

        // products not assigned to a shop have no listing (customers never see them)
        foreach (['ELEC-005', 'FASH-005', 'GROC-005'] as $sku) {
            $this->assertDatabaseMissing('shop_products', [
                'product_id' => Product::where('sku', $sku)->firstOrFail()->id,
            ]);
        }

        // running it a second time creates no duplicates and does not overwrite existing image files
        Storage::disk('public')->put('shop-products/seed-ELEC-001.jpg', 'edited-by-user');
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(16, ShopProduct::count());
        $this->assertSame('edited-by-user', Storage::disk('public')->get('shop-products/seed-ELEC-001.jpg'));
    }

    public function test_admin_can_create_update_and_delete_shop_owner_accounts(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/users/Index'));

        $this->post(route('admin.users.store'), [
            'name' => 'Shop Owner',
            'email' => 'shop-owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.index'));

        $owner = User::where('email', 'shop-owner@example.com')->firstOrFail();
        $this->assertTrue($owner->hasRole('shop_owner'));

        $this->put(route('admin.users.update', $owner), [
            'name' => 'Updated Owner',
            'email' => 'updated-owner@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'name' => 'Updated Owner',
            'email' => 'updated-owner@example.com',
        ]);

        $this->delete(route('admin.users.destroy', $owner))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
    }

    public function test_admin_can_manage_shops_and_only_assign_shop_owners(): void
    {
        $this->actingAs($this->admin());
        $owner = $this->shopOwner();
        $this->get(route('admin.shops.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/shops/Index'));
        $customer = User::factory()->create(['email_verified_at' => now()]); // has no shop_owner role

        $this->post(route('admin.shops.store'), [
            'owner_id' => $owner->id,
            'name' => 'Example Shop',
            'slug' => 'example-shop',
            'description' => 'Example description',
        ])->assertRedirect(route('admin.shops.index'));

        $shop = Shop::where('slug', 'example-shop')->firstOrFail();
        $this->assertSame($owner->id, $shop->owner_id);
        $this->delete(route('admin.users.destroy', $owner))
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $owner->id]);

        $this->get(route('admin.users.index', ['search' => $owner->email]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.email', $owner->email));

        $this->put(route('admin.shops.update', $shop), [
            'owner_id' => $owner->id,
            'name' => 'Updated Shop',
            'slug' => 'updated-shop',
            'description' => '',
        ])->assertRedirect(route('admin.shops.index'));

        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'name' => 'Updated Shop',
            'slug' => 'updated-shop',
        ]);

        $this->post(route('admin.shops.store'), [
            'owner_id' => $customer->id,
            'name' => 'Invalid Shop',
            'slug' => 'invalid-shop',
        ])->assertSessionHasErrors('owner_id');

        $this->delete(route('admin.shops.destroy', $shop))
            ->assertRedirect(route('admin.shops.index'));
        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
    }

    public function test_admin_can_manage_catalog_products(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/products/Index'));

        $this->post(route('admin.products.store'), [
            'name' => 'Wireless Mouse',
            'slug' => 'wireless-mouse',
            'sku' => 'ELEC-001',
            'description' => 'Wireless optical mouse',
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'ELEC-001')->firstOrFail();

        $this->put(route('admin.products.update', $product), [
            'name' => 'Updated Mouse',
            'slug' => 'updated-mouse',
            'sku' => 'ELEC-002',
            'description' => '',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Mouse',
            'sku' => 'ELEC-002',
        ]);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_non_admin_cannot_access_admin_management(): void
    {
        $owner = $this->shopOwner();
        $owner->email_verified_at = now();
        $owner->save();

        $this->actingAs($owner)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    private function admin(): User
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole(Role::findOrCreate('admin', 'web'));
        $this->assertTrue($user->hasPermissionTo('view_users'));

        return $user;
    }

    private function shopOwner(): User
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole(Role::findOrCreate('shop_owner', 'web'));

        return $user;
    }
}
