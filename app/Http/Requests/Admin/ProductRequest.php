<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both create and edit; it is an edit when the route has {product}.
 */
class ProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'description' => ['nullable', 'string'],
        ];
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
