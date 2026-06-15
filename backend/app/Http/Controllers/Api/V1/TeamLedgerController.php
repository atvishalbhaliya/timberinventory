<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Finance\TeamLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeamLedgerController extends Controller
{
    public function __construct(private readonly TeamLedgerService $ledger)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Team ledger loaded.',
            'data' => $this->ledger->paginate($request),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->ledger->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Team', 'Pallet Model', 'Type', 'Qty', 'Created By']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->transaction_date,
                    $row->team_name,
                    $row->pallet_model_name,
                    $row->transaction_type,
                    $row->qty,
                    $row->created_by_name,
                ]);
            }
            fclose($handle);
        }, 'team-ledger.csv', ['Content-Type' => 'text/csv']);
    }
}
