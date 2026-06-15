<?php

namespace App\Services\Inventory;

class InventoryTransactionService
{
    public function __construct(
        private readonly StockLedgerService $ledger,
        private readonly StockSummaryService $summary,
    ) {
    }

    public function record(array $movement): int
    {
        $ledgerId = $this->ledger->createEntry($movement);
        $this->summary->applyMovement($movement);

        return $ledgerId;
    }
}
