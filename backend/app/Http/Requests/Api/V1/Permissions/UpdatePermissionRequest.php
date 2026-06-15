<?php

namespace App\Http\Requests\Api\V1\Permissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                'regex:/^[a-z0-9-]+\.[a-z0-9-]+$/',
                Rule::unique('permissions', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('permission')),
            ],
            'main_module' => ['nullable', 'string', 'max:80'],
            'sub_module' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:50'],
        ];
    }
}
