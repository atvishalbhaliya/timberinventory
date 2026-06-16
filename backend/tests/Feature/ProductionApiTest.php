<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_production_can_be_posted_and_cancelled_with_stock_reversal(): void
    {
        [$user, $data] = $this->fixture(['production.view', 'production.manage', 'production.post', 'production.cancel', 'bom.view', 'bom.manage']);
        Sanctum::actingAs($user);

        $productionId = $this->postJson('/api/v1/production', $this->payload($data))->assertCreated()->json('data.production_id');

        $this->postJson("/api/v1/production/{$productionId}/post")
            ->assertOk()
            ->assertJsonPath('data.status', 'Posted');

        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['raw_item_id'], 'location_id' => $data['rm_location_id'], 'stock_type' => 'Fresh', 'stock_qty' => 14]);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['fg_item_id'], 'location_id' => $data['fg_location_id'], 'stock_qty' => 5]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Production', 'reference_id' => $productionId, 'transaction_type' => 'Production Consumption', 'stock_type' => 'Fresh', 'qty_out' => 5]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Production', 'reference_id' => $productionId, 'transaction_type' => 'Production Output', 'stock_type' => 'Fresh', 'qty_in' => 5]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Production', 'reference_id' => $productionId, 'transaction_type' => 'Production Output', 'labour_charge' => 75]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Production', 'reference_id' => $productionId, 'transaction_type' => 'Production Wastage', 'stock_type' => 'Fresh', 'qty_out' => 1]);
        $this->assertDatabaseHas('team_ledger', ['team_id' => $data['team_id'], 'transaction_type' => 'Production', 'qty' => 5]);
        $this->assertDatabaseHas('production_wastage', ['production_id' => $productionId, 'item_id' => $data['raw_item_id'], 'location_id' => $data['wastage_location_id'], 'qty' => 1]);
        $this->assertDatabaseHas('wastage_stock', ['item_id' => $data['raw_item_id'], 'location_id' => $data['wastage_location_id'], 'source_module' => 'Production', 'source_reference' => 'PROD-001', 'generated_qty' => 1, 'available_qty' => 1, 'status' => 'Posted']);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['raw_item_id'], 'location_id' => $data['wastage_location_id'], 'stock_type' => 'Fresh', 'stock_qty' => 1]);

        $this->postJson("/api/v1/production/{$productionId}/cancel", ['reason' => 'Wrong shift'])
            ->assertOk()
            ->assertJsonPath('data.status', 'Cancelled');

        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['raw_item_id'], 'location_id' => $data['rm_location_id'], 'stock_type' => 'Fresh', 'stock_qty' => 20]);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['fg_item_id'], 'location_id' => $data['fg_location_id'], 'stock_qty' => 0]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Production', 'reference_id' => $productionId, 'transaction_type' => 'Production Consumption Reversal', 'qty_in' => 5]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Production', 'reference_id' => $productionId, 'transaction_type' => 'Production Output Reversal', 'qty_out' => 5]);
        $this->assertDatabaseHas('stock_ledger', ['reference_type' => 'Production', 'reference_id' => $productionId, 'transaction_type' => 'Production Wastage Reversal', 'stock_type' => 'Fresh', 'qty_in' => 1]);
        $this->assertDatabaseHas('wastage_stock', ['item_id' => $data['raw_item_id'], 'location_id' => $data['wastage_location_id'], 'generated_qty' => 1, 'status' => 'Cancelled']);
        $this->assertDatabaseHas('stock_summary', ['item_id' => $data['raw_item_id'], 'location_id' => $data['wastage_location_id'], 'stock_type' => 'Fresh', 'stock_qty' => 0]);
        $this->assertDatabaseHas('team_ledger', ['team_id' => $data['team_id'], 'transaction_type' => 'Production', 'qty' => -5]);
    }

    public function test_duplicate_production_post_is_rejected(): void
    {
        [$user, $data] = $this->fixture(['production.view', 'production.manage', 'production.post']);
        Sanctum::actingAs($user);

        $productionId = $this->postJson('/api/v1/production', $this->payload($data))->json('data.production_id');
        $this->postJson("/api/v1/production/{$productionId}/post")->assertOk();

        $this->postJson("/api/v1/production/{$productionId}/post")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_production_post_prevents_negative_inventory(): void
    {
        [$user, $data] = $this->fixture(['production.view', 'production.manage', 'production.post']);
        Sanctum::actingAs($user);

        DB::table('stock_summary')->where('stock_id', $data['stock_id'])->update(['stock_qty' => 1]);

        $productionId = $this->postJson('/api/v1/production', $this->payload($data))->json('data.production_id');

        $response = $this->postJson("/api/v1/production/{$productionId}/post");
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('stock');
        $this->assertStringContainsString('Wood Plank', (string) $response->json('errors.stock.0'));
        $this->assertStringContainsString('Required:', (string) $response->json('errors.stock.0'));
        $this->assertStringContainsString('Available:', (string) $response->json('errors.stock.0'));
    }

    public function test_production_post_permission_is_required(): void
    {
        [$user, $data] = $this->fixture(['production.view', 'production.manage']);
        Sanctum::actingAs($user);

        $productionId = $this->postJson('/api/v1/production', $this->payload($data))->json('data.production_id');

        $this->postJson("/api/v1/production/{$productionId}/post")->assertForbidden();
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
        $rawItemId = DB::table('item_master')->insertGetId(['tenant_id' => $tenantId, 'item_code' => 'WOOD', 'item_name' => 'Wood Plank', 'item_type' => 'Raw Material', 'uom_id' => $uomId, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $fgItemId = DB::table('item_master')->insertGetId(['tenant_id' => $tenantId, 'item_code' => 'PALLET', 'item_name' => 'Finished Pallet', 'item_type' => 'Finish Product', 'uom_id' => $uomId, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $rmLocationId = DB::table('storage_location_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'location_code' => 'RM', 'location_name' => 'Raw Store', 'location_type' => 'RM', 'created_at' => now(), 'updated_at' => now()]);
        $fgLocationId = DB::table('storage_location_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'location_code' => 'FG', 'location_name' => 'Finished Store', 'location_type' => 'FG', 'created_at' => now(), 'updated_at' => now()]);
        $wastageLocationId = DB::table('storage_location_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'location_code' => 'WST', 'location_name' => 'Wastage Store', 'location_type' => 'WASTAGE', 'created_at' => now(), 'updated_at' => now()]);
        $teamId = DB::table('team_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'team_code' => 'T1', 'team_name' => 'Team One', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $palletModelId = DB::table('pallet_model_master')->insertGetId(['tenant_id' => $tenantId, 'model_code' => 'PAL', 'model_name' => 'Pallet Model', 'created_at' => now(), 'updated_at' => now()]);
        $bomId = DB::table('bom_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'bom_no' => 'BOM-001', 'bom_name' => 'Pallet BOM', 'pallet_model_id' => $palletModelId, 'version_no' => 'V1', 'is_active' => true, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('bom_material')->insert(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'bom_id' => $bomId, 'item_id' => $rawItemId, 'uom_id' => $uomId, 'required_qty' => 1, 'wastage_percent' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $stockId = DB::table('stock_summary')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'item_id' => $rawItemId, 'location_id' => $rmLocationId, 'stock_qty' => 20, 'avg_rate' => 10, 'created_at' => now(), 'updated_at' => now()], 'stock_id');

        return [$user, compact('branchId', 'uomId', 'rawItemId', 'fgItemId', 'rmLocationId', 'fgLocationId', 'wastageLocationId', 'teamId', 'palletModelId', 'bomId', 'stockId') + [
            'branch_id' => $branchId,
            'uom_id' => $uomId,
            'raw_item_id' => $rawItemId,
            'fg_item_id' => $fgItemId,
            'rm_location_id' => $rmLocationId,
            'fg_location_id' => $fgLocationId,
            'wastage_location_id' => $wastageLocationId,
            'team_id' => $teamId,
            'pallet_model_id' => $palletModelId,
            'bom_id' => $bomId,
            'stock_id' => $stockId,
        ]];
    }

    private function payload(array $data): array
    {
        return [
            'branch_id' => $data['branch_id'],
            'production_no' => 'PROD-001',
            'production_date' => '2026-06-10',
            'bom_id' => $data['bom_id'],
            'pallet_model_id' => $data['pallet_model_id'],
            'produced_item_id' => $data['fg_item_id'],
            'team_id' => $data['team_id'],
            'fg_location_id' => $data['fg_location_id'],
            'produced_qty' => 5,
            'production_cost' => 250,
            'labour_charge' => 75,
            'consumptions' => [[
                'item_id' => $data['raw_item_id'],
                'uom_id' => $data['uom_id'],
                'location_id' => $data['rm_location_id'],
                'required_qty' => 5,
                'consumed_qty' => 5,
                'wastage_qty' => 1,
            ]],
        ];
    }
}
