<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'production_id' => $this->production_id,
            'branch_id' => $this->branch_id,
            'production_no' => $this->production_no,
            'production_date' => $this->production_date,
            'bom_id' => $this->bom_id,
            'bom_no' => $this->bom_no ?? null,
            'bom_name' => $this->bom_name ?? null,
            'pallet_model_id' => $this->pallet_model_id,
            'model_name' => $this->model_name ?? null,
            'produced_item_id' => $this->produced_item_id,
            'produced_item_name' => $this->produced_item_name ?? null,
            'team_id' => $this->team_id,
            'team_name' => $this->team_name ?? null,
            'fg_location_id' => $this->fg_location_id,
            'produced_qty' => (float) $this->produced_qty,
            'production_cost' => (float) $this->production_cost,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'created_by' => $this->created_by ?? null,
            'updated_by' => $this->updated_by ?? null,
            'posted_by' => $this->posted_by ?? null,
            'cancelled_by' => $this->cancelled_by ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'posted_at' => $this->posted_at,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason ?? null,
            'consumptions' => collect($this->consumptions ?? [])->map(fn ($row): array => [
                'consumption_id' => $row->consumption_id,
                'item_id' => $row->item_id,
                'item_name' => $row->item_name ?? null,
                'uom_id' => $row->uom_id ?? null,
                'uom_name' => $row->uom_name ?? null,
                'location_id' => $row->location_id ?? null,
                'location_name' => $row->location_name ?? null,
                'required_qty' => (float) ($row->required_qty ?? 0),
                'consumed_qty' => (float) $row->consumed_qty,
                'wastage_qty' => (float) ($row->wastage_qty ?? 0),
                'current_stock' => (float) ($row->current_stock ?? 0),
                'remarks' => $row->remarks ?? null,
            ])->values(),
            'wastages' => collect($this->wastages ?? [])->map(fn ($row): array => [
                'wastage_id' => $row->wastage_id,
                'item_id' => $row->item_id,
                'item_name' => $row->item_name ?? null,
                'location_id' => $row->location_id ?? null,
                'location_name' => $row->location_name ?? null,
                'qty' => (float) $row->qty,
                'wastage_type' => $row->wastage_type ?? 'Scrap',
                'remarks' => $row->remarks ?? null,
            ])->values(),
            'outputs' => collect($this->outputs ?? [])->values(),
        ];
    }
}
