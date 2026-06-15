<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Grns\StoreGRNRequest;
use App\Http\Requests\Api\V1\Grns\UpdateGRNRequest;
use App\Http\Resources\Api\V1\Grns\GRNResource;
use App\Services\Inventory\GRNService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GRNController extends Controller
{
    public function __construct(private readonly GRNService $grns)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'GRN records loaded.',
            'data' => $this->grns->paginate($request),
        ]);
    }

    public function store(StoreGRNRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'GRN created.',
            'data' => new GRNResource($this->grns->create($request, $request->validated())),
        ], 201);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Next GRN number generated.',
            'data' => [
                'grn_no' => $this->grns->previewNextNumber($request),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'GRN loaded.',
            'data' => new GRNResource($this->grns->find($request, $id)),
        ]);
    }

    public function update(UpdateGRNRequest $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'GRN updated.',
            'data' => new GRNResource($this->grns->update($request, $id, $request->validated())),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->grns->delete($request, $id);

        return response()->json([
            'success' => true,
            'message' => 'GRN deleted.',
            'data' => [],
        ]);
    }

    public function post(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'GRN posted.',
            'data' => new GRNResource($this->grns->post($request, $id)),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'GRN cancelled.',
            'data' => new GRNResource($this->grns->cancel($request, $id)),
        ]);
    }
}
