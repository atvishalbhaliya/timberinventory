<?php

namespace App\Http\Requests\Api\V1\Permissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (blank($this->input('name')) && filled($this->input('sub_module')) && filled($this->input('action'))) {
            $module = str((string) $this->input('sub_module'))->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString();
            $action = str((string) $this->input('action'))->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString();

            $this->merge(['name' => "{$module}.{$action}"]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                'regex:/^[a-z0-9-]+\.[a-z0-9-]+$/',
                Rule::unique('permissions', 'name')->whereNull('deleted_at'),
            ],
            'main_module' => ['nullable', 'string', 'max:80'],
            'sub_module' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:50'],
        ];
    }
}
