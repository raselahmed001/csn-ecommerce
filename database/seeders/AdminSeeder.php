<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrNew([
            'email' => 'admin@example.com',
        ]);

        $admin->name = 'System Admin';

        if (! $admin->exists) {
            $admin->password = 'password';
        }
        $admin->email_verified_at = now();
        $admin->save();

        $admin->syncRoles(['admin']);
    }
}
