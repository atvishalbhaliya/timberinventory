<?php

namespace App\Http\Requests\Api\V1\WastageReuse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWastageReuseRequest extends FormRequest
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
            'reuse_no' => ['nullable', 'string', 'max:50', Rule::unique('wastage_reuse_master', 'reuse_no')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)],
            'reuse_date' => ['required', 'date'],
            'source_wastage_stock_id' => ['required', 'integer', Rule::exists('wastage_stock', 'wastage_stock_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)],
            'consumed_qty' => ['required', 'numeric', 'gt:0'],
            'produced_item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id)->where('status', 'Active')],
            'destination_location_id' => ['required', 'integer', Rule::exists('storage_location_master', 'location_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('status', 'Active')],
            'team_id' => ['nullable', 'integer', Rule::exists('team_master', 'team_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('status', 'Active')],
            'produced_qty' => ['required', 'numeric', 'gt:0'],
            'production_cost' => ['nullable', 'numeric', 'gte:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
