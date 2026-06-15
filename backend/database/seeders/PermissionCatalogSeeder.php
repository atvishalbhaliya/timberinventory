<?php

namespace Database\Seeders;

use App\Services\PermissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $permissionIds = [];

        foreach (PermissionCatalog::permissions() as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission],
                [
                    'guard_name' => 'api',
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            $permissionIds[$permission] = DB::table('permissions')->where('name', $permission)->value('id');
        }

        $rolePermissionMap = [
            'Super Admin' => PermissionCatalog::permissions(),
            'Admin' => PermissionCatalog::permissions(),
            'Manager' => [
                'dashboard.view',
                'masters.view',
                'inventory.view',
                'purchase-grn.view',
                'stock-ledger.view',
                'stock-summary.view',
                'stock-verification.view',
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
                'production.post',
                'production.cancel',
                'wastage.view',
                'wastage.manage',
                'wastage.post',
                'wastage.cancel',
                'wastage-reuse.view',
                'wastage-reuse.manage',
                'wastage-reuse.post',
                'wastage-reuse.cancel',
                'reports.view',
            ],
            'Store' => [
                'dashboard.view',
                'masters.view',
                'inventory.view',
                'inventory.manage',
                'purchase-grn.view',
                'purchase-grn.manage',
                'stock-ledger.view',
                'stock-summary.view',
                'stock-verification.view',
                'stock-verification.create',
                'stock-verification.edit',
                'stock-verification.submit',
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

        foreach ($rolePermissionMap as $roleName => $permissions) {
            $roleIds = DB::table('roles')->where('name', $roleName)->pluck('id');

            foreach ($roleIds as $roleId) {
                foreach ($permissions as $permission) {
                    DB::table('role_permissions')->insertOrIgnore([
                        'role_id' => $roleId,
                        'permission_id' => $permissionIds[$permission],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
