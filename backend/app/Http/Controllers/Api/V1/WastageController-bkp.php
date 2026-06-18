<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Wastage\StoreWastageRequest;
use App\Http\Requests\Api\V1\Wastage\UpdateWastageRequest;
use App\Http\Resources\Api\V1\WastageResource;
use App\Services\Production\WastageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WastageController extends Controller
{
    public function __construct(private readonly WastageService $wastage)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage records loaded.', 'data' => $this->wastage->paginate($request)]);
    }

    public function store(StoreWastageRequest $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage draft created.', 'data' => new WastageResource($this->wastage->create($request, $request->validated()))], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage loaded.', 'data' => new WastageResource($this->wastage->find($request, $id))]);
    }

    public function update(UpdateWastageRequest $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage updated.', 'data' => new WastageResource($this->wastage->update($request, $id, $request->validated()))]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->wastage->delete($request, $id);

        return response()->json(['success' => true, 'message' => 'Wastage deleted.', 'data' => []]);
    }

    public function post(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage posted.', 'data' => new WastageResource($this->wastage->post($request, $id))]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);

        return response()->json(['success' => true, 'message' => 'Wastage cancelled.', 'data' => new WastageResource($this->wastage->cancel($request, $id, $data['reason'] ?? null))]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->wastage->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Item', 'Location', 'Type', 'Source', 'Generated', 'Available', 'Used', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->transaction_date, $row->item_name, $row->location_name, $row->wastage_type, $row->source_reference ?: $row->source_module, $row->generated_qty, $row->available_qty, $row->used_qty, $row->status]);
            }
            fclose($handle);
        }, 'wastage.csv', ['Content-Type' => 'text/csv']);
    }
}
