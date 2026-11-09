<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Which catalog products each shop lists, and at what price.
 * The same product can have a different price (and optionally name/description) in each shop.
 * Images are copied from `database/seeders/images/{SKU}.jpg` to the `public` disk.
 */
class DemoShopProductSeeder extends Seeder
{
    public function run(): void
    {
        // shop slug => [sku => ['price' => ..., 'name' => ..., 'description' => ...]]
        $listings = [
            'demo-online-shop' => [
                'ELEC-001' => ['price' => 550],
                'ELEC-002' => ['price' => 850],
                'FASH-001' => ['price' => 450],
                'GROC-001' => ['price' => 400],
            ],
            'tech-hub' => [
                'ELEC-001' => [
                    'price' => 520,
                    'name' => 'Tech Hub Wireless Mouse',
                    'description' => 'Ergonomic wireless mouse with silent clicks. 1 year Tech Hub warranty included.',
                ],
                'ELEC-002' => ['price' => 880],
                'ELEC-003' => ['price' => 1850],
                'ELEC-004' => ['price' => 3200],
            ],
            'fashion-point' => [
                'FASH-001' => ['price' => 480],
                'FASH-002' => ['price' => 950],
                'FASH-003' => ['price' => 1400],
                'FASH-004' => ['price' => 2600],
            ],
            'fresh-mart' => [
                'GROC-001' => ['price' => 390],
                'GROC-002' => ['price' => 185],
                'GROC-003' => ['price' => 160],
                'GROC-004' => ['price' => 320],
            ],
        ];

        foreach ($listings as $shopSlug => $items) {
            $shop = Shop::where('slug', $shopSlug)->firstOrFail();

            foreach ($items as $sku => $details) {
                $product = Product::where('sku', $sku)->firstOrFail();

                ShopProduct::updateOrCreate(
                    ['shop_id' => $shop->id, 'product_id' => $product->id],
                    [
                        'name' => $details['name'] ?? null,
                        'description' => $details['description'] ?? null,
                        'price' => $details['price'],
                        'thumbnail' => $this->copyImage($sku),
                    ],
                );
            }
        }
    }

    /**
     * Copies the seeder image to storage and returns the relative path.
     */
    private function copyImage(string $sku): ?string
    {
        $source = database_path("seeders/images/{$sku}.jpg");

        if (! File::exists($source)) {
            return null;
        }

        $path = "shop-products/seed-{$sku}.jpg";

        // Never overwrite an existing file (re-running the seeder is safe)
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, File::get($source));
        }

        return $path;
    }
}
