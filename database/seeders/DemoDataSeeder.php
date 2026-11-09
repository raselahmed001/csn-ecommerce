<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Demo products for the Shared Global Catalog.
 * The last three (`*-005`) are not listed in any shop, so customers never see them.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['ELEC-001', 'Wireless Mouse', 'wireless-mouse', 'Wireless optical mouse with 2.4GHz receiver'],
            ['ELEC-002', 'USB Keyboard', 'usb-keyboard', 'Standard full-size USB keyboard'],
            ['ELEC-003', 'Bluetooth Headphones', 'bluetooth-headphones', 'Over-ear wireless headphones with deep bass'],
            ['ELEC-004', 'Smart Watch', 'smart-watch', 'Fitness tracking smart watch with heart-rate monitor'],
            ['ELEC-005', 'Power Bank 20000mAh', 'power-bank-20000mah', 'High capacity fast-charging power bank'],

            ['FASH-001', 'Cotton T-Shirt', 'cotton-t-shirt', 'Comfortable 100% cotton T-shirt'],
            ['FASH-002', 'Casual Shirt', 'casual-shirt', 'Casual wear shirt for everyday use'],
            ['FASH-003', 'Denim Jeans', 'denim-jeans', 'Classic fit stretch denim jeans'],
            ['FASH-004', 'Running Shoes', 'running-shoes', 'Lightweight breathable running shoes'],
            ['FASH-005', 'Leather Handbag', 'leather-handbag', 'Genuine leather handbag'],

            ['GROC-001', 'Rice 5kg', 'rice-5kg', 'Premium quality long-grain rice'],
            ['GROC-002', 'Cooking Oil 1L', 'cooking-oil-1l', 'Refined cooking oil'],
            ['GROC-003', 'Fresh Eggs (12)', 'fresh-eggs-12', 'Farm fresh eggs, pack of 12'],
            ['GROC-004', 'Tea Leaves 500g', 'tea-leaves-500g', 'Strong aromatic black tea leaves'],
            ['GROC-005', 'Mango Juice 1L', 'mango-juice-1l', 'Natural mango juice'],
        ];

        foreach ($products as [$sku, $name, $slug, $description]) {
            Product::firstOrCreate(
                ['sku' => $sku],
                ['name' => $name, 'slug' => $slug, 'description' => $description],
            );
        }
    }
}
