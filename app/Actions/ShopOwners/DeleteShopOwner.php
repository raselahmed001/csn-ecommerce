<?php

namespace App\Actions\ShopOwners;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteShopOwner
{
    /**
     * Business rule: an owner who still has shops cannot be deleted.
     *
     * @throws ValidationException
     */
    public function handle(User $owner): void
    {
        if ($owner->shops()->exists()) {
            throw ValidationException::withMessages([
                'user' => 'This owner still has shops. Delete or reassign them first.',
            ]);
        }

        $owner->delete();
    }
}
