<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        $summaryRows = DB::table('team_ledger')
            ->leftJoin('team_master', 'team_ledger.team_id', '=', 'team_master.team_id')
            ->where('team_ledger.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('team_ledger.branch_id', $user->branch_id))
            ->where('team_ledger.transaction_type', 'Dispatch')
            ->whereMonth('team_ledger.transaction_date', $month)
            ->whereYear('team_ledger.transaction_date', $year)
            ->groupBy('team_ledger.team_id', 'team_master.rate_per_pallet', 'team_master.tds_percent')
            ->select([
                'team_ledger.team_id',
                DB::raw('SUM(team_ledger.qty) as dispatch_qty'),
                DB::raw('MAX(team_master.rate_per_pallet) as rate_per_pallet'),
                DB::raw('MAX(team_master.tds_percent) as tds_percent'),
            ])
            ->get();

        $count = 0;
        DB::transaction(function () use ($summaryRows, $user, $month, $year, &$count): void {
            foreach ($summaryRows as $row) {
                $dispatchQty = (float) $row->dispatch_qty;
                $rate = (float) $row->rate_per_pallet;
                $tdsPercent = (float) $row->tds_percent;
                $gross = $dispatchQty * $rate;
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
        $user = $request->user();

        $query = DB::table('team_payment_summary')
            ->leftJoin('team_master', 'team_payment_summary.team_id', '=', 'team_master.team_id')
            ->leftJoin('users as creator', 'team_payment_summary.created_by', '=', 'creator.id')
            ->leftJoin('users as updater', 'team_payment_summary.updated_by', '=', 'updater.id')
            ->where('team_payment_summary.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('team_payment_summary.branch_id', $user->branch_id))
            ->select([
                'team_payment_summary.payment_id',
                'team_payment_summary.payment_month',
                'team_payment_summary.payment_year',
                'team_payment_summary.dispatch_qty',
                'team_payment_summary.gross_amount',
                'team_payment_summary.tds_amount',
                'team_payment_summary.net_payable',
                'team_master.team_code',
                'team_master.team_name',
                'team_master.rate_per_pallet',
                'team_master.tds_percent',
                'creator.full_name as created_by_name',
                'updater.full_name as updated_by_name',
            ]);

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
}
