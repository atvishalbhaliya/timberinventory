<?php

namespace App\Services\Inventory;

class FinishedProductStockSummaryReportService extends StockSummaryReportService
{
    protected function itemTypePatterns(): array
    {
        return ['Finish Product%', 'Finished Product%'];
    }
}
