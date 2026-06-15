<?php

namespace App\Http\Requests\Api\V1\BOMs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateBOMRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $materials = $this->input('materials', []);
        if (! is_array($materials)) {
            return;
        }

        $itemIds = collect($materials)->pluck('item_id')->filter()->unique()->values();
        $uoms = $itemIds->isEmpty()
            ? collect()
            : DB::table('item_master')->whereIn('item_id', $itemIds)->pluck('uom_id', 'item_id');

        $materials = collect($materials)->map(function ($material) use ($uoms) {
            if (is_array($material) && empty($material['uom_id']) && ! empty($material['item_id'])) {
                $material['uom_id'] = $uoms[(int) $material['item_id']] ?? null;
            }

            return $material;
        })->all();

        $this->merge(['materials' => $materials]);
    }

    public function rules(): array
    {
        $user = $this->user();
        $branchId = (int) ($user->branch_id ?: $this->input('branch_id'));
        $bomId = (int) $this->route('id');

        return [
            'branch_id' => [Rule::requiredIf(! $user->branch_id), 'nullable', 'integer', Rule::exists('branch_master', 'branch_id')->where('tenant_id', $user->tenant_id)],
            'bom_no' => ['nullable', 'string', 'max:50', Rule::unique('bom_master', 'bom_no')->ignore($bomId, 'bom_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)],
            'bom_name' => ['required', 'string', 'max:255'],
            'finished_item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)->where('status', 'Active')->where('item_type', 'like', 'Finish Product%'))],
            'pallet_model_id' => ['nullable', 'integer', Rule::exists('pallet_model_master', 'pallet_model_id')->where('tenant_id', $user->tenant_id)],
            'version_no' => ['required', 'string', 'max:20', Rule::unique('bom_master', 'version_no')->ignore($bomId, 'bom_id')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)->where('finished_item_id', $this->input('finished_item_id'))],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'revision_note' => ['nullable', 'string', 'max:2000'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('tenant_id', $user->tenant_id), 'distinct'],
            'materials.*.uom_id' => ['required', 'integer', Rule::exists('uom_master', 'uom_id')->where('tenant_id', $user->tenant_id)],
            'materials.*.required_qty' => ['required', 'numeric', 'gt:0'],
            'materials.*.wastage_percent' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'materials.*.remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
