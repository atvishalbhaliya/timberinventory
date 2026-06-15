<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\PermissionRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function __construct(
        private readonly PermissionRepository $permissions,
    ) {
    }

    public function namesForUser(User $user): Collection
    {
        if (! $user->role_id) {
            return collect();
        }

        return DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.role_id', $user->role_id)
            ->whereNull('permissions.deleted_at')
            ->orderBy('permissions.name')
            ->pluck('permissions.name');
    }

    public function userCan(User $user, string $permission): bool
    {
        $roleName = DB::table('roles')
            ->where('id', $user->role_id)
            ->where('tenant_id', $user->tenant_id)
            ->value('name');

        if ($roleName === 'Super Admin') {
            return true;
        }

        return $this->namesForUser($user)->contains($permission);
    }

    public function navigationForUser(User $user): array
    {
        $permissionNames = $this->namesForUser($user);
        $roleName = DB::table('roles')
            ->where('id', $user->role_id)
            ->where('tenant_id', $user->tenant_id)
            ->value('name');
            
        $isSuperAdmin = $roleName === 'Super Admin';

        return collect(PermissionCatalog::navigation())
            ->filter(fn (array $item): bool => $isSuperAdmin || $permissionNames->contains($item['permission']))
            ->map(fn (array $item): array => [
                'title' => $item['title'],
                'path' => $item['path'],
                'section' => $item['section'],
                'permission' => $item['permission'],
                'icon' => $item['icon'] ?? 'circle',
            ])
            ->values()
            ->all();
    }

    public function ensureCatalog(): void
    {
        foreach (PermissionCatalog::permissions() as $permission) {
            \App\Models\Permission::query()->firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'api'],
            );
        }
    }
}
