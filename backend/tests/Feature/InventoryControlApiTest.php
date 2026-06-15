<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryControlApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_stock_ledger_listing_is_permission_protected_and_scoped(): void
    {
        [$user, $data] = $this->fixture(['stock-ledger.view']);
        $this->seedStock($data, 20);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/stock-ledger')
            ->assertOk()
            ->assertJsonPath('data.data.0.item_name', 'Wood Plank')
            ->assertJsonPath('data.data.0.running_balance', 20);

        [$blocked] = $this->fixture([]);
        Sanctum::actingAs($blocked);
        $this->getJson('/api/v1/stock-ledger')->assertForbidden();
    }

    public function test_stock_summary_calculates_current_stock_status_inputs(): void
    {
        [$user, $data] = $this->fixture(['stock-summary.view']);
        $this->seedStock($data, 20);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/stock-summary')
            ->assertOk()
            ->assertJsonPath('data.data.0.item_name', 'Wood Plank')
            ->assertJsonPath('data.data.0.available_qty', 20)
            ->assertJsonPath('metrics.available_stock', 20);
    }

    public function test_stock_verification_crud_and_approval_creates_adjustment_ledger(): void
    {
        [$user, $data] = $this->fixture([
            'stock-verification.view',
            'stock-verification.create',
            'stock-verification.edit',
            'stock-verification.submit',
            'stock-verification.approve',
        ]);
        $this->seedStock($data, 20);
        Sanctum::actingAs($user);

        $payload = [
            'branch_id' => $data['branch_id'],
            'location_id' => $data['location_id'],
            'verification_date' => '2026-06-09',
            'remarks' => 'Cycle count',
            'details' => [[
                'item_id' => $data['item_id'],
                'uom_id' => $data['uom_id'],
                'system_qty' => 20,
                'physical_qty' => 17,
            ]],
        ];

        $verificationId = $this->postJson('/api/v1/stock-verifications', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'Draft')
            ->assertJsonPath('data.details.0.variance_qty', -3)
            ->json('data.verification_id');

        $this->putJson("/api/v1/stock-verifications/{$verificationId}", array_replace($payload, ['remarks' => 'Updated']))
            ->assertOk()
            ->assertJsonPath('data.remarks', 'Updated');

        $this->postJson("/api/v1/stock-verifications/{$verificationId}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'Submitted');

        $this->postJson("/api/v1/stock-verifications/{$verificationId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'Completed');

        $this->assertDatabaseHas('stock_ledger', [
            'reference_type' => 'ADJUSTMENT',
            'transaction_type' => 'ADJUSTMENT',
            'qty_out' => 3,
        ]);
        $this->assertDatabaseHas('stock_summary', [
            'item_id' => $data['item_id'],
            'location_id' => $data['location_id'],
            'stock_qty' => 17,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'table_name' => 'stock_verification_master',
            'action_type' => 'approve',
        ]);
    }

    public function test_stock_verification_cancel_before_approval(): void
    {
        [$user, $data] = $this->fixture(['stock-verification.view', 'stock-verification.create', 'stock-verification.cancel']);
        Sanctum::actingAs($user);

        $id = $this->postJson('/api/v1/stock-verifications', [
            'branch_id' => $data['branch_id'],
            'location_id' => $data['location_id'],
            'verification_date' => '2026-06-09',
            'details' => [[
                'item_id' => $data['item_id'],
                'uom_id' => $data['uom_id'],
                'system_qty' => 0,
                'physical_qty' => 0,
            ]],
        ])->json('data.verification_id');

        $this->postJson("/api/v1/stock-verifications/{$id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'Cancelled');
    }

    private function seedStock(array $data, float $qty): void
    {
        DB::table('stock_summary')->insert([
            'tenant_id' => $data['tenant_id'],
            'branch_id' => $data['branch_id'],
            'item_id' => $data['item_id'],
            'location_id' => $data['location_id'],
            'stock_qty' => $qty,
            'avg_rate' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stock_ledger')->insert([
            'tenant_id' => $data['tenant_id'],
            'branch_id' => $data['branch_id'],
            'item_id' => $data['item_id'],
            'location_id' => $data['location_id'],
            'transaction_date' => '2026-06-09 00:00:00',
            'transaction_type' => 'GRN',
            'reference_id' => 1,
            'reference_type' => 'GRN',
            'qty_in' => $qty,
            'qty_out' => 0,
            'balance_qty' => $qty,
            'rate' => 0,
            'amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function fixture(array $permissions): array
    {
        $tenantId = DB::table('tenant_master')->insertGetId(['tenant_name' => uniqid('Tenant '), 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $branchId = DB::table('branch_master')->insertGetId(['tenant_id' => $tenantId, 'branch_name' => 'Main', 'created_at' => now(), 'updated_at' => now()]);
        $role = Role::query()->create(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'name' => uniqid('Role '), 'guard_name' => 'api']);
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission],
                ['guard_name' => 'api', 'deleted_at' => null, 'created_at' => now(), 'updated_at' => now()]
            );

            $permissionId = DB::table('permissions')->where('name', $permission)->value('id');

            DB::table('role_permissions')->insertOrIgnore(['role_id' => $role->id, 'permission_id' => $permissionId, 'created_at' => now(), 'updated_at' => now()]);
        }
        $user = User::query()->create(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'login_id' => uniqid('user'), 'password' => 'secret', 'full_name' => 'Inventory User', 'role_id' => $role->id, 'status' => 'Active']);
        $uomId = DB::table('uom_master')->insertGetId(['tenant_id' => $tenantId, 'uom_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);
        $typeId = DB::table('material_type_master')->insertGetId(['tenant_id' => $tenantId, 'material_type_name' => 'Timber', 'created_at' => now(), 'updated_at' => now()]);
        $itemId = DB::table('item_master')->insertGetId(['tenant_id' => $tenantId, 'item_name' => 'Wood Plank', 'item_code' => 'WOOD', 'item_type' => 'Raw Material', 'material_type_id' => $typeId, 'uom_id' => $uomId, 'minimum_stock' => 5, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $locationId = DB::table('storage_location_master')->insertGetId(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'location_name' => 'Raw Store', 'location_type' => 'RM', 'created_at' => now(), 'updated_at' => now()]);

        return [$user, ['tenant_id' => $tenantId, 'branch_id' => $branchId, 'uom_id' => $uomId, 'material_type_id' => $typeId, 'item_id' => $itemId, 'location_id' => $locationId]];
    }
}
