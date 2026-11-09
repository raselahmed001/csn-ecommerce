<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'sku', 'description'])]
class Product extends Model
{
    use SoftDeletes;

    /**
     * Catalog products that this shop has not listed yet.
     */
    #[Scope]
    protected function notListedIn(Builder $query, Shop $shop): void
    {
        $query->whereDoesntHave('shopProducts', fn (Builder $listings) => $listings->where('shop_id', $shop->id));
    }

    public function shopProducts(): HasMany
    {
        return $this->hasMany(ShopProduct::class);
    }
}
