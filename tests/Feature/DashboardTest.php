<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_without_dashboard_permission_are_forbidden()
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_admin_sees_totals_for_the_whole_system(): void
    {
        $this->seed(PermissionSeeder::class);
        $admin = $this->userWithRole('admin');
        [$owner, $shop] = $this->ownerWithShop('one');
        [, $otherShop] = $this->ownerWithShop('two');
        $product = Product::create(['name' => 'Mouse', 'slug' => 'mouse', 'sku' => 'M-1']);
        Product::create(['name' => 'Keyboard', 'slug' => 'keyboard', 'sku' => 'K-1']);
        ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $product->id, 'price' => 10]);
        ShopProduct::create(['shop_id' => $otherShop->id, 'product_id' => $product->id, 'price' => 12]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats', [
                    ['label' => 'Shop owners', 'value' => 2, 'href' => route('admin.users.index')],
                    ['label' => 'Shops', 'value' => 2, 'href' => route('admin.shops.index')],
                    ['label' => 'Catalog products', 'value' => 2, 'href' => route('admin.products.index')],
                    ['label' => 'Products listed in shops', 'value' => 2, 'href' => route('admin.shops.index')],
                ]));
    }

    public function test_shop_owner_sees_only_their_own_numbers(): void
    {
        $this->seed(PermissionSeeder::class);
        [$owner, $shop] = $this->ownerWithShop('mine');
        [, $otherShop] = $this->ownerWithShop('theirs');
        $mouse = Product::create(['name' => 'Mouse', 'slug' => 'mouse', 'sku' => 'M-1']);
        $keyboard = Product::create(['name' => 'Keyboard', 'slug' => 'keyboard', 'sku' => 'K-1']);
        ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $mouse->id, 'price' => 10]);
        ShopProduct::create(['shop_id' => $otherShop->id, 'product_id' => $mouse->id, 'price' => 10]);
        ShopProduct::create(['shop_id' => $otherShop->id, 'product_id' => $keyboard->id, 'price' => 10]);

        $this->actingAs($owner)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats', [
                    ['label' => 'My shops', 'value' => 1, 'href' => route('owner.shops.index')],
                    ['label' => 'Products listed in my shops', 'value' => 1, 'href' => route('owner.shops.index')],
                ]));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole(Role::findByName($role, 'web'));

        return $user;
    }

    /**
     * @return array{User, Shop}
     */
    private function ownerWithShop(string $suffix): array
    {
        $owner = $this->userWithRole('shop_owner');
        $shop = Shop::create(['owner_id' => $owner->id, 'name' => "Shop {$suffix}", 'slug' => "shop-{$suffix}"]);

        return [$owner, $shop];
    }
}
