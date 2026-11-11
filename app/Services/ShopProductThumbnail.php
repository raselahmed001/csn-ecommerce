<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Stores and deletes Shop Product thumbnail files.
 * Files live on the `public` disk; only the relative path is stored in the DB.
 */
class ShopProductThumbnail
{
    private const DISK = 'public';

    private const DIRECTORY = 'shop-products';

    public function store(UploadedFile $file): string
    {
        // hashName() generates a random name, so the user-supplied file name is never used
        $path = $file->store(self::DIRECTORY, self::DISK);

        if ($path === false) {
            throw new RuntimeException('Could not store the thumbnail on the ['.self::DISK.'] disk.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * A URL the frontend can use directly.
     */
    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : Storage::disk(self::DISK)->url($path);
    }
}
