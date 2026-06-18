<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;

class WastageService
{
    public function currentQty(int $tenantId, int $branchId, int $itemId, int $locationId, string $stockType = 'Wastage'): float
    {
        return (float) DB::table('stock_summary')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('stock_type', $stockType)
            ->lockForUpdate()
            ->value('stock_qty');
    }

    public function applyMovement(array $movement): void
    {
        $stockType = $movement['stock_type'] ?? 'Wastage';
        $existing = DB::table('stock_summary')
            ->where('tenant_id', $movement['tenant_id'])
            ->where('branch_id', $movement['branch_id'])
            ->where('item_id', $movement['item_id'])
            ->where('location_id', $movement['location_id'])
            ->where('stock_type', $stockType)
            ->lockForUpdate()
            ->first();

        $qtyIn = (float) ($movement['qty_in'] ?? 0);
        $qtyOut = (float) ($movement['qty_out'] ?? 0);
        $rate = (float) ($movement['rate'] ?? 0);
        $userId = $movement['user_id'] ?? null;
        if ($existing) {
            $oldQty = (float) $existing->stock_qty;
            $newQty = $oldQty + $qtyIn - $qtyOut;
            $avgRate = (float) $existing->avg_rate;

            if ($qtyIn > 0 && $newQty > 0) {
                $avgRate = (($oldQty * $avgRate) + ($qtyIn * $rate)) / ($oldQty + $qtyIn);
            }

            DB::table('stock_summary')
                ->where('stock_id', $existing->stock_id)
                ->update([
                    'stock_qty' => $newQty,
                    'avg_rate' => $avgRate,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('stock_summary')->insert([
            'tenant_id' => $movement['tenant_id'],
            'branch_id' => $movement['branch_id'],
            'item_id' => $movement['item_id'],
            'location_id' => $movement['location_id'],
            'stock_type' => $stockType,
            'stock_qty' => $qtyIn - $qtyOut,
            'avg_rate' => $qtyIn > 0 ? $rate : 0,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
