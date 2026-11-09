<?php

namespace App\Actions\Shops;

use App\Models\ShopProduct;
use App\Services\ShopProductThumbnail;
use Illuminate\Http\UploadedFile;

class UpdateShopProduct
{
    public function __construct(private readonly ShopProductThumbnail $thumbnails) {}

    /**
     * @param  array<string, mixed>  $data  validated data
     */
    public function handle(ShopProduct $listing, array $data): ShopProduct
    {
        $oldPath = $listing->thumbnail;
        $newPath = $oldPath;

        if (($data['thumbnail'] ?? null) instanceof UploadedFile) {
            $newPath = $this->thumbnails->store($data['thumbnail']);
        } elseif (! empty($data['remove_thumbnail'])) {
            $newPath = null;
        }

        unset($data['thumbnail'], $data['remove_thumbnail']);

        $listing->update([...$data, 'thumbnail' => $newPath]);

        // The old file is deleted only after the DB update succeeds
        if ($oldPath !== $newPath) {
            $this->thumbnails->delete($oldPath);
        }

        return $listing;
    }
}
