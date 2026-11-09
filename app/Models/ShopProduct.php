<?php

namespace App\Models;

use App\Services\ShopProductThumbnail;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['shop_id', 'product_id', 'name', 'description', 'price', 'thumbnail'])]
#[Appends(['thumbnail_url'])]
class ShopProduct extends Model
{
    /**
     * A listing only counts while its Global Product still exists (is not soft-deleted).
     */
    #[Scope]
    protected function withLiveProduct(Builder $query): void
    {
        $query->whereHas('product');
    }

    /**
     * The `thumbnail` column stores a path; this URL is what is sent to the frontend.
     *
     * @return Attribute<string|null, never>
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => app(ShopProductThumbnail::class)->url($this->thumbnail));
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
