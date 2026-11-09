<?php

namespace Tests\Feature\Customer;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Customer view (no login needed): only products assigned to a shop are visible.
 */
class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_only_see_products_assigned_to_the_shop_with_shop_specific_data(): void
    {
        $listed = $this->product('listed');
        $unassigned = $this->product('unassigned');
        $shop = $this->shop('public-shop');

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $listed->id,
            'name' => 'Shop Special Name',
            'price' => 12.50,
            'thumbnail' => 'https://example.test/thumb.jpg',
        ]);

        $this->get(route('shops.show', $shop->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/shops/Show')
                ->has('products.data', 1)
                ->where('products.data.0.product.slug', $listed->slug)
                ->where('products.data.0.name', 'Shop Special Name')
                ->where('products.data.0.price', '12.50')
                ->where('products.data.0.thumbnail_url', 'https://example.test/thumb.jpg'));

        // a product that was not assigned to a shop appears nowhere
        $this->assertDatabaseMissing('shop_products', ['product_id' => $unassigned->id]);
    }

    public function test_same_catalog_product_can_have_different_price_in_each_shop(): void
    {
        $product = $this->product('shared');
        $shopA = $this->shop('shop-a');
        $shopB = $this->shop('shop-b');

        ShopProduct::create(['shop_id' => $shopA->id, 'product_id' => $product->id, 'price' => 10]);
        ShopProduct::create(['shop_id' => $shopB->id, 'product_id' => $product->id, 'price' => 99]);

        $this->get(route('shops.show', $shopA->slug))
            ->assertInertia(fn (Assert $page) => $page->where('products.data.0.price', '10.00'));
        $this->get(route('shops.show', $shopB->slug))
            ->assertInertia(fn (Assert $page) => $page->where('products.data.0.price', '99.00'));
    }

    public function test_product_detail_is_only_available_through_the_shop_it_is_listed_in(): void
    {
        $product = $this->product('detail');
        $shop = $this->shop('has-product');
        $otherShop = $this->shop('no-product');
        $listing = ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $product->id, 'price' => 5]);

        $this->get(route('shops.products.show', [$shop->slug, $listing->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/products/Show')
                ->where('listing.product_id', $product->id));

        // the same listing is not reachable under another shop's URL
        $this->get(route('shops.products.show', [$otherShop->slug, $listing->id]))->assertNotFound();
    }

    public function test_soft_deleted_catalog_products_are_not_shown(): void
    {
        $product = $this->product('gone');
        $shop = $this->shop('shop-x');
        $listing = ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $product->id, 'price' => 5]);
        $product->delete();

        $this->get(route('shops.show', $shop->slug))
            ->assertInertia(fn (Assert $page) => $page->has('products.data', 0));
        $this->get(route('shops.products.show', [$shop->slug, $listing->id]))->assertNotFound();
    }

    public function test_shop_list_is_public(): void
    {
        $this->shop('listed-shop');

        // the home page also lists the shops
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/shops/Index')
                ->has('shops.data', 1));

        $this->get(route('shops.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/shops/Index')
                ->has('shops.data', 1));
    }

    private function shop(string $slug): Shop
    {
        return Shop::create([
            'owner_id' => User::factory()->create()->id,
            'name' => ucfirst($slug),
            'slug' => $slug,
        ]);
    }

    private function product(string $suffix): Product
    {
        return Product::create([
            'name' => "Product {$suffix}",
            'slug' => "product-{$suffix}",
            'sku' => strtoupper($suffix),
        ]);
    }
}
