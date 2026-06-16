<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    public function __construct(private readonly StockSummaryService $summary)
    {
    }

    public function createEntry(array $movement): int
    {
        $balanceQty = $this->summary->currentQty(
            (int) $movement['tenant_id'],
            (int) $movement['branch_id'],
            (int) $movement['item_id'],
            (int) $movement['location_id'],
            $movement['stock_type'] ?? 'Fresh',
        ) + (float) ($movement['qty_in'] ?? 0) - (float) ($movement['qty_out'] ?? 0);

        return DB::table('stock_ledger')->insertGetId([
            'tenant_id' => $movement['tenant_id'],
            'branch_id' => $movement['branch_id'],
            'item_id' => $movement['item_id'],
            'location_id' => $movement['location_id'],
            'team_id' => $movement['team_id'] ?? null,
            'stock_type' => $movement['stock_type'] ?? 'Fresh',
            'transaction_date' => $movement['transaction_date'],
            'transaction_type' => $movement['transaction_type'],
            'reference_id' => $movement['reference_id'],
            'reference_type' => $movement['reference_type'],
            'qty_in' => $movement['qty_in'] ?? 0,
            'qty_out' => $movement['qty_out'] ?? 0,
            'balance_qty' => $balanceQty,
            'rate' => $movement['rate'] ?? 0,
            'amount' => $movement['amount'] ?? 0,
            'production_rate' => $movement['production_rate'] ?? 0,
            'labour_charge' => $movement['labour_charge'] ?? 0,
            'created_by' => $movement['user_id'] ?? null,
            'updated_by' => $movement['user_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'ledger_id');
    }

    public function hasReference(string $referenceType, int $referenceId): bool
    {
        return DB::table('stock_ledger')
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->exists();
    }
}
