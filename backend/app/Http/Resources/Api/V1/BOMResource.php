<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BOMResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bom_id' => $this->bom_id,
            'branch_id' => $this->branch_id,
            'bom_no' => $this->bom_no,
            'bom_name' => $this->bom_name,
            'pallet_model_id' => $this->pallet_model_id,
            'finished_item_id' => $this->finished_item_id ?? null,
            'finished_item_name' => $this->finished_item_name ?? $this->model_name ?? null,
            'model_name' => $this->model_name ?? null,
            'version_no' => $this->version_no,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'system_protected' => (bool) ($this->system_protected ?? false),
            'revision_note' => $this->revision_note,
            'materials' => collect($this->materials ?? [])->map(fn ($row): array => [
                'bom_material_id' => $row->bom_material_id,
                'item_id' => $row->item_id,
                'item_name' => $row->item_name ?? null,
                'uom_id' => $row->uom_id,
                'uom_name' => $row->uom_name ?? null,
                'required_qty' => (float) $row->required_qty,
                'wastage_percent' => (float) $row->wastage_percent,
                'remarks' => $row->remarks ?? null,
            ])->values(),
        ];
    }
}
