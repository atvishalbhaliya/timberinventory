<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WastageReuseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reuse_id' => $this->reuse_id,
            'branch_id' => $this->branch_id,
            'reuse_no' => $this->reuse_no,
            'reuse_date' => $this->reuse_date,
            'source_wastage_stock_id' => $this->source_wastage_stock_id,
            'source_item_id' => $this->source_item_id,
            'source_item_name' => $this->source_item_name ?? null,
            'source_location_id' => $this->source_location_id,
            'source_location_name' => $this->source_location_name ?? null,
            'consumed_qty' => (float) $this->consumed_qty,
            'produced_item_id' => $this->produced_item_id,
            'produced_item_name' => $this->produced_item_name ?? null,
            'destination_location_id' => $this->destination_location_id,
            'destination_location_name' => $this->destination_location_name ?? null,
            'team_id' => $this->team_id,
            'team_name' => $this->team_name ?? null,
            'produced_qty' => (float) $this->produced_qty,
            'production_cost' => (float) $this->production_cost,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'details' => $this->details ?? [],
            'created_by' => $this->created_by ?? null,
            'updated_by' => $this->updated_by ?? null,
            'posted_by' => $this->posted_by ?? null,
            'cancelled_by' => $this->cancelled_by ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'posted_at' => $this->posted_at ?? null,
            'cancelled_at' => $this->cancelled_at ?? null,
            'cancellation_reason' => $this->cancellation_reason ?? null,
        ];
    }
}
