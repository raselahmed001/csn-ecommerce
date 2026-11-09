<?php

namespace App\Actions\ShopOwners;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreateShopOwner
{
    /**
     * Creates a verified shop owner account.
     *
     * @param  array{name: string, email: string, password: string}  $data  validated data
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $owner = new User;
            $owner->name = $data['name'];
            $owner->email = $data['email'];
            $owner->password = $data['password']; // hashed by the model cast
            $owner->email_verified_at = now();
            $owner->save();

            $owner->assignRole(Role::findOrCreate('shop_owner', 'web'));

            return $owner;
        });
    }
}
