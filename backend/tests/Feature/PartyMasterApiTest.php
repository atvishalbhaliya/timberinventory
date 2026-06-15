<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PartyMasterApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_party_codes_are_generated_by_type_and_status_is_system_managed(): void
    {
        $user = $this->userWithMasterPermissions();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/parties/next-code?party_type=Customer')
            ->assertOk()
            ->assertJsonPath('data.party_code', 'C00001');

        $customer = $this->postJson('/api/v1/parties', [
            'party_code' => 'MANUAL',
            'party_type' => 'Customer',
            'party_name' => 'ABC Customer',
            'contact_person' => 'Anil',
            'mobile' => '9999999999',
            'email' => 'abc@example.test',
            'address' => 'Industrial Area',
            'state' => 'Gujarat',
            'gst_no' => 'GST001',
            'pan_no' => 'PAN001',
            'remarks' => 'Preferred customer',
            'status' => 'Inactive',
            'city' => 'Ignored',
            'country' => 'Ignored',
            'credit_days' => 90,
            'credit_limit' => 99999,
        ])->assertCreated()->json('data');

        $supplier = $this->postJson('/api/v1/parties', [
            'party_type' => 'Supplier',
            'party_name' => 'XYZ Supplier',
            'state' => 'Gujarat',
        ])->assertCreated()->json('data');

        $this->assertSame('C00001', $customer['party_code']);
        $this->assertSame('S00001', $supplier['party_code']);

        $this->assertDatabaseHas('party_master', [
            'party_name' => 'ABC Customer',
            'party_code' => 'C00001',
            'party_type' => 'Customer',
            'status' => 'Active',
            'remarks' => 'Preferred customer',
        ]);

        $this->assertDatabaseMissing('party_master', [
            'party_code' => 'MANUAL',
        ]);
    }

    private function userWithMasterPermissions(): User
    {
        $tenantId = DB::table('tenant_master')->insertGetId([
            'tenant_name' => 'Test Tenant',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $branchId = DB::table('branch_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_name' => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $role = Role::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => 'Masters Tester',
            'guard_name' => 'api',
        ]);

        foreach (['masters.view', 'masters.manage'] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission],
                ['guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()]
            );
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $role->id,
                'permission_id' => DB::table('permissions')->where('name', $permission)->value('id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return User::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'login_id' => 'party-user',
            'password' => 'secret',
            'full_name' => 'Party User',
            'role_id' => $role->id,
            'status' => 'Active',
        ]);
    }
}
