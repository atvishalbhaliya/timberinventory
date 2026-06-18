<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RolePermissionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_navigation_is_filtered_by_role_permissions(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view', 'roles.view']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard/navigation');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.title', 'Dashboard');

        $titles = collect($response->json('data.items'))->pluck('title');

        $this->assertTrue($titles->contains('Roles'));
        $this->assertFalse($titles->contains('Production'));
    }

    public function test_inventory_stock_navigation_items_require_matching_view_permissions(): void
    {
        $user = $this->createUserWithPermissions([
            'purchase-grn.view',
            'stock-ledger.view',
            'stock-summary.view',
            'overall-stock-summary.view',
            'stock-verification.view',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard/navigation');

        $response->assertOk();

        $inventoryItems = collect($response->json('data.items'))
            ->where('section', 'Inventory')
            ->map(fn (array $item): array => [
                'title' => $item['title'],
                'path' => $item['path'],
                'permission' => $item['permission'],
            ])
            ->values()
            ->all();

        $this->assertSame([
            ['title' => 'GRN', 'path' => '/grn', 'permission' => 'purchase-grn.view'],
            ['title' => 'Stock Ledger', 'path' => '/stock-ledger', 'permission' => 'stock-ledger.view'],
            ['title' => 'Stock Summary', 'path' => '/stock-summary', 'permission' => 'stock-summary.view'],
            ['title' => 'OverAll Stock Summary', 'path' => '/overall-stock-summary', 'permission' => 'overall-stock-summary.view'],
            ['title' => 'Finished Product Stock Summary', 'path' => '/finished-product-stock-summary', 'permission' => 'overall-stock-summary.view'],
            ['title' => 'Stock Verification', 'path' => '/stock-verification', 'permission' => 'stock-verification.view'],
        ], $inventoryItems);

        $blocked = $this->createUserWithPermissions(['purchase-grn.view']);

        Sanctum::actingAs($blocked);

        $titles = collect($this->getJson('/api/v1/dashboard/navigation')->json('data.items'))->pluck('title');

        $this->assertTrue($titles->contains('GRN'));
        $this->assertFalse($titles->contains('Stock Ledger'));
        $this->assertFalse($titles->contains('Stock Summary'));
        $this->assertFalse($titles->contains('OverAll Stock Summary'));
        $this->assertFalse($titles->contains('Finished Product Stock Summary'));
        $this->assertFalse($titles->contains('Stock Verification'));
    }

    public function test_role_permission_mapping_requires_manage_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view', 'roles.view']);
        $targetRole = Role::query()->create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => null,
            'name' => 'Store',
            'guard_name' => 'api',
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/admin/roles/{$targetRole->id}/permissions", [
            'permission_ids' => [],
        ])->assertForbidden();
    }

    public function test_role_permissions_can_be_synced(): void
    {
        $user = $this->createUserWithPermissions(['role-permissions.manage']);
        $targetRole = Role::query()->create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => null,
            'name' => 'Production',
            'guard_name' => 'api',
        ]);
        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'production.view',
            'guard_name' => 'api',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/admin/roles/{$targetRole->id}/permissions", [
            'permission_ids' => [$permissionId],
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.permission_ids.0', $permissionId);

        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $targetRole->id,
            'permission_id' => $permissionId,
        ]);
    }

    private function createUserWithPermissions(array $permissionNames): User
    {
        $tenantId = DB::table('tenant_master')->insertGetId([
            'tenant_code' => uniqid('TEST'),
            'tenant_name' => 'Test Tenant',
            'company_name' => 'Test Tenant',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $branchId = DB::table('branch_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_code' => 'MAIN',
            'branch_name' => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $role = Role::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => 'Tester',
            'guard_name' => 'api',
        ]);

        foreach ($permissionNames as $permissionName) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permissionName],
                ['guard_name' => 'api', 'deleted_at' => null, 'created_at' => now(), 'updated_at' => now()]
            );

            $permissionId = DB::table('permissions')->where('name', $permissionName)->value('id');

            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $role->id,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return User::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => null,
            'login_id' => 'tester',
            'password' => 'secret',
            'employee_code' => 'T001',
            'full_name' => 'Test User',
            'role_id' => $role->id,
            'status' => 'Active',
        ]);
    }
}
