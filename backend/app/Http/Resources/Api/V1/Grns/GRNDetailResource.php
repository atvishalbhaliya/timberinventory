<?php

namespace App\Http\Resources\Api\V1\Grns;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GRNDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'grn_detail_id' => $this->grn_detail_id,
            'item_id' => $this->item_id,
            'item_name' => $this->item_name ?? null,
            'uom_id' => $this->uom_id,
            'uom_name' => $this->uom_name ?? null,
            'location_id' => $this->location_id,
            'location_name' => $this->location_name ?? null,
            'ordered_qty' => (float) ($this->ordered_qty ?? 0),
            'received_qty' => (float) ($this->received_qty ?? $this->qty),
            'rejected_qty' => (float) ($this->rejected_qty ?? 0),
            'accepted_qty' => (float) ($this->accepted_qty ?? $this->qty),
            'qty' => (float) $this->qty,
            'rate' => (float) $this->rate,
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'tax_amount' => (float) ($this->tax_amount ?? 0),
            'amount' => (float) $this->amount,
        ];
    }
}
