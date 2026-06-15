<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WastageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'wastage_stock_id' => $this->wastage_stock_id,
            'branch_id' => $this->branch_id,
            'item_id' => $this->item_id,
            'item_name' => $this->item_name ?? null,
            'item_code' => $this->item_code ?? null,
            'location_id' => $this->location_id,
            'location_name' => $this->location_name ?? null,
            'wastage_type' => $this->wastage_type,
            'source_module' => $this->source_module,
            'source_id' => $this->source_id,
            'source_reference' => $this->source_reference,
            'transaction_date' => $this->transaction_date,
            'generated_qty' => (float) $this->generated_qty,
            'available_qty' => (float) $this->available_qty,
            'used_qty' => (float) $this->used_qty,
            'balance_qty' => (float) $this->balance_qty,
            'status' => $this->status,
            'remarks' => $this->remarks ?? null,
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
