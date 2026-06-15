<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Permission::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('main_module', 'like', "%{$search}%")
                        ->orWhere('sub_module', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            })
            ->when($filters['main_module'] ?? null, fn ($query, string $module) => $query->where('main_module', $module))
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 50));
    }

    public function all(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get();
    }

    public function find(int $permissionId): Permission
    {
        return Permission::query()->findOrFail($permissionId);
    }

    public function findMany(array $permissionIds): Collection
    {
        return Permission::query()
            ->whereIn('id', $permissionIds)
            ->get();
    }

    public function create(array $data): Permission
    {
        return Permission::query()->create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission->refresh();
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
