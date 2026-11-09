<?php

namespace App\Actions\Shops;

use App\Models\Shop;
use App\Services\ShopProductThumbnail;

class DeleteShop
{
    public function __construct(private readonly ShopProductThumbnail $thumbnails) {}

    /**
     * Deletes the shop. Its shop_products rows go with it (DB cascade),
     * and the uploaded thumbnail files are cleaned up afterwards.
     */
    public function handle(Shop $shop): void
    {
        $paths = $shop->shopProducts()->pluck('thumbnail');

        $shop->delete();

        $paths->each(fn (?string $path) => $this->thumbnails->delete($path));
    }
}
