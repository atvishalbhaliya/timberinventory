<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Finance\TeamPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeamPaymentController extends Controller
{
    public function __construct(private readonly TeamPaymentService $payments)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $rebuildCount = $this->payments->rebuild($request, (int) $request->query('payment_month') ?: null, (int) $request->query('payment_year') ?: null);

        return response()->json([
            'success' => true,
            'message' => 'Team payments loaded.',
            'data' => $this->payments->paginate($request),
            'metrics' => ['rebuilt' => $rebuildCount],
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $month = $request->validate([
            'payment_month' => ['required', 'integer', 'between:1,12'],
            'payment_year' => ['required', 'integer', 'between:2000,2100'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment summary refreshed.',
            'data' => [
                'rebuilt' => $this->payments->rebuild($request, (int) $month['payment_month'], (int) $month['payment_year']),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->payments->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Team', 'Month', 'Year', 'Dispatch Qty', 'Gross Amount', 'TDS Amount', 'Net Payable']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->team_name,
                    $row->payment_month,
                    $row->payment_year,
                    $row->dispatch_qty,
                    $row->gross_amount,
                    $row->tds_amount,
                    $row->net_payable,
                ]);
            }
            fclose($handle);
        }, 'team-payments.csv', ['Content-Type' => 'text/csv']);
    }
}
