<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both create and edit; it is an edit when the route has {shop}.
 */
class ShopRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'owner_id' => ['required', 'integer', Rule::in(User::query()->shopOwners()->pluck('id'))],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('shops', 'slug')->ignore($this->route('shop'))],
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
            'owner_id' => 'owner',
        ];
    }
}
