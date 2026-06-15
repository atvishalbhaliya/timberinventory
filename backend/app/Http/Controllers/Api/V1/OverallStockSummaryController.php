<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Inventory\OverAllStockSummaryReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OverallStockSummaryController extends Controller
{
    public function __construct(private readonly OverAllStockSummaryReportService $summary)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'OverAll Stock summary loaded.', 'data' => $this->summary->paginate($request), 'metrics' => $this->summary->dashboardMetrics($request)]);
    }

    public function export(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'OverAll Stock summary export loaded.', 'data' => $this->summary->export($request)]);
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            foreach ($this->summary->importTemplate() as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'overall-stock-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'OverAll Stock import completed.', 'data' => $this->summary->importStock($request)]);
    }

    public function history(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'OverAll Stock history loaded.', 'data' => $this->summary->history($request)]);
    }
}
