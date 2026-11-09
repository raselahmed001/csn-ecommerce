<?php

namespace App\Actions\ShopOwners;

use App\Models\User;

class UpdateShopOwner
{
    /**
     * Updates name and email; the password changes only when a new one is given.
     *
     * @param  array{name: string, email: string, password?: string|null}  $data  validated data
     */
    public function handle(User $owner, array $data): User
    {
        $owner->name = $data['name'];
        $owner->email = $data['email'];

        if (! empty($data['password'])) {
            $owner->password = $data['password']; // hashed by the model cast
        }

        $owner->save();

        return $owner;
    }
}
