<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ShopOwnerSeeder extends Seeder
{
    /**
     * Demo shop owner accounts (all passwords: `password`).
     */
    public function run(): void
    {
        $owners = [
            'owner@example.com' => 'Demo Shop Owner',
            'owner2@example.com' => 'Rahim Tech',
            'owner3@example.com' => 'Karim Traders',
        ];

        foreach ($owners as $email => $name) {
            $owner = User::firstOrNew(['email' => $email]);

            $owner->name = $name;

            if (! $owner->exists) {
                $owner->password = 'password';
            }
            $owner->email_verified_at = now();
            $owner->save();

            $owner->syncRoles(['shop_owner']);
        }
    }
}
