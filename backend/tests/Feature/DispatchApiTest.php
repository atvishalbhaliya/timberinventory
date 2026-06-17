<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DispatchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_dispatch_challan_posts_team_ledger_qty_and_amount_and_keeps_it_in_sync(): void
    {
        [$user, $data] = $this->fixture(['dispatch.view', 'dispatch.manage']);
        Sanctum::actingAs($user);
        $month = (int) now()->month;
        $year = (int) now()->year;

        $payload = $this->payload($data, 4);
        $challanId = $this->postJson('/api/v1/dispatch/challans', $payload)
            ->assertCreated()
            ->json('data.challan_id');

        $this->assertDatabaseHas('team_ledger', [
            'team_id' => $data['team_id'],
            'transaction_type' => 'Dispatch',
            'reference_type' => 'Dispatch Challan',
            'reference_id' => $challanId,
            'qty' => 4,
            'amount' => 100,
        ]);
        $this->assertDatabaseHas('team_payment_summary', [
            'team_id' => $data['team_id'],
            'payment_month' => $month,
            'payment_year' => $year,
            'dispatch_qty' => 4,
            'gross_amount' => 100,
            'tds_amount' => 0,
            'net_payable' => 100,
        ]);

        $this->assertDatabaseCount('team_ledger', 1);

        $updatedPayload = $this->payload($data, 6);
        $this->putJson("/api/v1/dispatch/challans/{$challanId}", $updatedPayload)
            ->assertOk();

        $this->assertDatabaseHas('team_ledger', [
            'team_id' => $data['team_id'],
            'transaction_type' => 'Dispatch',
            'reference_type' => 'Dispatch Challan',
            'reference_id' => $challanId,
            'qty' => 6,
            'amount' => 150,
        ]);
        $this->assertDatabaseHas('team_payment_summary', [
            'team_id' => $data['team_id'],
            'payment_month' => $month,
            'payment_year' => $year,
            'dispatch_qty' => 6,
            'gross_amount' => 150,
            'tds_amount' => 0,
            'net_payable' => 150,
        ]);
        $this->assertDatabaseMissing('team_ledger', [
            'team_id' => $data['team_id'],
            'transaction_type' => 'Dispatch',
            'reference_type' => 'Dispatch Challan',
            'reference_id' => $challanId,
            'qty' => 4,
            'amount' => 100,
        ]);
        $this->assertDatabaseCount('team_ledger', 1);

        $this->deleteJson("/api/v1/dispatch/challans/{$challanId}")
            ->assertOk();

        $this->assertDatabaseMissing('team_ledger', [
            'reference_type' => 'Dispatch Challan',
            'reference_id' => $challanId,
        ]);
        $this->assertDatabaseMissing('team_payment_summary', [
            'team_id' => $data['team_id'],
            'payment_month' => $month,
            'payment_year' => $year,
        ]);
        $this->assertDatabaseCount('team_ledger', 0);
    }

    public function test_dispatch_challan_uses_passed_labour_rate_when_provided(): void
    {
        [$user, $data] = $this->fixture(['dispatch.view', 'dispatch.manage']);
        Sanctum::actingAs($user);

        $payload = $this->payload($data, 4, 40);
        $challanId = $this->postJson('/api/v1/dispatch/challans', $payload)
            ->assertCreated()
            ->json('data.challan_id');

        $this->assertDatabaseHas('team_ledger', [
            'team_id' => $data['team_id'],
            'transaction_type' => 'Dispatch',
            'reference_type' => 'Dispatch Challan',
            'reference_id' => $challanId,
            'qty' => 4,
            'amount' => 160,
        ]);
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
            'name' => 'Dispatch',
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

        $user = User::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'login_id' => 'dispatch',
            'password' => 'secret',
            'full_name' => 'Dispatch User',
            'role_id' => $role->id,
            'status' => 'Active',
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
            'item_code' => 'FG-001',
            'item_name' => 'Finished Product',
            'item_type' => 'Finish Product',
            'uom_id' => $uomId,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $locationId = DB::table('storage_location_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'location_code' => 'FG',
            'location_name' => 'Finished Store',
            'location_type' => 'FG',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $customerId = DB::table('party_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'party_code' => 'CUS-001',
            'party_name' => 'Customer One',
            'party_type' => 'Customer',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $teamId = DB::table('team_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'team_code' => 'T-01',
            'team_name' => 'Team One',
            'rate_per_pallet' => 25,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stock_summary')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'item_id' => $itemId,
            'location_id' => $locationId,
            'stock_type' => 'Fresh',
            'stock_qty' => 20,
            'avg_rate' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'location_id' => $locationId,
            'item_id' => $itemId,
            'team_id' => $teamId,
        ]];
    }

    private function payload(array $data, float $qty, ?float $labourRate = null): array
    {
        $line = [
            'item_id' => $data['item_id'],
            'team_id' => $data['team_id'],
            'qty' => $qty,
        ];

        if ($labourRate !== null) {
            $line['labour_rate'] = $labourRate;
        }

        return [
            'challan_date' => now()->toDateString(),
            'customer_id' => $data['customer_id'],
            'source_location_id' => $data['location_id'],
            'vehicle_no' => 'MH12AB1234',
            'driver_name' => 'Driver One',
            'destination' => 'Customer Site',
            'team_details' => [$line],
        ];
    }
}
