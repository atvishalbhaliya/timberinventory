<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WastageReuseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_wastage_reuse_can_be_drafted_posted_and_cancelled(): void
    {
        [$user, $data] = $this->fixture(['wastage-reuse.view', 'wastage-reuse.manage', 'wastage-reuse.post', 'wastage-reuse.cancel']);
        Sanctum::actingAs($user);

        $reuseId = $this->postJson('/api/v1/wastage-reuse', $this->payload($data, 2))
            ->assertCreated()
            ->assertJsonPath('data.status', 'Draft')
            ->json('data.reuse_id');

        $this->postJson("/api/v1/wastage-reuse/{$reuseId}/post")->assertOk()->assertJsonPath('data.status', 'Posted');
        $this->assertDatabaseHas('wastage_stock', ['wastage_stock_id' => $data['source_wastage_stock_id'], 'available_qty' => 3, 'used_qty' => 2]);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['wastage_item_id'], 'location_id' => $data['wastage_location_id'], 'stock_qty' => 3]);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['recovered_item_id'], 'location_id' => $data['fg_location_id'], 'stock_qty' => 1.5]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Wastage Reuse', 'reference_id' => $reuseId, 'transaction_type' => 'Wastage Reuse Consumption', 'qty_out' => 2]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Wastage Reuse', 'reference_id' => $reuseId, 'transaction_type' => 'Wastage Reuse Output', 'qty_in' => 1.5]);

        $this->postJson("/api/v1/wastage-reuse/{$reuseId}/cancel", ['reason' => 'Quality issue'])->assertOk()->assertJsonPath('data.status', 'Cancelled');
        $this->assertDatabaseHas('wastage_stock', ['wastage_stock_id' => $data['source_wastage_stock_id'], 'available_qty' => 5, 'used_qty' => 0]);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['wastage_item_id'], 'location_id' => $data['wastage_location_id'], 'stock_qty' => 5]);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['recovered_item_id'], 'location_id' => $data['fg_location_id'], 'stock_qty' => 0]);
    }

    public function test_wastage_reuse_post_rejects_insufficient_wastage_stock(): void
    {
        [$user, $data] = $this->fixture(['wastage-reuse.view', 'wastage-reuse.manage', 'wastage-reuse.post']);
        Sanctum::actingAs($user);

        $reuseId = $this->postJson('/api/v1/wastage-reuse', $this->payload($data, 6))->assertCreated()->json('data.reuse_id');

        $this->postJson("/api/v1/wastage-reuse/{$reuseId}/post")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consumed_qty');
    }

    public function test_wastage_reuse_draft_rejects_source_without_location(): void
    {
        [$user, $data] = $this->fixture(['wastage-reuse.view', 'wastage-reuse.manage']);
        Sanctum::actingAs($user);

        DB::table('wastage_stock')->where('wastage_stock_id', $data['source_wastage_stock_id'])->update(['location_id' => null]);

        $this->postJson('/api/v1/wastage-reuse', $this->payload($data, 2))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source_wastage_stock_id');
    }

    public function test_wastage_reuse_permission_is_required(): void
    {
        [$user] = $this->fixture([]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wastage-reuse')->assertForbidden();
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
        $recoveredItemId = DB::table('item_master')->insertGetId(['tenant_id' => $tenantId, 'item_code' => 'REC', 'item_name' => 'Recovered Wood', 'item_type' => 'Semi Product', 'uom_id' => $uomId, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $wastageLocationId = DB::table('storage_location_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'location_code' => 'WST', 'location_name' => 'Wastage Yard', 'location_type' => 'WASTAGE', 'created_at' => now(), 'updated_at' => now()]);
        $fgLocationId = DB::table('storage_location_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'location_code' => 'FG', 'location_name' => 'Finished Store', 'location_type' => 'FG', 'created_at' => now(), 'updated_at' => now()]);
        $teamId = DB::table('team_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'team_code' => 'T1', 'team_name' => 'Team One', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $sourceWastageStockId = DB::table('wastage_stock')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'item_id' => $wastageItemId,
            'location_id' => $wastageLocationId,
            'wastage_type' => 'Reusable',
            'source_module' => 'Production',
            'source_reference' => 'PROD-001',
            'transaction_date' => '2026-06-10',
            'generated_qty' => 5,
            'available_qty' => 5,
            'used_qty' => 0,
            'balance_qty' => 5,
            'status' => 'Posted',
            'posted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'wastage_stock_id');
        DB::table('stock_summary')->insert(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'item_id' => $wastageItemId, 'location_id' => $wastageLocationId, 'stock_type' => 'Wastage', 'stock_qty' => 5, 'avg_rate' => 0, 'created_at' => now(), 'updated_at' => now()]);

        return [$user, [
            'branch_id' => $branchId,
            'wastage_item_id' => $wastageItemId,
            'recovered_item_id' => $recoveredItemId,
            'wastage_location_id' => $wastageLocationId,
            'fg_location_id' => $fgLocationId,
            'team_id' => $teamId,
            'source_wastage_stock_id' => $sourceWastageStockId,
        ]];
    }

    private function payload(array $data, float $consumedQty): array
    {
        return [
            'reuse_date' => '2026-06-11',
            'source_wastage_stock_id' => $data['source_wastage_stock_id'],
            'consumed_qty' => $consumedQty,
            'produced_item_id' => $data['recovered_item_id'],
            'destination_location_id' => $data['fg_location_id'],
            'team_id' => $data['team_id'],
            'produced_qty' => 1.5,
            'production_cost' => 30,
        ];
    }
}
