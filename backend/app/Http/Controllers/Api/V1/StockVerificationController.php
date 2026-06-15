<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StockVerifications\StoreStockVerificationRequest;
use App\Http\Requests\Api\V1\StockVerifications\UpdateStockVerificationRequest;
use App\Services\Inventory\StockVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockVerificationController extends Controller
{
    public function __construct(private readonly StockVerificationService $verifications)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock verifications loaded.', 'data' => $this->verifications->paginate($request)]);
    }

    public function store(StoreStockVerificationRequest $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock verification created.', 'data' => $this->verifications->create($request, $request->validated())], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock verification loaded.', 'data' => $this->verifications->find($request, $id)]);
    }

    public function update(UpdateStockVerificationRequest $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock verification updated.', 'data' => $this->verifications->update($request, $id, $request->validated())]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->verifications->delete($request, $id);

        return response()->json(['success' => true, 'message' => 'Stock verification deleted.', 'data' => []]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock verification submitted.', 'data' => $this->verifications->submit($request, $id)]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock verification approved.', 'data' => $this->verifications->approve($request, $id)]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Stock verification cancelled.', 'data' => $this->verifications->cancel($request, $id)]);
    }

    public function currentStock(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Current stock loaded.', 'data' => $this->verifications->currentStock($request)]);
    }
}
