<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $tenantId = DB::table('tenant_master')->insertGetId([
            'tenant_code' => 'SURESH',
            'tenant_name' => 'Suresh Timber',
            'company_name' => 'Suresh Timber',
            'address' => 'Main Industrial Area',
            'mobile' => '9000000000',
            'email' => 'admin@sureshtimber.test',
            'status' => 'Active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $mainBranchId = DB::table('branch_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => null,
            'branch_code' => 'MAIN',
            'branch_name' => 'Main Branch',
            'address' => 'Main Factory Yard',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'country' => 'India',
            'mobile' => '9000000001',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $warehouseBranchId = DB::table('branch_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => null,
            'branch_code' => 'WH',
            'branch_name' => 'Factory Branch',
            'address' => 'Warehouse Yard',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'country' => 'India',
            'mobile' => '9000000002',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $roleNames = ['Super Admin', 'Admin', 'Manager', 'Store', 'Production', 'Accounts'];
        $roleIds = [];
        $roleMasterIds = [];

        foreach ($roleNames as $roleName) {
            $roleIds[$roleName] = DB::table('roles')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'name' => $roleName,
                'guard_name' => 'api',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $roleMasterIds[$roleName] = DB::table('role_master')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'role_name' => $roleName,
                'guard_name' => 'api',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissions = [
            'dashboard.view',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
            'role-permissions.manage',
            'masters.view',
            'masters.manage',
            'inventory.view',
            'inventory.manage',
            'purchase-grn.view',
            'purchase-grn.manage',
            'bom.view',
            'bom.manage',
            'production.view',
            'production.manage',
            'dispatch.view',
            'dispatch.manage',
            'accounts.view',
            'accounts.manage',
            'reports.view',
            'audit.view',
        ];
        $permissionIds = [];

        foreach ($permissions as $permission) {
            $permissionIds[$permission] = DB::table('permissions')->insertGetId([
                'name' => $permission,
                'guard_name' => 'api',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $rolePermissionMap = [
            'Super Admin' => $permissions,
            'Admin' => $permissions,
            'Manager' => [
                'dashboard.view',
                'masters.view',
                'inventory.view',
                'purchase-grn.view',
                'bom.view',
                'production.view',
                'dispatch.view',
                'reports.view',
            ],
            'Production' => [
                'dashboard.view',
                'inventory.view',
                'bom.view',
                'bom.manage',
                'production.view',
                'production.manage',
                'reports.view',
            ],
            'Store' => [
                'dashboard.view',
                'masters.view',
                'inventory.view',
                'inventory.manage',
                'purchase-grn.view',
                'purchase-grn.manage',
                'dispatch.view',
                'dispatch.manage',
                'reports.view',
            ],
            'Accounts' => [
                'dashboard.view',
                'purchase-grn.view',
                'accounts.view',
                'accounts.manage',
                'reports.view',
                'audit.view',
            ],
        ];

        foreach ($rolePermissionMap as $roleName => $rolePermissions) {
            foreach ($rolePermissions as $permission) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleIds[$roleName],
                    'permission_id' => $permissionIds[$permission],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $users = [
            ['login_id' => 'superadmin', 'employee_code' => 'SA001', 'full_name' => 'Super Admin', 'role' => 'Super Admin', 'branch_id' => null],
            ['login_id' => 'admin', 'employee_code' => 'AD001', 'full_name' => 'Admin', 'role' => 'Admin', 'branch_id' => $mainBranchId],
            ['login_id' => 'manager', 'employee_code' => 'MG001', 'full_name' => 'Manager', 'role' => 'Manager', 'branch_id' => $mainBranchId],
            ['login_id' => 'store', 'employee_code' => 'ST001', 'full_name' => 'Store', 'role' => 'Store', 'branch_id' => $warehouseBranchId],
            ['login_id' => 'production', 'employee_code' => 'PR001', 'full_name' => 'Production', 'role' => 'Production', 'branch_id' => $mainBranchId],
            ['login_id' => 'accounts', 'employee_code' => 'AC001', 'full_name' => 'Accounts', 'role' => 'Accounts', 'branch_id' => $mainBranchId],
        ];

        foreach ($users as $user) {
            User::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $user['branch_id'],
                'login_id' => $user['login_id'],
                'password' => Hash::make('Admin@123'),
                'employee_code' => $user['employee_code'],
                'full_name' => $user['full_name'],
                'mobile' => '9000000000',
                'email' => $user['login_id'].'@sureshtimber.test',
                'role_id' => $roleIds[$user['role']],
                'status' => 'Active',
            ]);

            DB::table('user_master')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $user['branch_id'],
                'role_id' => $roleMasterIds[$user['role']],
                'login_id' => $user['login_id'],
                'password' => Hash::make('Admin@123'),
                'employee_code' => $user['employee_code'],
                'full_name' => $user['full_name'],
                'mobile' => '9000000000',
                'email' => $user['login_id'].'@sureshtimber.test',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $materialTypes = [];
        foreach (['WOOD', 'PLYWOOD', 'NAIL', 'SCREW', 'PACKING', 'CONSUMABLE', 'SCRAP'] as $type) {
            $materialTypes[$type] = DB::table('material_type_master')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'material_type_code' => $type,
                'material_type_name' => $type,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $uoms = [];
        foreach (['PCS', 'NOS', 'KG', 'BOX', 'CFT', 'MTR'] as $uom) {
            $uoms[$uom] = DB::table('uom_master')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'uom_code' => $uom,
                'uom_name' => $uom,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $locations = [];
        foreach (['RM', 'WIP', 'FG', 'WASTAGE', 'SCRAP'] as $location) {
            $locations[$location] = DB::table('storage_location_master')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => $mainBranchId,
                'location_code' => $location,
                'location_name' => $location,
                'location_type' => $location,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (['TEAM-01', 'TEAM-02', 'TEAM-03'] as $index => $teamCode) {
            DB::table('team_master')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $mainBranchId,
                'team_code' => $teamCode,
                'team_name' => $teamCode,
                'contractor_name' => 'Contractor '.($index + 1),
                'rate_per_pallet' => 25 + ($index * 5),
                'tds_percent' => 1,
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $items = [
            ['code' => 'RM-PINE-1356-22', 'name' => 'Pine Wood 1356x22', 'type' => 'Raw Material', 'material' => 'WOOD', 'uom' => 'CFT', 'qty' => 500, 'rate' => 820],
            ['code' => 'RM-PINE-906-22', 'name' => 'Pine Wood 906x22', 'type' => 'Raw Material', 'material' => 'WOOD', 'uom' => 'CFT', 'qty' => 420, 'rate' => 790],
            ['code' => 'RM-PLY-SHEET', 'name' => 'Plywood Sheet', 'type' => 'Raw Material', 'material' => 'PLYWOOD', 'uom' => 'PCS', 'qty' => 250, 'rate' => 550],
            ['code' => 'RM-NAIL-75', 'name' => 'Nail 75mm', 'type' => 'Consumable', 'material' => 'NAIL', 'uom' => 'KG', 'qty' => 180, 'rate' => 95],
            ['code' => 'RM-SCREW-50', 'name' => 'Screw 50mm', 'type' => 'Consumable', 'material' => 'SCREW', 'uom' => 'KG', 'qty' => 120, 'rate' => 140],
            ['code' => 'FG-P001', 'name' => 'Pallet P001', 'type' => 'Finish Product', 'material' => 'WOOD', 'uom' => 'PCS', 'qty' => 40, 'rate' => 1200],
            ['code' => 'FG-P002', 'name' => 'Pallet P002', 'type' => 'Finish Product', 'material' => 'WOOD', 'uom' => 'PCS', 'qty' => 25, 'rate' => 1550],
        ];

        $itemIds = [];
        foreach ($items as $item) {
            $itemId = DB::table('item_master')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'item_code' => $item['code'],
                'item_name' => $item['name'],
                'item_type' => $item['type'],
                'material_type_id' => $materialTypes[$item['material']],
                'uom_id' => $uoms[$item['uom']],
                'minimum_stock' => 10,
                'opening_qty' => $item['qty'],
                'opening_rate' => $item['rate'],
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $itemIds[$item['name']] = $itemId;

            $locationId = str_starts_with($item['code'], 'FG-') ? $locations['FG'] : $locations['RM'];
            DB::table('stock_summary')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $mainBranchId,
                'item_id' => $itemId,
                'location_id' => $locationId,
                'stock_qty' => $item['qty'],
                'avg_rate' => $item['rate'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('stock_ledger')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $mainBranchId,
                'item_id' => $itemId,
                'location_id' => $locationId,
                'transaction_date' => $now,
                'transaction_type' => 'Opening',
                'qty_in' => $item['qty'],
                'qty_out' => 0,
                'balance_qty' => $item['qty'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $models = [
            'P001' => ['name' => 'Pallet P001', 'length' => 1200, 'width' => 1000, 'height' => 140],
            'P002' => ['name' => 'Pallet P002', 'length' => 1300, 'width' => 1100, 'height' => 150],
        ];

        foreach ($models as $modelCode => $model) {
            $modelId = DB::table('pallet_model_master')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'model_code' => $modelCode,
                'model_name' => $model['name'],
                'length' => $model['length'],
                'width' => $model['width'],
                'height' => $model['height'],
                'wood_type' => 'Pine',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $bomId = DB::table('bom_master')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'pallet_model_id' => $modelId,
                'version_no' => 'V1',
                'is_active' => true,
                'revision_note' => 'Initial demo BOM',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $materials = $modelCode === 'P001'
                ? [
                    ['item' => 'Pine Wood 1356x22', 'qty' => 0.85, 'wastage' => 4],
                    ['item' => 'Nail 75mm', 'qty' => 0.12, 'wastage' => 1],
                    ['item' => 'Plywood Sheet', 'qty' => 0.20, 'wastage' => 2],
                ]
                : [
                    ['item' => 'Pine Wood 906x22', 'qty' => 1.10, 'wastage' => 5],
                    ['item' => 'Screw 50mm', 'qty' => 0.18, 'wastage' => 1],
                    ['item' => 'Plywood Sheet', 'qty' => 0.30, 'wastage' => 2],
                ];

            foreach ($materials as $material) {
                DB::table('bom_material')->insert([
                    'tenant_id' => $tenantId,
                    'branch_id' => null,
                    'bom_id' => $bomId,
                    'item_id' => $itemIds[$material['item']],
                    'required_qty' => $material['qty'],
                    'wastage_percent' => $material['wastage'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
