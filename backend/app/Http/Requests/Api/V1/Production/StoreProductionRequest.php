<?php

namespace App\Http\Requests\Api\V1\Production;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        $branchId = (int) ($user->branch_id ?: $this->input('branch_id'));

        if (! $this->filled('production_time')) {
            $productionId = (int) $this->route('id');
            $existingTime = $productionId > 0
                ? (string) DB::table('production_master')->where('tenant_id', $user->tenant_id)->where('production_id', $productionId)->value('production_time')
                : '';

            $this->merge(['production_time' => $existingTime !== '' ? substr($existingTime, 0, 5) : now()->format('H:i')]);
        }

        $consumptions = $this->input('consumptions', []);
        if (! is_array($consumptions)) {
            return;
        }

        $itemIds = collect($consumptions)->pluck('item_id')->filter()->unique()->values();
        $uoms = $itemIds->isEmpty()
            ? collect()
            : DB::table('item_master')->where('tenant_id', $user->tenant_id)->whereIn('item_id', $itemIds)->pluck('uom_id', 'item_id');

        $defaultLocationId = DB::table('storage_location_master')
            ->where('tenant_id', $user->tenant_id)
            ->when($branchId > 0, fn ($query) => $query->where('branch_id', $branchId))
            ->where('location_type', 'RM')
            ->where('status', 'Active')
            ->orderBy('location_id')
            ->value('location_id');

        $consumptions = collect($consumptions)->map(function ($consumption) use ($uoms, $defaultLocationId) {
            if (! is_array($consumption)) {
                return $consumption;
            }

            if (empty($consumption['uom_id']) && ! empty($consumption['item_id'])) {
                $consumption['uom_id'] = $uoms[(int) $consumption['item_id']] ?? null;
            }

            if (empty($consumption['location_id']) && ! empty($defaultLocationId)) {
                $consumption['location_id'] = $defaultLocationId;
            }

            return $consumption;
        })->all();

        $this->merge(['consumptions' => $consumptions]);
    }

    public function rules(): array
    {
        $user = $this->user();
        $branchId = (int) ($user->branch_id ?: $this->input('branch_id'));

        return [
            'branch_id' => [Rule::requiredIf(! $user->branch_id), 'nullable', 'integer', Rule::exists('branch_master', 'branch_id')->where('tenant_id', $user->tenant_id)],
            'production_no' => ['nullable', 'string', 'max:50', Rule::unique('production_master', 'production_no')->where('tenant_id', $user->tenant_id)->where('branch_id', $branchId)],
            'production_date' => ['required', 'date'],
            'production_time' => ['nullable', 'date_format:H:i'],
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
