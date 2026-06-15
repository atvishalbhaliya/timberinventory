<?php

namespace App\Http\Requests\Api\V1\Grns;

use Illuminate\Validation\Rule;

class UpdateGRNRequest extends StoreGRNRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $user = $this->user();
        $branchId = (int) ($user->branch_id ?: $this->input('branch_id'));

        $rules['grn_no'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('grn_master', 'grn_no')
                ->ignore((int) $this->route('id'), 'grn_id')
                ->where('tenant_id', $user->tenant_id)
                ->where('branch_id', $branchId),
        ];

        return $rules;
    }
}
