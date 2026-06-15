<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GRNApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_grn_can_be_created_as_draft(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view', 'purchase-grn.manage']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/grns', $this->payload($data));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Draft')
            ->assertJsonPath('data.total_qty', 10)
            ->assertJsonPath('data.total_amount', 1250);

        $this->assertDatabaseHas('grn_master', [
            'tenant_id' => $data['tenant_id'],
            'branch_id' => $data['branch_id'],
            'grn_no' => 'GRN-001',
            'status' => 'Draft',
        ]);
    }

    public function test_grn_can_be_created_with_multiple_item_lines(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view', 'purchase-grn.manage']);
        Sanctum::actingAs($user);

        $secondItemId = DB::table('item_master')->insertGetId([
            'tenant_id' => $data['tenant_id'],
            'item_code' => 'PLY',
            'item_name' => 'Plywood Sheet',
            'item_type' => 'Raw Material',
            'uom_id' => $data['uom_id'],
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = $this->payload($data);
        $payload['details'][] = [
            'item_id' => $secondItemId,
            'uom_id' => $data['uom_id'],
            'location_id' => $data['location_id'],
            'qty' => 5,
            'rate' => 80,
        ];

        $response = $this->postJson('/api/v1/grns', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.total_qty', 15)
            ->assertJsonPath('data.total_amount', 1650);

        $this->assertDatabaseCount('grn_detail', 2);
    }

    public function test_grn_accepts_erp_transaction_fields_and_auto_number(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view', 'purchase-grn.manage']);
        Sanctum::actingAs($user);

        $payload = $this->payload($data);
        unset($payload['grn_no']);
        $payload['purchase_order_ref'] = 'PO-2026-10';
        $payload['warehouse_location_id'] = $data['location_id'];
        $payload['received_by'] = 'Store Keeper';
        $payload['freight_charges'] = 100;
        $payload['other_charges'] = 50;
        $payload['attachments'] = [['name' => 'invoice.pdf', 'type' => 'application/pdf']];
        $payload['details'][0]['ordered_qty'] = 12;
        $payload['details'][0]['received_qty'] = 10;
        $payload['details'][0]['rejected_qty'] = 2;
        $payload['details'][0]['accepted_qty'] = 8;
        $payload['details'][0]['discount_amount'] = 25;
        $payload['details'][0]['tax_amount'] = 45;

        $response = $this->postJson('/api/v1/grns', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.purchase_order_ref', 'PO-2026-10')
            ->assertJsonPath('data.received_by', 'Store Keeper')
            ->assertJsonPath('data.total_qty', 8)
            ->assertJsonPath('data.discount_amount', 25)
            ->assertJsonPath('data.tax_amount', 45)
            ->assertJsonPath('data.freight_charges', 100)
            ->assertJsonPath('data.other_charges', 50)
            ->assertJsonPath('data.grand_total', 1170);

        $this->assertStringStartsWith('GRN-', $response->json('data.grn_no'));
    }

    public function test_next_grn_number_can_be_previewed(): void
    {
        [$user] = $this->fixture(['purchase-grn.view']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/grns/next-number')
            ->assertOk()
            ->assertJsonPath('data.grn_no', 'GRN-2026-000001');
    }

    public function test_updating_grn_preserves_existing_grn_number(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view', 'purchase-grn.manage']);
        Sanctum::actingAs($user);

        $grn = $this->postJson('/api/v1/grns', $this->payload($data))->json('data');
        $payload = $this->payload($data);
        unset($payload['grn_no']);
        $payload['remarks'] = 'Edited remarks';

        $this->putJson("/api/v1/grns/{$grn['grn_id']}", $payload)
            ->assertOk()
            ->assertJsonPath('data.grn_no', $grn['grn_no'])
            ->assertJsonPath('data.remarks', 'Edited remarks');
    }


    public function test_posting_grn_creates_ledger_summary_and_audit_records(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view', 'purchase-grn.manage']);
        Sanctum::actingAs($user);

        $grnId = $this->postJson('/api/v1/grns', $this->payload($data))->json('data.grn_id');
        $response = $this->postJson("/api/v1/grns/{$grnId}/post");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Posted');

        $this->assertDatabaseHas('stock_ledger', [
            'tenant_id' => $data['tenant_id'],
            'branch_id' => $data['branch_id'],
            'item_id' => $data['item_id'],
            'location_id' => $data['location_id'],
            'reference_id' => $grnId,
            'reference_type' => 'GRN',
            'transaction_type' => 'GRN',
        ]);

        $this->assertDatabaseHas('stock_summary', [
            'tenant_id' => $data['tenant_id'],
            'branch_id' => $data['branch_id'],
            'item_id' => $data['item_id'],
            'location_id' => $data['location_id'],
            'stock_qty' => 10,
        ]);

        $this->assertDatabaseHas('audit_log', [
            'tenant_id' => $data['tenant_id'],
            'table_name' => 'grn_master',
            'action_type' => 'post',
            'record_id' => $grnId,
        ]);
    }

    public function test_grn_cannot_be_posted_twice(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view', 'purchase-grn.manage']);
        Sanctum::actingAs($user);

        $grnId = $this->postJson('/api/v1/grns', $this->payload($data))->json('data.grn_id');
        $this->postJson("/api/v1/grns/{$grnId}/post")->assertOk();

        $this->postJson("/api/v1/grns/{$grnId}/post")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_cancelling_posted_grn_reverses_stock(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view', 'purchase-grn.manage']);
        Sanctum::actingAs($user);

        $grnId = $this->postJson('/api/v1/grns', $this->payload($data))->json('data.grn_id');
        $this->postJson("/api/v1/grns/{$grnId}/post")->assertOk();
        $this->postJson("/api/v1/grns/{$grnId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'Cancelled');

        $this->assertDatabaseHas('stock_ledger', [
            'reference_id' => $grnId,
            'reference_type' => 'GRN',
            'transaction_type' => 'GRN_CANCEL',
            'qty_out' => 10,
        ]);

        $this->assertDatabaseHas('stock_summary', [
            'item_id' => $data['item_id'],
            'location_id' => $data['location_id'],
            'stock_qty' => 0,
        ]);
    }

    public function test_grn_manage_permission_is_required_for_create(): void
    {
        [$user, $data] = $this->fixture(['purchase-grn.view']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/grns', $this->payload($data))->assertForbidden();
    }

    private function fixture(array $permissionNames): array
    {
        $tenantId = DB::table('tenant_master')->insertGetId([
            'tenant_code' => 'TEST',
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
            'name' => 'Inventory',
            'guard_name' => 'api',
        ]);

        foreach ($permissionNames as $permissionName) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $permissionName,
                'guard_name' => 'api',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('role_permissions')->insert([
                'role_id' => $role->id,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $user = User::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'login_id' => 'inventory',
            'password' => 'secret',
            'employee_code' => 'INV001',
            'full_name' => 'Inventory User',
            'role_id' => $role->id,
            'status' => 'Active',
        ]);

        $supplierId = DB::table('party_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'party_code' => 'SUP',
            'party_name' => 'Supplier',
            'party_type' => 'Supplier',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomId = DB::table('uom_master')->insertGetId([
            'tenant_id' => $tenantId,
            'uom_code' => 'PCS',
            'uom_name' => 'Pieces',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemId = DB::table('item_master')->insertGetId([
            'tenant_id' => $tenantId,
            'item_code' => 'WOOD',
            'item_name' => 'Wood Plank',
            'item_type' => 'Raw Material',
            'uom_id' => $uomId,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('storage_location_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'location_code' => 'RM',
            'location_name' => 'Raw Store',
            'location_type' => 'RM',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, compact('tenantId', 'branchId', 'supplierId', 'uomId', 'itemId', 'locationId') + [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'supplier_id' => $supplierId,
            'uom_id' => $uomId,
            'item_id' => $itemId,
            'location_id' => $locationId,
        ]];
    }

    private function payload(array $data): array
    {
        return [
            'branch_id' => $data['branch_id'],
            'supplier_id' => $data['supplier_id'],
            'grn_no' => 'GRN-001',
            'grn_date' => '2026-06-09',
            'remarks' => 'Initial inward stock',
            'details' => [
                [
                    'item_id' => $data['item_id'],
                    'uom_id' => $data['uom_id'],
                    'location_id' => $data['location_id'],
                    'qty' => 10,
                    'rate' => 125,
                ],
            ],
        ];
    }
}
