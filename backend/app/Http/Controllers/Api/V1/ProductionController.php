<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Production\StoreProductionRequest;
use App\Http\Requests\Api\V1\Production\UpdateProductionRequest;
use App\Http\Resources\Api\V1\ProductionResource;
use App\Services\Production\ProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductionController extends Controller
{
    public function __construct(private readonly ProductionService $production)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Production records loaded.', 'data' => $this->production->paginate($request)]);
    }

    public function store(StoreProductionRequest $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Production draft created.', 'data' => new ProductionResource($this->production->create($request, $request->validated()))], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Production loaded.', 'data' => new ProductionResource($this->production->find($request, $id))]);
    }

    public function update(UpdateProductionRequest $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Production draft updated.', 'data' => new ProductionResource($this->production->update($request, $id, $request->validated()))]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->production->delete($request, $id);

        return response()->json(['success' => true, 'message' => 'Production deleted.', 'data' => []]);
    }

    public function post(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Production posted.', 'data' => new ProductionResource($this->production->post($request, $id))]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);

        return response()->json(['success' => true, 'message' => 'Production cancelled.', 'data' => new ProductionResource($this->production->cancel($request, $id, $data['reason'] ?? null))]);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Next production number generated.', 'data' => ['production_no' => $this->production->previewNextNumber($request)]]);
    }

    public function bomMaterials(Request $request, int $bom): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'BOM materials loaded.', 'data' => $this->production->bomMaterials($request, $bom)]);
    }

    public function currentStock(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Current stock loaded.', 'data' => $this->production->currentStock($request)]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->production->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Production No', 'Date', 'BOM', 'Team', 'Produced Item', 'Qty', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->production_no, $row->production_date, $row->bom_no, $row->team_name, $row->produced_item_name, $row->produced_qty, $row->status]);
            }
            fclose($handle);
        }, 'production.csv', ['Content-Type' => 'text/csv']);
    }
}
