<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WastageReuse\StoreWastageReuseRequest;
use App\Http\Requests\Api\V1\WastageReuse\UpdateWastageReuseRequest;
use App\Http\Resources\Api\V1\WastageReuseResource;
use App\Services\Production\WastageReuseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WastageReuseController extends Controller
{
    public function __construct(private readonly WastageReuseService $reuse)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage reuse records loaded.', 'data' => $this->reuse->paginate($request)]);
    }

    public function store(StoreWastageReuseRequest $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage reuse draft created.', 'data' => new WastageReuseResource($this->reuse->create($request, $request->validated()))], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage reuse loaded.', 'data' => new WastageReuseResource($this->reuse->find($request, $id))]);
    }

    public function update(UpdateWastageReuseRequest $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage reuse updated.', 'data' => new WastageReuseResource($this->reuse->update($request, $id, $request->validated()))]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->reuse->delete($request, $id);

        return response()->json(['success' => true, 'message' => 'Wastage reuse deleted.', 'data' => []]);
    }

    public function post(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Wastage reuse posted.', 'data' => new WastageReuseResource($this->reuse->post($request, $id))]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);

        return response()->json(['success' => true, 'message' => 'Wastage reuse cancelled.', 'data' => new WastageReuseResource($this->reuse->cancel($request, $id, $data['reason'] ?? null))]);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Next wastage reuse number generated.', 'data' => ['reuse_no' => $this->reuse->previewNextNumber($request)]]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->reuse->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reuse No', 'Date', 'Source Item', 'Consumed Qty', 'Produced Item', 'Produced Qty', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->reuse_no, $row->reuse_date, $row->source_item_name, $row->consumed_qty, $row->produced_item_name, $row->produced_qty, $row->status]);
            }
            fclose($handle);
        }, 'wastage-reuse.csv', ['Content-Type' => 'text/csv']);
    }
}
