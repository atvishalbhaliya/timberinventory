<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BOMs\StoreBOMRequest;
use App\Http\Requests\Api\V1\BOMs\UpdateBOMRequest;
use App\Http\Resources\Api\V1\BOMResource;
use App\Services\Production\BomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BOMController extends Controller
{
    public function __construct(private readonly BomService $boms)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'BOM records loaded.', 'data' => $this->boms->paginate($request)]);
    }

    public function store(StoreBOMRequest $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'BOM created.', 'data' => new BOMResource($this->boms->create($request, $request->validated()))], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'BOM loaded.', 'data' => new BOMResource($this->boms->find($request, $id))]);
    }

    public function update(UpdateBOMRequest $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'BOM updated.', 'data' => new BOMResource($this->boms->update($request, $id, $request->validated()))]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->boms->delete($request, $id);

        return response()->json(['success' => true, 'message' => 'BOM deleted.', 'data' => []]);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Next BOM number generated.', 'data' => ['bom_no' => $this->boms->previewNextNumber($request)]]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->boms->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['BOM No', 'BOM Name', 'Model', 'Version', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->bom_no, $row->bom_name, $row->model_name, $row->version_no, $row->status]);
            }
            fclose($handle);
        }, 'boms.csv', ['Content-Type' => 'text/csv']);
    }
}
