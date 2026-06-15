<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STOCK_PERMISSIONS = [
        'stock-ledger.view',
        'stock-summary.view',
        'stock-verification.view',
        'stock-verification.create',
        'stock-verification.edit',
        'stock-verification.submit',
        'stock-verification.approve',
        'stock-verification.cancel',
    ];

    private const ADMIN_VIEW_PERMISSIONS = [
        'stock-ledger.view',
        'stock-summary.view',
        'stock-verification.view',
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::STOCK_PERMISSIONS as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission],
                [
                    'guard_name' => 'api',
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', self::STOCK_PERMISSIONS)
            ->pluck('id', 'name');

        $rolePermissions = [
            'Super Admin' => self::STOCK_PERMISSIONS,
            'Admin' => self::STOCK_PERMISSIONS,
            'Manager' => self::ADMIN_VIEW_PERMISSIONS,
            'Store' => [
                'stock-ledger.view',
                'stock-summary.view',
                'stock-verification.view',
                'stock-verification.create',
                'stock-verification.edit',
                'stock-verification.submit',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
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

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', self::STOCK_PERMISSIONS)
            ->pluck('id');

        DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
