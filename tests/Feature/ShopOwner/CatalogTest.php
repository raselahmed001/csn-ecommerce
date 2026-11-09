<?php

namespace Tests\Feature\ShopOwner;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(PermissionSeeder::class);
    }

    public function test_thumbnail_upload_is_stored_replaced_removed_and_deleted_with_the_listing(): void
    {
        $product = $this->product();
        [$owner, $shop] = $this->ownerWithShop('upload');
        $this->actingAs($owner);

        $this->post(route('owner.shops.products.store', $shop), [
            'product_id' => $product->id, 'price' => 10,
            'thumbnail' => UploadedFile::fake()->image('first.png'),
        ])->assertRedirect(route('owner.shops.products.index', $shop));

        $listing = ShopProduct::firstOrFail();
        $first = $listing->thumbnail;
        $this->assertStringStartsWith('shop-products/', $first);
        $this->assertStringNotContainsString('first', $first); // the user-supplied file name is not used
        Storage::disk('public')->assertExists($first);
        $this->assertNotNull($listing->thumbnail_url);

        // uploading a new file deletes the old one
        $this->put(route('owner.shops.products.update', [$shop, $listing]), [
            'price' => 10,
            'thumbnail' => UploadedFile::fake()->image('second.jpg'),
        ])->assertRedirect();
        $second = $listing->refresh()->thumbnail;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        // without a new file the existing one is kept
        $this->put(route('owner.shops.products.update', [$shop, $listing]), [
            'price' => 11,
        ])->assertRedirect();
        $this->assertSame($second, $listing->refresh()->thumbnail);

        // Remove checkbox
        $this->put(route('owner.shops.products.update', [$shop, $listing]), [
            'price' => 11, 'remove_thumbnail' => '1',
        ])->assertRedirect();
        $this->assertNull($listing->refresh()->thumbnail);
        Storage::disk('public')->assertMissing($second);

        // deleting the listing deletes the file too
        $this->put(route('owner.shops.products.update', [$shop, $listing]), [
            'price' => 11,
            'thumbnail' => UploadedFile::fake()->image('third.webp'),
        ]);
        $third = $listing->refresh()->thumbnail;
        $this->delete(route('owner.shops.products.destroy', [$shop, $listing]));
        Storage::disk('public')->assertMissing($third);
    }

    public function test_invalid_thumbnails_are_rejected(): void
    {
        $product = $this->product();
        [$owner, $shop] = $this->ownerWithShop('badfile');
        $this->actingAs($owner);
        $payload = ['product_id' => $product->id, 'price' => 10];

        $this->post(route('owner.shops.products.store', $shop), [
            ...$payload, 'thumbnail' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('thumbnail');

        $this->post(route('owner.shops.products.store', $shop), [
            ...$payload, 'thumbnail' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('thumbnail');

        $this->post(route('owner.shops.products.store', $shop), [
            ...$payload, 'thumbnail' => UploadedFile::fake()->image('huge.jpg')->size(3000),
        ])->assertSessionHasErrors('thumbnail');

        $this->post(route('owner.shops.products.store', $shop), [
            ...$payload, 'thumbnail' => 'https://evil.test/a.jpg',
        ])->assertSessionHasErrors('thumbnail');

        $this->assertSame(0, ShopProduct::count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_shop_owners_can_add_edit_and_remove_shared_catalog_listings(): void
    {
        $product = $this->product();
        [$owner, $shop] = $this->ownerWithShop('owner-one');
        [$otherOwner, $otherShop] = $this->ownerWithShop('owner-two');

        $this->actingAs($owner)
            ->get(route('owner.shops.catalog', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('owner/catalog/Index')
                ->has('products.data', 1));

        // The slide-in form needs the product's data straight from the catalog list
        $this->get(route('owner.shops.catalog', $shop))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.id', $product->id)
                ->where('products.data.0.sku', 'ELEC-001'));

        $this->post(route('owner.shops.products.store', $shop), [
            'product_id' => $product->id,
            'price' => 25.50,
            'thumbnail' => UploadedFile::fake()->image('mouse.jpg'),
        ])->assertRedirect(route('owner.shops.products.index', $shop));

        $listing = ShopProduct::where('shop_id', $shop->id)->where('product_id', $product->id)->firstOrFail();

        // The slide-in edit form is filled from the listings list (listing + its product)
        $this->get(route('owner.shops.products.index', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('owner/products/Index')
                ->where('listings.data.0.id', $listing->id)
                ->where('listings.data.0.product.sku', 'ELEC-001')
                ->where('listings.data.0.price', '25.50')
                ->whereNot('listings.data.0.thumbnail_url', null));

        $this->put(route('owner.shops.products.update', [$shop, $listing]), [
            'name' => 'Owner One Mouse',
            'description' => 'Shop-specific description',
            'price' => 27.00,
            'thumbnail' => UploadedFile::fake()->image('owner-one.jpg'),
        ])->assertRedirect(route('owner.shops.products.index', $shop));

        $this->assertDatabaseHas('shop_products', [
            'id' => $listing->id,
            'name' => 'Owner One Mouse',
            'description' => 'Shop-specific description',
            'price' => '27.00',
        ]);

        // the same Global Product can have a different price in another shop without changing the first shop's listing
        $this->actingAs($otherOwner)
            ->post(route('owner.shops.products.store', $otherShop), [
                'product_id' => $product->id,
                'price' => 30,
            ])->assertRedirect(route('owner.shops.products.index', $otherShop));
        $this->assertDatabaseHas('shop_products', ['id' => $listing->id, 'price' => '27.00']);

        $this->actingAs($owner)
            ->get(route('owner.shops.products.index', $shop))
            ->assertInertia(fn (Assert $page) => $page->has('listings.data', 1));

        $this->get(route('owner.shops.catalog', $shop))
            ->assertInertia(fn (Assert $page) => $page->has('products.data', 0));

        $this->delete(route('owner.shops.products.destroy', [$shop, $listing]))
            ->assertRedirect(route('owner.shops.products.index', $shop));

        $this->assertDatabaseMissing('shop_products', ['id' => $listing->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_owner_created_products_are_shared_but_listed_only_in_their_shop(): void
    {
        [$owner, $shop] = $this->ownerWithShop('creator');
        [$otherOwner, $otherShop] = $this->ownerWithShop('viewer');

        $this->actingAs($owner)
            ->post(route('owner.shops.products.store', $shop), [
                'name' => 'Owner-made Keyboard',
                'slug' => 'owner-made-keyboard',
                'sku' => 'OWNER-001',
                'description' => 'Created by a shop owner',
                'price' => 45.25,
                'thumbnail' => UploadedFile::fake()->image('keyboard.jpg'),
            ])
            ->assertRedirect(route('owner.shops.products.index', $shop));

        $product = Product::where('sku', 'OWNER-001')->firstOrFail();
        $this->assertDatabaseHas('shop_products', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => '45.25',
        ]);

        $this->actingAs($otherOwner)
            ->get(route('owner.shops.catalog', $otherShop))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.sku', 'OWNER-001'));

        $this->assertDatabaseMissing('shop_products', ['shop_id' => $otherShop->id]);
    }

    public function test_duplicate_and_soft_deleted_products_cannot_be_added(): void
    {
        $product = $this->product();
        [$owner, $shop] = $this->ownerWithShop('dup');
        $payload = ['product_id' => $product->id, 'price' => 10];

        $this->actingAs($owner)->post(route('owner.shops.products.store', $shop), $payload)->assertRedirect();
        $this->post(route('owner.shops.products.store', $shop), $payload)->assertSessionHasErrors('product_id');
        $this->assertSame(1, ShopProduct::where('shop_id', $shop->id)->count());

        $deleted = $this->product('DEL-001', 'deleted-product');
        $deleted->delete();
        $this->post(route('owner.shops.products.store', $shop), [...$payload, 'product_id' => $deleted->id])
            ->assertSessionHasErrors('product_id');
    }

    public function test_shop_owners_cannot_access_another_owners_shop(): void
    {
        $product = $this->product();
        [$owner] = $this->ownerWithShop('one');
        [$otherOwner, $shop] = $this->ownerWithShop('two');
        $listing = $shop->shopProducts()->create(['product_id' => $product->id, 'price' => 10]);

        $this->actingAs($owner);

        $this->get(route('owner.shops.catalog', $shop))->assertForbidden();
        $this->get(route('owner.shops.products.index', $shop))->assertForbidden();
        $this->post(route('owner.shops.products.store', $shop), [
            'product_id' => $product->id, 'price' => 10,
        ])->assertForbidden();
        $this->put(route('owner.shops.products.update', [$shop, $listing]), [
            'price' => 1,
        ])->assertForbidden();
        $this->delete(route('owner.shops.products.destroy', [$shop, $listing]))->assertForbidden();

        $this->assertDatabaseHas('shop_products', ['id' => $listing->id, 'price' => '10.00']);
    }

    public function test_listing_must_belong_to_the_shop_in_the_url(): void
    {
        $product = $this->product();
        [$owner, $shop] = $this->ownerWithShop('mine');
        [, $otherShop] = $this->ownerWithShop('theirs');
        $foreign = $otherShop->shopProducts()->create(['product_id' => $product->id, 'price' => 10]);

        // a listing of another shop under your own shop's URL gives 404 via scoped binding
        $this->actingAs($owner)
            ->put(route('owner.shops.products.update', [$shop, $foreign]), [
                'price' => 1,
            ])->assertNotFound();
    }

    public function test_my_shops_lists_only_own_shops(): void
    {
        [$owner] = $this->ownerWithShop('mine');
        $this->ownerWithShop('theirs');

        $this->actingAs($owner)->get(route('owner.shops.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('owner/shops/Index')
                ->has('shops', 1)
                ->where('shops.0.slug', 'shop-mine'));
    }

    private function product(string $sku = 'ELEC-001', string $slug = 'wireless-mouse'): Product
    {
        return Product::create([
            'name' => 'Wireless Mouse',
            'slug' => $slug,
            'sku' => $sku,
            'description' => 'A shared catalog product',
        ]);
    }

    /**
     * @return array{User, Shop}
     */
    private function ownerWithShop(string $suffix): array
    {
        $owner = User::factory()->create([
            'email' => "{$suffix}@example.com",
            'email_verified_at' => now(),
        ]);
        $owner->assignRole(Role::findByName('shop_owner', 'web'));

        $shop = Shop::create([
            'owner_id' => $owner->id,
            'name' => "Shop {$suffix}",
            'slug' => "shop-{$suffix}",
        ]);

        return [$owner, $shop];
    }
}
