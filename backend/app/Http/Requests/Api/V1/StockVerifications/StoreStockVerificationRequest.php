<?php

namespace App\Http\Requests\Api\V1\StockVerifications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockVerificationRequest extends FormRequest
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
            'location_id' => ['required', 'integer', Rule::exists('storage_location_master', 'location_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)],
            'verification_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id)],
            'details.*.uom_id' => ['nullable', 'integer', Rule::exists('uom_master', 'uom_id')->where('tenant_id', $user->tenant_id)],
            'details.*.location_id' => ['nullable', 'integer'],
            'details.*.system_qty' => ['required', 'numeric'],
            'details.*.physical_qty' => ['required', 'numeric', 'gte:0'],
        ];
    }
}
