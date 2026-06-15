<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [$module, $action] = array_pad(explode('.', $this->name, 2), 2, null);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'module' => $module,
            'main_module' => $this->main_module,
            'sub_module' => $this->sub_module,
            'action' => $this->action ?? $action,
            'action_key' => $action,
            'description' => $this->description,
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
