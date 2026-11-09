<?php

namespace App\Actions\Shops;

use App\Models\ShopProduct;
use App\Services\ShopProductThumbnail;

class RemoveProductFromShop
{
    public function __construct(private readonly ShopProductThumbnail $thumbnails) {}

    /**
     * Removes only the shop listing (the shop_products row) and its thumbnail file;
     * the Global Product stays in the shared catalog.
     */
    public function handle(ShopProduct $listing): void
    {
        $listing->delete();

        $this->thumbnails->delete($listing->thumbnail);
    }
}
