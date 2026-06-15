<?php

namespace App\Http\Requests\Api\V1\WastageReuse;

use Illuminate\Validation\Rule;

class UpdateWastageReuseRequest extends StoreWastageReuseRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $user = $this->user();
        $branchId = (int) ($user->branch_id ?: $this->input('branch_id'));
        $id = (int) $this->route('id');

        $rules['reuse_no'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('wastage_reuse_master', 'reuse_no')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->ignore($id, 'reuse_id'),
        ];

        return $rules;
    }
}
