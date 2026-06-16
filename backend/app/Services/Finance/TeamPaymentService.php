<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeamPaymentService
{
    public function rebuild(Request $request, ?int $month = null, ?int $year = null): int
    {
        $user = $request->user();
        $month = $month ?: (int) now()->month;
        $year = $year ?: (int) now()->year;

        DB::table('team_payment_summary')
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
            ->where('payment_month', $month)
            ->where('payment_year', $year)
            ->delete();

        $summaryRows = DB::table('stock_ledger')
            ->leftJoin('team_master', 'stock_ledger.team_id', '=', 'team_master.team_id')
            ->where('stock_ledger.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('stock_ledger.branch_id', $user->branch_id))
            ->where('stock_ledger.transaction_type', 'Dispatch')
            ->where('stock_ledger.reference_type', 'Dispatch Challan')
            ->whereMonth('stock_ledger.transaction_date', $month)
            ->whereYear('stock_ledger.transaction_date', $year)
            ->whereNotNull('stock_ledger.team_id')
            ->groupBy('stock_ledger.team_id', 'team_master.tds_percent')
            ->select([
                'stock_ledger.team_id',
                DB::raw('SUM(stock_ledger.qty_out) as dispatch_qty'),
                DB::raw('SUM(COALESCE(stock_ledger.labour_charge, 0)) as gross_amount'),
                DB::raw('MAX(team_master.tds_percent) as tds_percent'),
            ])
            ->get();

        $summaryRows = collect($summaryRows)->keyBy('team_id');

        $count = 0;
        DB::transaction(function () use ($summaryRows, $user, $month, $year, &$count): void {
            foreach ($summaryRows as $row) {
                $dispatchQty = (float) $row->dispatch_qty;
                $gross = (float) $row->gross_amount;
                $tdsPercent = (float) $row->tds_percent;
                $tds = $gross * $tdsPercent / 100;
                $net = $gross - $tds;

                DB::table('team_payment_summary')->updateOrInsert(
                    [
                        'tenant_id' => $user->tenant_id,
                        'branch_id' => $user->branch_id,
                        'team_id' => $row->team_id,
                        'payment_month' => $month,
                        'payment_year' => $year,
                    ],
                    [
                        'dispatch_qty' => $dispatchQty,
                        'gross_amount' => $gross,
                        'tds_amount' => $tds,
                        'net_payable' => $net,
                        'created_by' => $user?->getKey(),
                        'updated_by' => $user?->getKey(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $count++;
            }
        });

        return $count;
    }

    public function paginate(Request $request)
    {
        $query = $this->summaryQuery($request);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->orWhere('team_master.team_name', 'like', "%{$search}%")
                    ->orWhere('team_master.team_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('team_id')) {
            $query->where('team_payment_summary.team_id', $request->query('team_id'));
        }

        if ($request->filled('payment_month')) {
            $query->where('team_payment_summary.payment_month', $request->query('payment_month'));
        }

        if ($request->filled('payment_year')) {
            $query->where('team_payment_summary.payment_year', $request->query('payment_year'));
        }

        $sortMap = [
            'team_name' => 'team_master.team_name',
            'payment_month' => 'team_payment_summary.payment_month',
            'payment_year' => 'team_payment_summary.payment_year',
            'dispatch_qty' => 'team_payment_summary.dispatch_qty',
            'gross_amount' => 'team_payment_summary.gross_amount',
            'tds_amount' => 'team_payment_summary.tds_amount',
            'net_payable' => 'team_payment_summary.net_payable',
            'paid_amount' => 'paid_amount',
            'pending_amount' => 'pending_amount',
        ];

        $sortBy = $sortMap[$request->query('sort_by', 'payment_year')] ?? 'team_payment_summary.payment_year';
        $sortDirection = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate((int) min(max((int) $request->query('per_page', 10), 1), 100));
    }

    public function export(Request $request): Collection
    {
        return $this->paginate($request)->getCollection();
    }

    public function find(Request $request, int $paymentId): object
    {
        return $this->summaryQuery($request)
            ->where('team_payment_summary.payment_id', $paymentId)
            ->firstOrFail();
    }

    public function paymentHistory(Request $request, int $paymentId): Collection
    {
        $summary = $this->find($request, $paymentId);

        return DB::table('team_payment_entry')
            ->leftJoin('users as creator', 'team_payment_entry.created_by', '=', 'creator.id')
            ->where('team_payment_entry.tenant_id', $summary->tenant_id)
            ->where('team_payment_entry.branch_id', $summary->branch_id)
            ->where('team_payment_entry.team_id', $summary->team_id)
            ->where('team_payment_entry.payment_month', $summary->payment_month)
            ->where('team_payment_entry.payment_year', $summary->payment_year)
            ->select([
                'team_payment_entry.entry_id',
                'team_payment_entry.payment_date',
                'team_payment_entry.payment_mode',
                'team_payment_entry.reference_no',
                'team_payment_entry.payment_amount',
                'team_payment_entry.remarks',
                'team_payment_entry.created_at',
                'creator.full_name as created_by_name',
            ])
            ->orderByDesc('team_payment_entry.payment_date')
            ->orderByDesc('team_payment_entry.entry_id')
            ->get();
    }

    public function addPayment(Request $request, int $paymentId, array $data): object
    {
        $summary = $this->find($request, $paymentId);
        $pendingAmount = (float) $summary->pending_amount;

        if ($pendingAmount <= 0) {
            throw ValidationException::withMessages([
                'payment_amount' => 'This team payment has no pending amount left.',
            ]);
        }

        $payload = validator($data, [
            'payment_amount' => ['required', 'numeric', 'gt:0', 'lte:'.$pendingAmount],
            'payment_date' => ['required', 'date'],
            'payment_mode' => ['required', 'string', 'max:30'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ])->validate();

        $user = $request->user();

        $entryId = DB::table('team_payment_entry')->insertGetId([
            'tenant_id' => $summary->tenant_id,
            'branch_id' => $summary->branch_id,
            'team_id' => $summary->team_id,
            'payment_month' => $summary->payment_month,
            'payment_year' => $summary->payment_year,
            'payment_amount' => $payload['payment_amount'],
            'payment_date' => $payload['payment_date'],
            'payment_mode' => $payload['payment_mode'],
            'reference_no' => $payload['reference_no'] ?? null,
            'remarks' => $payload['remarks'] ?? null,
            'created_by' => $user?->getKey(),
            'updated_by' => $user?->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'entry_id');

        return (object) [
            'entry_id' => $entryId,
            'paid_amount' => $payload['payment_amount'],
            'pending_amount' => max($pendingAmount - (float) $payload['payment_amount'], 0),
        ];
    }

    private function summaryQuery(Request $request)
    {
        $user = $request->user();

        $paymentTotals = DB::table('team_payment_entry')
            ->select([
                'tenant_id',
                'branch_id',
                'team_id',
                'payment_month',
                'payment_year',
                DB::raw('SUM(payment_amount) as paid_amount'),
            ])
            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
            ->groupBy('tenant_id', 'branch_id', 'team_id', 'payment_month', 'payment_year');

        return DB::table('team_payment_summary')
            ->leftJoin('team_master', 'team_payment_summary.team_id', '=', 'team_master.team_id')
            ->leftJoin('users as creator', 'team_payment_summary.created_by', '=', 'creator.id')
            ->leftJoin('users as updater', 'team_payment_summary.updated_by', '=', 'updater.id')
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join): void {
                $join->on('team_payment_summary.tenant_id', '=', 'payment_totals.tenant_id')
                    ->on('team_payment_summary.branch_id', '=', 'payment_totals.branch_id')
                    ->on('team_payment_summary.team_id', '=', 'payment_totals.team_id')
                    ->on('team_payment_summary.payment_month', '=', 'payment_totals.payment_month')
                    ->on('team_payment_summary.payment_year', '=', 'payment_totals.payment_year');
            })
            ->where('team_payment_summary.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('team_payment_summary.branch_id', $user->branch_id))
            ->select([
                'team_payment_summary.payment_id',
                'team_payment_summary.tenant_id',
                'team_payment_summary.branch_id',
                'team_payment_summary.team_id',
                'team_payment_summary.payment_month',
                'team_payment_summary.payment_year',
                'team_payment_summary.dispatch_qty',
                'team_payment_summary.gross_amount',
                'team_payment_summary.tds_amount',
                'team_payment_summary.net_payable',
                DB::raw('COALESCE(payment_totals.paid_amount, 0) as paid_amount'),
                DB::raw('GREATEST(team_payment_summary.net_payable - COALESCE(payment_totals.paid_amount, 0), 0) as pending_amount'),
                'team_master.team_code',
                'team_master.team_name',
                'team_master.rate_per_pallet',
                'team_master.tds_percent',
                'creator.full_name as created_by_name',
                'updater.full_name as updated_by_name',
            ]);
    }
}
