<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The query rules live in the models (local scopes) so every controller reuses the same definition.
 */
class ModelScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_owners_scope_only_returns_users_with_the_shop_owner_role(): void
    {
        $this->seed(PermissionSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole(Role::findByName('shop_owner', 'web'));
        $admin = User::factory()->create();
        $admin->assignRole(Role::findByName('admin', 'web'));
        User::factory()->create(); // no role

        $this->assertSame([$owner->id], User::query()->shopOwners()->pluck('id')->all());
    }

    public function test_search_scope_matches_name_or_email_and_ignores_blank_terms(): void
    {
        User::factory()->create(['name' => 'Rahim Uddin', 'email' => 'rahim@example.com']);
        User::factory()->create(['name' => 'Karim', 'email' => 'karim@shop.test']);

        $this->assertSame(['Rahim Uddin'], User::query()->search('rahim')->pluck('name')->all());
        $this->assertSame(['Karim'], User::query()->search('shop.test')->pluck('name')->all());
        $this->assertCount(2, User::query()->search('')->get());
        $this->assertCount(2, User::query()->search(null)->get());
        $this->assertCount(0, User::query()->search('nobody')->get());
    }

    public function test_not_listed_in_scope_hides_products_already_in_the_shop(): void
    {
        $owner = User::factory()->create();
        $shop = Shop::create(['owner_id' => $owner->id, 'name' => 'Shop', 'slug' => 'shop']);
        $otherShop = Shop::create(['owner_id' => $owner->id, 'name' => 'Other', 'slug' => 'other']);
        $listed = Product::create(['name' => 'Listed', 'slug' => 'listed', 'sku' => 'L-1']);
        $free = Product::create(['name' => 'Free', 'slug' => 'free', 'sku' => 'F-1']);
        $onlyElsewhere = Product::create(['name' => 'Elsewhere', 'slug' => 'elsewhere', 'sku' => 'E-1']);

        ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $listed->id, 'price' => 10]);
        ShopProduct::create(['shop_id' => $otherShop->id, 'product_id' => $onlyElsewhere->id, 'price' => 10]);

        $this->assertEqualsCanonicalizing(
            [$free->id, $onlyElsewhere->id],
            Product::query()->notListedIn($shop)->pluck('id')->all(),
        );
    }

    public function test_with_live_product_scope_skips_listings_of_soft_deleted_products(): void
    {
        $owner = User::factory()->create();
        $shop = Shop::create(['owner_id' => $owner->id, 'name' => 'Shop', 'slug' => 'shop']);
        $live = Product::create(['name' => 'Live', 'slug' => 'live', 'sku' => 'L-1']);
        $gone = Product::create(['name' => 'Gone', 'slug' => 'gone', 'sku' => 'G-1']);
        $liveListing = ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $live->id, 'price' => 10]);
        ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $gone->id, 'price' => 10]);
        $gone->delete();

        $this->assertSame([$liveListing->id], ShopProduct::query()->withLiveProduct()->pluck('id')->all());
    }
}
