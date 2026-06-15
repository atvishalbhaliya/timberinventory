<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Inventory\StockLedgerReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function __construct(private readonly StockLedgerReportService $ledger)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock ledger loaded.', 'data' => $this->ledger->paginate($request)]);
    }

    public function export(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock ledger export loaded.', 'data' => $this->ledger->export($request)]);
    }
}
