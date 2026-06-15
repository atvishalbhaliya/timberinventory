<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Roles\StoreRoleRequest;
use App\Http\Requests\Api\V1\Roles\SyncRolePermissionsRequest;
use App\Http\Requests\Api\V1\Roles\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleResource;
use App\Services\AuditLogService;
use App\Services\RoleManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleManagementService $roles,
        private readonly AuditLogService $audit,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roles->listRoles($request->user(), $request->only(['search', 'per_page']));

        return response()->json([
            'success' => true,
            'message' => 'Roles loaded.',
            'data' => RoleResource::collection($roles)->response()->getData(true),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roles->createRole($request->user(), $request->validated());
        $this->audit->record($request, 'roles', 'create', $role->id, null, $role->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Role created.',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show(Request $request, int $role): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Role loaded.',
            'data' => new RoleResource($this->roles->getRole($request->user(), $role)),
        ]);
    }

    public function update(UpdateRoleRequest $request, int $role): JsonResponse
    {
        $updatedRole = $this->roles->updateRole($request->user(), $role, $request->validated());
        $this->audit->record($request, 'roles', 'update', $updatedRole->id, null, $updatedRole->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Role updated.',
            'data' => new RoleResource($updatedRole),
        ]);
    }

    public function destroy(Request $request, int $role): JsonResponse
    {
        try {
            $this->roles->deleteRole($request->user(), $role);
            $this->audit->record($request, 'roles', 'delete', $role);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role deleted.',
            'data' => [],
        ]);
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, int $role): JsonResponse
    {
        try {
            $updatedRole = $this->roles->syncRolePermissions($request->user(), $role, $request->validated('permission_ids'));
            $this->audit->record($request, 'role_permissions', 'sync', $updatedRole->id, null, [
                'permission_ids' => $updatedRole->permissions->pluck('id')->values(),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role permissions updated.',
            'data' => new RoleResource($updatedRole),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $roles = $this->roles->allRoles($request->user());

        return response()->streamDownload(function () use ($roles): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Role', 'Description', 'Status', 'Users', 'Guard', 'Permissions']);

            foreach ($roles as $role) {
                fputcsv($handle, [
                    $role->id,
                    $role->name,
                    $role->description,
                    $role->status,
                    $role->users_count ?? 0,
                    $role->guard_name,
                    $role->permissions->pluck('name')->implode(', '),
                ]);
            }

            fclose($handle);
        }, 'roles.csv', ['Content-Type' => 'text/csv']);
    }
}
