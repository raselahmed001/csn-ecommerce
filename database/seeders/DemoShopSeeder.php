<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoShopSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            ['owner@example.com', 'Demo Online Shop', 'demo-online-shop', 'A general store with a little of everything'],
            ['owner2@example.com', 'Tech Hub', 'tech-hub', 'Gadgets and accessories at honest prices'],
            ['owner3@example.com', 'Fashion Point', 'fashion-point', 'Trendy clothing and footwear for everyone'],
            ['owner3@example.com', 'Fresh Mart', 'fresh-mart', 'Daily groceries delivered fresh'],
        ];

        foreach ($shops as [$ownerEmail, $name, $slug, $description]) {
            $owner = User::where('email', $ownerEmail)->firstOrFail();

            Shop::firstOrCreate(
                ['slug' => $slug],
                ['owner_id' => $owner->id, 'name' => $name, 'description' => $description],
            );
        }
    }
}
