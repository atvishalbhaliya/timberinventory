<?php

namespace App\Http\Resources\Api\V1\Grns;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GRNResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'grn_id' => $this->grn_id,
            'tenant_id' => $this->tenant_id,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch_name ?? null,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name ?? $this->party_name ?? null,
            'party_name' => $this->party_name ?? null,
            'grn_no' => $this->grn_no,
            'grn_date' => $this->grn_date,
            'invoice_no' => $this->invoice_no,
            'vehicle_no' => $this->vehicle_no,
            'purchase_order_ref' => $this->purchase_order_ref ?? null,
            'warehouse_location_id' => $this->warehouse_location_id ?? null,
            'received_by' => $this->received_by ?? null,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'total_qty' => (float) $this->total_qty,
            'total_amount' => (float) $this->total_amount,
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'tax_amount' => (float) ($this->tax_amount ?? 0),
            'freight_charges' => (float) ($this->freight_charges ?? 0),
            'other_charges' => (float) ($this->other_charges ?? 0),
            'grand_total' => (float) ($this->grand_total ?? $this->total_amount),
            'attachments' => $this->attachments ? json_decode($this->attachments, true) : [],
            'posted_at' => $this->posted_at,
            'cancelled_at' => $this->cancelled_at,
            'details' => GRNDetailResource::collection(collect($this->details ?? [])),
        ];
    }
}
