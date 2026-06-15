<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function paginateForTenant(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        return Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->where('tenant_id', $tenantId)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allForTenant(int $tenantId): Collection
    {
        return Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    public function findForTenant(int $tenantId, int $roleId): Role
    {
        return Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->where('tenant_id', $tenantId)
            ->findOrFail($roleId);
    }

    public function create(array $data): Role
    {
        return Role::query()->create($data)->load('permissions:id,name');
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role->refresh()->load('permissions:id,name');
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionIds): Role
    {
        $role->permissions()->sync($permissionIds);

        return $role->refresh()->load('permissions:id,name');
    }
}
