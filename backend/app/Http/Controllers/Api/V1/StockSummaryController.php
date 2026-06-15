<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Inventory\StockSummaryReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockSummaryController extends Controller
{
    public function __construct(private readonly StockSummaryReportService $summary)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock summary loaded.', 'data' => $this->summary->paginate($request), 'metrics' => $this->summary->dashboardMetrics($request)]);
    }

    public function export(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock summary export loaded.', 'data' => $this->summary->export($request)]);
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            foreach ($this->summary->importTemplate() as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'stock-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock import completed.', 'data' => $this->summary->importStock($request)]);
    }

    public function history(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock history loaded.', 'data' => $this->summary->history($request)]);
    }
}
