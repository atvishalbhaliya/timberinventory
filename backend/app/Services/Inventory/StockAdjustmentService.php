<?php

namespace App\Services\Inventory;

class StockAdjustmentService
{
    public function __construct(private readonly InventoryTransactionService $transactions)
    {
    }
}
