<?php

namespace App\Http\Requests\Api\V1\Grns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGRNRequest extends FormRequest
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
            'branch_id' => [
                Rule::requiredIf(! $user->branch_id),
                'nullable',
                'integer',
                Rule::exists('branch_master', 'branch_id')->where('tenant_id', $user->tenant_id),
            ],
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('party_master', 'party_id')
                    ->where('tenant_id', $user->tenant_id)
                    ->where(fn ($query) => $query->where('party_type', 'Supplier')->orWhere('party_type', 'Both')),
            ],
            'grn_no' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('grn_master', 'grn_no')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $branchId),
            ],
            'grn_date' => ['required', 'date'],
            'invoice_no' => ['nullable', 'string', 'max:50'],
            'vehicle_no' => ['nullable', 'string', 'max:50'],
            'purchase_order_ref' => ['nullable', 'string', 'max:80'],
            'warehouse_location_id' => [
                'nullable',
                'integer',
                Rule::exists('storage_location_master', 'location_id')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $branchId),
            ],
            'received_by' => ['nullable', 'string', 'max:120'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'freight_charges' => ['nullable', 'numeric', 'gte:0'],
            'other_charges' => ['nullable', 'numeric', 'gte:0'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.name' => ['required_with:attachments', 'string', 'max:180'],
            'attachments.*.type' => ['nullable', 'string', 'max:80'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.item_id' => [
                'required',
                'integer',
                Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id),
            ],
            'details.*.uom_id' => [
                'required',
                'integer',
                Rule::exists('uom_master', 'uom_id')->where('tenant_id', $user->tenant_id),
            ],
            'details.*.location_id' => [
                'nullable',
                'integer',
                Rule::exists('storage_location_master', 'location_id')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $branchId),
            ],
            'details.*.ordered_qty' => ['nullable', 'numeric', 'gte:0'],
            'details.*.received_qty' => ['nullable', 'numeric', 'gte:0'],
            'details.*.rejected_qty' => ['nullable', 'numeric', 'gte:0'],
            'details.*.accepted_qty' => ['nullable', 'numeric', 'gte:0'],
            'details.*.qty' => ['nullable', 'numeric', 'gt:0'],
            'details.*.rate' => ['required', 'numeric', 'gte:0'],
            'details.*.discount_amount' => ['nullable', 'numeric', 'gte:0'],
            'details.*.tax_amount' => ['nullable', 'numeric', 'gte:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Branch is required.',
            'supplier_id.required' => 'Supplier is required.',
            'details.required' => 'At least one line item is required.',
            'details.*.qty.gt' => 'Line quantity must be greater than zero.',
        ];
    }
}
