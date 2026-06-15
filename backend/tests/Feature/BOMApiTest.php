<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BOMApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }

    public function test_bom_can_be_created_updated_listed_and_deleted(): void
    {
        [$user, $data] = $this->fixture(['bom.view', 'bom.manage']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boms', $this->payload($data));

        $response->assertCreated()
            ->assertJsonPath('data.bom_no', 'BOM-001')
            ->assertJsonPath('data.status', 'Active')
            ->assertJsonPath('data.materials.0.required_qty', 2.5);

        $bomId = $response->json('data.bom_id');
        $payload = $this->payload($data);
        $payload['bom_name'] = 'Updated Pallet BOM';
        $payload['materials'][0]['required_qty'] = 3;

        $this->putJson("/api/v1/boms/{$bomId}", $payload)
            ->assertOk()
            ->assertJsonPath('data.bom_name', 'Updated Pallet BOM')
            ->assertJsonPath('data.materials.0.required_qty', 3);

        $this->getJson('/api/v1/boms?search=Updated')
            ->assertOk()
            ->assertJsonPath('data.total', 1);

        $this->deleteJson("/api/v1/boms/{$bomId}")->assertOk();
        $this->assertDatabaseMissing('bom_master', ['bom_id' => $bomId]);
    }

    public function test_bom_material_validation_rejects_duplicates(): void
    {
        [$user, $data] = $this->fixture(['bom.view', 'bom.manage']);
        Sanctum::actingAs($user);

        $payload = $this->payload($data);
        $payload['materials'][] = $payload['materials'][0];

        $this->postJson('/api/v1/boms', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('materials.0.item_id');
    }

    public function test_bom_material_uom_defaults_from_selected_item(): void
    {
        [$user, $data] = $this->fixture(['bom.view', 'bom.manage']);
        Sanctum::actingAs($user);

        $payload = $this->payload($data);
        unset($payload['materials'][0]['uom_id']);

        $this->postJson('/api/v1/boms', $payload)
            ->assertCreated()
            ->assertJsonPath('data.materials.0.uom_id', $data['uom_id']);
    }

    public function test_bom_manage_permission_is_required(): void
    {
        [$user, $data] = $this->fixture(['bom.view']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/boms', $this->payload($data))->assertForbidden();
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
        $itemId = DB::table('item_master')->insertGetId(['tenant_id' => $tenantId, 'item_code' => 'WOOD', 'item_name' => 'Wood Plank', 'item_type' => 'Raw Material', 'uom_id' => $uomId, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $finishedItemId = DB::table('item_master')->insertGetId(['tenant_id' => $tenantId, 'item_code' => 'PALLET', 'item_name' => 'Finished Pallet', 'item_type' => 'Finish Product', 'uom_id' => $uomId, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
        $palletModelId = DB::table('pallet_model_master')->insertGetId(['tenant_id' => $tenantId, 'model_code' => 'PAL', 'model_name' => 'Pallet Model', 'created_at' => now(), 'updated_at' => now()]);

        return [$user, compact('branchId', 'uomId', 'itemId', 'finishedItemId', 'palletModelId') + ['branch_id' => $branchId, 'uom_id' => $uomId, 'item_id' => $itemId, 'finished_item_id' => $finishedItemId, 'pallet_model_id' => $palletModelId]];
    }

    private function payload(array $data): array
    {
        return [
            'branch_id' => $data['branch_id'],
            'bom_no' => 'BOM-001',
            'bom_name' => 'Pallet BOM',
            'finished_item_id' => $data['finished_item_id'],
            'pallet_model_id' => $data['pallet_model_id'],
            'version_no' => 'V1',
            'status' => 'Active',
            'materials' => [[
                'item_id' => $data['item_id'],
                'uom_id' => $data['uom_id'],
                'required_qty' => 2.5,
                'wastage_percent' => 5,
            ]],
        ];
    }
}
