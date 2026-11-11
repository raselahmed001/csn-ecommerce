<?php

namespace App\Http\Requests\ShopOwner;

use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LogicException;

/**
 * Validation for the three modes of the single form:
 *  - Edit: the route has {shopProduct}
 *  - Add from catalog: `product_id` is sent
 *  - New product: no product_id; name/slug/sku are sent
 */
class ShopProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $listing = $this->route('shopProduct');

        $listingRules = [
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            // Image up to 2 MB only; `mimes` checks the real content type, not just the extension
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
        ];

        if ($listing) {
            return [
                ...$listingRules,
                'name' => ['nullable', 'string', 'max:255'],
            ];
        }

        if ($this->filled('product_id')) {
            return [
                ...$listingRules,
                'name' => ['nullable', 'string', 'max:255'],
                'product_id' => [
                    'required',
                    'integer',
                    // A soft-deleted Global Product cannot be added to a shop again
                    Rule::exists('products', 'id')->whereNull('deleted_at'),
                    // No duplicate listing in the same shop
                    Rule::unique('shop_products', 'product_id')->where('shop_id', $this->shop()->id),
                ],
            ];
        }

        return [
            ...$listingRules,
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')],
        ];
    }

    /**
     * The shop from the route (model-bound).
     */
    private function shop(): Shop
    {
        $shop = $this->route('shop');

        if (! $shop instanceof Shop) {
            throw new LogicException('The {shop} route parameter must be bound to a Shop model.');
        }

        return $shop;
    }

    /**
     * Human-friendly field names used in validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
        ];
    }
}
