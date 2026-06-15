<?php

namespace App\Http\Requests\Api\V1\Wastage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWastageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $branchId = (int) ($user->branch_id ?: $this->input('branch_id'));

        return [
            'branch_id' => [Rule::requiredIf(! $user->branch_id), 'nullable', 'integer', Rule::exists('branch_master', 'branch_id')->where('tenant_id', $user->tenant_id)],
            'transaction_date' => ['required', 'date'],
            'item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id)->where('status', 'Active')],
            'location_id' => ['required', 'integer', Rule::exists('storage_location_master', 'location_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('status', 'Active')],
            'wastage_type' => ['required', Rule::in(['Reusable', 'Non-Reusable', 'Scrap'])],
            'generated_qty' => ['required', 'numeric', 'gt:0'],
            'source_reference' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
