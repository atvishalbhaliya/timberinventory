<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Inventory\OverAllStockSummaryReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WastageSummaryController extends Controller
{
    public function __construct(private readonly OverAllStockSummaryReportService $summary)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $request->merge(['stock_type' => 'Wastage']);

        return response()->json([
            'success' => true,
            'message' => 'Wastage management loaded.',
            'data' => $this->summary->paginate($request),
            'metrics' => $this->summary->dashboardMetrics($request),
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $request->merge(['stock_type' => 'Wastage']);

        return response()->json([
            'success' => true,
            'message' => 'Wastage management export loaded.',
            'data' => $this->summary->export($request),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $request->merge(['stock_type' => 'Wastage']);

        return response()->json([
            'success' => true,
            'message' => 'Wastage management history loaded.',
            'data' => $this->summary->history($request),
        ]);
    }
}
