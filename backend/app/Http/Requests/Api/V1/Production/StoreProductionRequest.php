<?php

namespace App\Http\Requests\Api\V1\Production;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductionRequest extends FormRequest
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
            'production_no' => ['nullable', 'string', 'max:50', Rule::unique('production_master', 'production_no')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)],
            'production_date' => ['required', 'date'],
            'bom_id' => ['required', 'integer', Rule::exists('bom_master', 'bom_id')->where('tenant_id', $user->tenant_id)->where('status', 'Active')->where('is_active', true)],
            'pallet_model_id' => ['nullable', 'integer', Rule::exists('pallet_model_master', 'pallet_model_id')->where('tenant_id', $user->tenant_id)],
            'produced_item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id)->where('status', 'Active')],
            'team_id' => ['required', 'integer', Rule::exists('team_master', 'team_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('status', 'Active')],
            'fg_location_id' => ['required', 'integer', Rule::exists('storage_location_master', 'location_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('status', 'Active')],
            'produced_qty' => ['required', 'numeric', 'gt:0'],
            'production_cost' => ['nullable', 'numeric', 'gte:0'],
            'labour_charge' => ['nullable', 'numeric', 'gte:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'consumptions' => ['required', 'array', 'min:1'],
            'consumptions.*.item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id)->where('status', 'Active')],
            'consumptions.*.uom_id' => ['nullable', 'integer', Rule::exists('uom_master', 'uom_id')->where('tenant_id', $user->tenant_id)],
            'consumptions.*.location_id' => ['required', 'integer', Rule::exists('storage_location_master', 'location_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('status', 'Active')],
            'consumptions.*.required_qty' => ['nullable', 'numeric', 'gte:0'],
            'consumptions.*.consumed_qty' => ['required', 'numeric', 'gt:0'],
            'consumptions.*.wastage_qty' => ['nullable', 'numeric', 'gte:0'],
            'consumptions.*.remarks' => ['nullable', 'string', 'max:1000'],
            'wastages' => ['nullable', 'array'],
            'wastages.*.item_id' => ['required_with:wastages', 'integer', Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id)->where('status', 'Active')],
            'wastages.*.location_id' => ['nullable', 'integer', Rule::exists('storage_location_master', 'location_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('status', 'Active')],
            'wastages.*.qty' => ['required_with:wastages', 'numeric', 'gt:0'],
            'wastages.*.wastage_type' => ['nullable', 'string', 'max:30'],
            'wastages.*.remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
