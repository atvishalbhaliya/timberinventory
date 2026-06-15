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

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Team payment loaded.',
            'data' => [
                'summary' => $this->payments->find($request, $id),
                'entries' => $this->payments->paymentHistory($request, $id),
            ],
        ]);
    }

    public function pay(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_mode' => ['required', 'string', 'max:30', 'in:Cash,Bank Transfer,UPI,Cheque,Other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team payment saved.',
            'data' => $this->payments->addPayment($request, $id, $data),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->payments->export($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Team', 'Month', 'Year', 'Dispatch Qty', 'Gross Amount', 'TDS Amount', 'Net Payable', 'Paid Amount', 'Pending Amount', 'Last Payment Date']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->team_name,
                    $row->payment_month,
                    $row->payment_year,
                    $row->dispatch_qty,
                    $row->gross_amount,
                    $row->tds_amount,
                    $row->net_payable,
                    $row->paid_amount,
                    $row->pending_amount,
                    $row->last_payment_date,
                ]);
            }
            fclose($handle);
        }, 'team-payments.csv', ['Content-Type' => 'text/csv']);
    }
}
