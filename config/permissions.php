
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | Initial system roles.
    | Additional roles can be created dynamically from Admin Dashboard.
    |
    */

    'roles' => [
        'admin' => 'Administrator',
        'shop_owner' => 'Shop Owner',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Initial system permissions.
    | Additional permissions can be created dynamically when corresponding
    | application features and authorization checks are implemented.
    |
    */

    'permissions' => [

        // Dashboard
        'view_dashboard' => 'View Dashboard',

        // User Management
        'view_users' => 'View Users',
        'create_users' => 'Create Users',
        'edit_users' => 'Edit Users',
        'delete_users' => 'Delete Users',

        // Shop Management
        'view_shops' => 'View Shops',
        'create_shops' => 'Create Shops',
        'edit_shops' => 'Edit Shops',
        'delete_shops' => 'Delete Shops',

        // Global Product Catalog
        'view_catalog_products' => 'View Catalog Products',
        'create_catalog_products' => 'Create Catalog Products',
        'edit_catalog_products' => 'Edit Catalog Products',
        'delete_catalog_products' => 'Delete Catalog Products',

        // Shop Product Management
        'view_shop_products' => 'View Shop Products',
        'add_shop_products' => 'Add Products to Shop',
        'edit_shop_products' => 'Edit Shop Products',
        'remove_shop_products' => 'Remove Shop Products',

    ],

];
