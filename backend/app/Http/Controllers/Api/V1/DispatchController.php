<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Dispatch\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DispatchController extends Controller
{
    public function __construct(private readonly DispatchService $dispatch)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Dispatch challans loaded.',
            'data' => $this->dispatch->paginate($request),
        ]);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Next challan number generated.',
            'data' => ['challan_no' => $this->dispatch->nextNumber($request)],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Dispatch challan loaded.',
            'data' => $this->dispatch->find($request, $id),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Dispatch challan created.',
            'data' => ['challan_id' => $this->dispatch->store($request)],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Dispatch challan updated.',
            'data' => ['challan_id' => $this->dispatch->update($request, $id)],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->dispatch->delete($request, $id);

        return response()->json([
            'success' => true,
            'message' => 'Dispatch challan deleted.',
            'data' => [],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->dispatch->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Challan No', 'Date', 'Customer', 'Location', 'Vehicle', 'Driver', 'Destination', 'Qty', 'Created By']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->challan_no,
                    $row->challan_date,
                    $row->customer_name,
                    $row->source_location_name,
                    $row->vehicle_no,
                    $row->driver_name,
                    $row->destination,
                    $row->total_qty,
                    $row->created_by_name,
                ]);
            }
            fclose($handle);
        }, 'dispatch-challans.csv', ['Content-Type' => 'text/csv']);
    }
}
