<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Permission checks on admin routes now use policies + the #[Authorize] attribute,
 * so a user with the role but without the permission should get a 403.
 */
class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_permission_is_forbidden(): void
    {
        $admin = $this->adminWithPermissions([]);

        $this->actingAs($admin)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Phone', 'slug' => 'phone', 'sku' => 'PH-1',
        ])->assertForbidden();
    }

    public function test_permission_is_checked_per_action(): void
    {
        $admin = $this->adminWithPermissions(['view_catalog_products']);
        $product = Product::create(['name' => 'Phone', 'slug' => 'phone', 'sku' => 'PH-1']);

        $this->actingAs($admin)->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/products/Index'));

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Changed', 'slug' => 'phone', 'sku' => 'PH-1',
        ])->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))->assertForbidden();
        $this->assertModelExists($product);
    }

    public function test_frontend_only_receives_the_permissions_the_user_actually_has(): void
    {
        // The UI hides menu items and buttons using this list, so anything missing here is hidden
        $admin = $this->adminWithPermissions(['view_catalog_products', 'create_catalog_products']);

        $this->actingAs($admin)->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions', fn ($permissions) => collect($permissions)->sort()->values()->all()
                    === ['create_catalog_products', 'view_catalog_products']));
    }

    public function test_edit_permission_allows_updating(): void
    {
        $admin = $this->adminWithPermissions(['edit_catalog_products']);
        $product = Product::create(['name' => 'Phone', 'slug' => 'phone', 'sku' => 'PH-1']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Phone Pro', 'slug' => 'phone', 'sku' => 'PH-1',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertSame('Phone Pro', $product->refresh()->name);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function adminWithPermissions(array $permissions): User
    {
        $this->seed(PermissionSeeder::class);

        $admin = User::factory()->create();
        $role = Role::findByName('admin', 'web');
        $role->syncPermissions([]);
        $admin->assignRole($role);
        $admin->givePermissionTo($permissions);

        return $admin;
    }
}
