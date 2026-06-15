<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'branch_id' => $this->branch_id,
            'name' => $this->name,
            'description' => $this->description,
            'guard_name' => $this->guard_name,
            'status' => $this->status ?? 'Active',
            'user_count' => $this->users_count ?? 0,
            'is_system' => in_array($this->name, ['Super Admin', 'Admin', 'Manager', 'Store', 'Production', 'Accounts'], true),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'permission_ids' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('id')->values()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
