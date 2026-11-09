<?php

namespace App\Actions\Shops;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Services\ShopProductThumbnail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ListProductInShop
{
    public function __construct(private readonly ShopProductThumbnail $thumbnails) {}

    /**
     * Lists a product in a shop. If `product_id` is given, that Shared Catalog product is added;
     * otherwise a new Global Product is created first (it becomes available in the catalog for everyone) and then listed.
     *
     * @param  array<string, mixed>  $data  validated data
     */
    public function handle(Shop $shop, array $data): ShopProduct
    {
        return DB::transaction(function () use ($shop, $data): ShopProduct {
            if (empty($data['product_id'])) {
                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'sku' => $data['sku'],
                    'description' => $data['description'] ?? null,
                ]);

                // The new product's name/description live on the Global Product; no shop-level override is needed
                $data['product_id'] = $product->id;
                unset($data['name'], $data['description']);
            }

            $thumbnail = ($data['thumbnail'] ?? null) instanceof UploadedFile
                ? $this->thumbnails->store($data['thumbnail'])
                : null;

            return $shop->shopProducts()->create([
                'product_id' => $data['product_id'],
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'thumbnail' => $thumbnail,
            ]);
        });
    }
}
