<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = config('permissions.roles');
        $permissions = config('permissions.permissions');

        DB::transaction(function () use ($roles, $permissions): void {
            foreach (array_keys($roles) as $name) {
                Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }

            foreach (array_keys($permissions) as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }

            Role::findByName('admin', 'web')->syncPermissions(array_keys($permissions));

            Role::findByName('shop_owner', 'web')->syncPermissions([
                'view_dashboard',
                'view_catalog_products',
                'view_shop_products',
                'add_shop_products',
                'edit_shop_products',
                'remove_shop_products',
            ]);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
