<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WastageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_manual_wastage_can_be_created_posted_listed_and_cancelled(): void
    {
        [$user, $data] = $this->fixture(['wastage.view', 'wastage.manage', 'wastage.post', 'wastage.cancel']);
        Sanctum::actingAs($user);

        $id = $this->postJson('/api/v1/wastage', [
            'transaction_date' => '2026-06-10',
            'item_id' => $data['wastage_item_id'],
            'location_id' => $data['wastage_location_id'],
            'wastage_type' => 'Reusable',
            'generated_qty' => 3,
            'source_reference' => 'MAN-001',
        ])->assertCreated()->assertJsonPath('data.status', 'Draft')->json('data.wastage_stock_id');

        $this->getJson('/api/v1/wastage?status=Draft')->assertOk()->assertJsonPath('data.data.0.wastage_stock_id', $id);

        $this->postJson("/api/v1/wastage/{$id}/post")->assertOk()->assertJsonPath('data.status', 'Posted');
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['wastage_item_id'], 'location_id' => $data['wastage_location_id'], 'stock_qty' => 3]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Wastage', 'reference_id' => $id, 'transaction_type' => 'Wastage Adjustment', 'qty_in' => 3]);

        $this->postJson("/api/v1/wastage/{$id}/cancel", ['reason' => 'Bad entry'])->assertOk()->assertJsonPath('data.status', 'Cancelled');
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['wastage_item_id'], 'location_id' => $data['wastage_location_id'], 'stock_qty' => 0]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Wastage', 'reference_id' => $id, 'transaction_type' => 'Wastage Reversal', 'qty_out' => 3]);
    }

    public function test_wastage_permission_is_required(): void
    {
        [$user] = $this->fixture([]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wastage')->assertForbidden();
    }

    private function fixture(array $permissionNames): array
    {
        $tenantId = DB::table('tenant_master')->insertGetId(['tenant_code' => 'TEST', 'tenant_name' => 'Test Tenant', 'company_name' => 'Test Tenant', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $branchId = DB::table('branch_master')->insertGetId(['tenant_id' => $tenantId, 'branch_code' => 'MAIN', 'branch_name' => 'Main', 'created_at' => now(), 'updated_at' => now()]);
        $role = Role::query()->create(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'name' => 'Production', 'guard_name' => 'api']);

        foreach ($permissionNames as $permissionName) {
            DB::table('permissions')->updateOrInsert(['name' => $permissionName], ['guard_name' => 'api', 'deleted_at' => null, 'created_at' => now(), 'updated_at' => now()]);
            $permissionId = DB::table('permissions')->where('name', $permissionName)->value('id');
            DB::table('role_permissions')->insertOrIgnore(['role_id' => $role->id, 'permission_id' => $permissionId, 'created_at' => now(), 'updated_at' => now()]);
        }

        $user = User::query()->create(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'login_id' => 'production', 'password' => 'secret', 'full_name' => 'Production User', 'role_id' => $role->id, 'status' => 'Active']);
        $uomId = DB::table('uom_master')->insertGetId(['tenant_id' => $tenantId, 'uom_code' => 'PCS', 'uom_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);
        $wastageItemId = DB::table('item_master')->insertGetId(['tenant_id' => $tenantId, 'item_code' => 'WST', 'item_name' => 'Reusable Wastage', 'item_type' => 'Wastage', 'uom_id' => $uomId, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $wastageLocationId = DB::table('storage_location_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'location_code' => 'WST', 'location_name' => 'Wastage Yard', 'location_type' => 'WASTAGE', 'created_at' => now(), 'updated_at' => now()]);

        return [$user, [
            'branch_id' => $branchId,
            'wastage_item_id' => $wastageItemId,
            'wastage_location_id' => $wastageLocationId,
        ]];
    }
}
