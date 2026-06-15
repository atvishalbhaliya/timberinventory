<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RoleManagementService
{
    public function __construct(
        private readonly RoleRepository $roles,
        private readonly PermissionRepository $permissions,
    ) {
    }

    public function listRoles(User $user, array $filters): LengthAwarePaginator
    {
        return $this->roles->paginateForTenant($user->tenant_id, $filters);
    }

    public function allRoles(User $user): Collection
    {
        return $this->roles->allForTenant($user->tenant_id);
    }

    public function getRole(User $user, int $roleId): Role
    {
        return $this->roles->findForTenant($user->tenant_id, $roleId);
    }

    public function createRole(User $user, array $data): Role
    {
        return $this->roles->create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'guard_name' => $data['guard_name'] ?? 'api',
            'status' => $data['status'] ?? 'Active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function updateRole(User $user, int $roleId, array $data): Role
    {
        $role = $this->roles->findForTenant($user->tenant_id, $roleId);

        return $this->roles->update($role, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'guard_name' => $data['guard_name'] ?? $role->guard_name,
            'status' => $data['status'] ?? $role->status ?? 'Active',
            'updated_by' => $user->id,
        ]);
    }

    public function deleteRole(User $user, int $roleId): void
    {
        $role = $this->roles->findForTenant($user->tenant_id, $roleId);

        if (in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'Store', 'Production', 'Accounts'], true)) {
            throw new RuntimeException('System roles cannot be deleted.');
        }

        if (DB::table('users')->where('role_id', $role->id)->exists()) {
            throw new RuntimeException('This role is assigned to active users and cannot be deleted.');
        }

        $this->roles->delete($role);
    }

    public function listPermissions(array $filters): LengthAwarePaginator
    {
        return $this->permissions->paginate($filters);
    }

    public function allPermissions(): Collection
    {
        return $this->permissions->all();
    }

    public function createPermission(array $data): Permission
    {
        $name = $data['name'] ?? $this->permissionNameFromParts($data);

        return $this->permissions->create([
            'name' => $name,
            'main_module' => $data['main_module'] ?? null,
            'sub_module' => $data['sub_module'] ?? null,
            'action' => $data['action'] ?? null,
            'description' => $data['description'] ?? null,
            'guard_name' => $data['guard_name'] ?? 'api',
        ]);
    }

    public function updatePermission(int $permissionId, array $data): Permission
    {
        $permission = $this->permissions->find($permissionId);

        return $this->permissions->update($permission, [
            'name' => $data['name'],
            'main_module' => $data['main_module'] ?? $permission->main_module,
            'sub_module' => $data['sub_module'] ?? $permission->sub_module,
            'action' => $data['action'] ?? $permission->action,
            'description' => $data['description'] ?? $permission->description,
            'guard_name' => $data['guard_name'] ?? $permission->guard_name,
        ]);
    }

    public function deletePermission(int $permissionId): void
    {
        $permission = $this->permissions->find($permissionId);

        if (DB::table('role_permissions')->where('permission_id', $permission->id)->exists()) {
            throw new RuntimeException('This permission is assigned to roles and cannot be deleted.');
        }

        $this->permissions->delete($permission);
    }

    public function syncRolePermissions(User $user, int $roleId, array $permissionIds): Role
    {
        $role = $this->roles->findForTenant($user->tenant_id, $roleId);
        $existingPermissionIds = $this->permissions->findMany($permissionIds)->pluck('id')->all();

        if (count($existingPermissionIds) !== count(array_unique($permissionIds))) {
            throw new RuntimeException('One or more permissions are invalid.');
        }

        return $this->roles->syncPermissions($role, $existingPermissionIds);
    }

    private function permissionNameFromParts(array $data): string
    {
        if (blank($data['sub_module'] ?? null) || blank($data['action'] ?? null)) {
            throw new RuntimeException('Sub module and action are required when permission name is not provided.');
        }

        $module = str((string) $data['sub_module'])->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString();
        $action = str((string) $data['action'])->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString();

        return "{$module}.{$action}";
    }
}
