<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Permissions\StorePermissionRequest;
use App\Http\Requests\Api\V1\Permissions\UpdatePermissionRequest;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Services\AuditLogService;
use App\Services\RoleManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PermissionController extends Controller
{
    public function __construct(
        private readonly RoleManagementService $roles,
        private readonly AuditLogService $audit,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $permissions = $this->roles->listPermissions($request->only(['search', 'per_page', 'main_module', 'action']));

        return response()->json([
            'success' => true,
            'message' => 'Permissions loaded.',
            'data' => PermissionResource::collection($permissions)->response()->getData(true),
        ]);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->roles->createPermission($request->validated());
        $this->audit->record($request, 'permissions', 'create', $permission->id, null, $permission->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Permission created.',
            'data' => new PermissionResource($permission),
        ], 201);
    }

    public function update(UpdatePermissionRequest $request, int $permission): JsonResponse
    {
        $updatedPermission = $this->roles->updatePermission($permission, $request->validated());
        $this->audit->record($request, 'permissions', 'update', $updatedPermission->id, null, $updatedPermission->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Permission updated.',
            'data' => new PermissionResource($updatedPermission),
        ]);
    }

    public function destroy(Request $request, int $permission): JsonResponse
    {
        try {
            $this->roles->deletePermission($permission);
            $this->audit->record($request, 'permissions', 'delete', $permission);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted.',
            'data' => [],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $permissions = $this->roles->allPermissions();

        return response()->streamDownload(function () use ($permissions): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Permission', 'Guard']);

            foreach ($permissions as $permission) {
                fputcsv($handle, [$permission->id, $permission->name, $permission->guard_name]);
            }

            fclose($handle);
        }, 'permissions.csv', ['Content-Type' => 'text/csv']);
    }
}
