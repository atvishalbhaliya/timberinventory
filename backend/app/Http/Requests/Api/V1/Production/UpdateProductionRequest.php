<?php

namespace App\Http\Requests\Api\V1\Production;

use Illuminate\Validation\Rule;

class UpdateProductionRequest extends StoreProductionRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $user = $this->user();
        $branchId = (int) ($user->branch_id ?: $this->input('branch_id'));
        $productionId = (int) $this->route('id');

        $rules['production_no'] = ['nullable', 'string', 'max:50', Rule::unique('production_master', 'production_no')->ignore($productionId, 'production_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)];

        return $rules;
    }
}
