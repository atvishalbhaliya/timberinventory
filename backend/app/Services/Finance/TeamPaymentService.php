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

        $paymentTeamIds = DB::table('team_payment_entry')
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
            ->where('payment_month', $month)
            ->where('payment_year', $year)
            ->distinct()
            ->pluck('team_id')
            ->all();

        $summaryRows = collect($summaryRows)->keyBy('team_id');

        foreach ($paymentTeamIds as $teamId) {
            if ($summaryRows->has($teamId)) {
                continue;
            }

            $team = DB::table('team_master')->where('team_id', $teamId)->first();
            if (! $team) {
                continue;
            }

            $summaryRows->put($teamId, (object) [
                'team_id' => $teamId,
                'dispatch_qty' => 0,
                'rate_per_pallet' => $team->rate_per_pallet,
                'tds_percent' => $team->tds_percent,
            ]);
        }

        $count = 0;
        DB::transaction(function () use ($summaryRows, $user, $month, $year, &$count): void {
            foreach ($summaryRows as $row) {
                $dispatchQty = (float) $row->dispatch_qty;
                $rate = (float) $row->rate_per_pallet;
                $tdsPercent = (float) $row->tds_percent;
                $gross = $dispatchQty * $rate;
                $tds = $gross * $tdsPercent / 100;
                $net = $gross - $tds;
                $paid = (float) DB::table('team_payment_entry')
                    ->where('tenant_id', $user->tenant_id)
                    ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
                    ->where('team_id', $row->team_id)
                    ->where('payment_month', $month)
                    ->where('payment_year', $year)
                    ->sum('payment_amount');
                $pending = max(0, $net - $paid);

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
                        'paid_amount' => $paid,
                        'pending_amount' => $pending,
                        'last_payment_date' => DB::table('team_payment_entry')
                            ->where('tenant_id', $user->tenant_id)
                            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
                            ->where('team_id', $row->team_id)
                            ->where('payment_month', $month)
                            ->where('payment_year', $year)
                            ->max('payment_date'),
                        'last_payment_mode' => DB::table('team_payment_entry')
                            ->where('tenant_id', $user->tenant_id)
                            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
                            ->where('team_id', $row->team_id)
                            ->where('payment_month', $month)
                            ->where('payment_year', $year)
                            ->orderByDesc('payment_date')
                            ->orderByDesc('entry_id')
                            ->value('payment_mode'),
                        'last_payment_reference' => DB::table('team_payment_entry')
                            ->where('tenant_id', $user->tenant_id)
                            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
                            ->where('team_id', $row->team_id)
                            ->where('payment_month', $month)
                            ->where('payment_year', $year)
                            ->orderByDesc('payment_date')
                            ->orderByDesc('entry_id')
                            ->value('reference_no'),
                        'last_payment_note' => DB::table('team_payment_entry')
                            ->where('tenant_id', $user->tenant_id)
                            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
                            ->where('team_id', $row->team_id)
                            ->where('payment_month', $month)
                            ->where('payment_year', $year)
                            ->orderByDesc('payment_date')
                            ->orderByDesc('entry_id')
                            ->value('remarks'),
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
                'team_payment_summary.paid_amount',
                'team_payment_summary.pending_amount',
                'team_payment_summary.last_payment_date',
                'team_payment_summary.last_payment_mode',
                'team_payment_summary.last_payment_reference',
                'team_payment_summary.last_payment_note',
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
            'paid_amount' => 'team_payment_summary.paid_amount',
            'pending_amount' => 'team_payment_summary.pending_amount',
            'last_payment_date' => 'team_payment_summary.last_payment_date',
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
        $user = $request->user();

        return DB::table('team_payment_summary')
            ->leftJoin('team_master', 'team_payment_summary.team_id', '=', 'team_master.team_id')
            ->where('team_payment_summary.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('team_payment_summary.branch_id', $user->branch_id))
            ->where('team_payment_summary.payment_id', $paymentId)
            ->select([
                'team_payment_summary.*',
                'team_master.team_code',
                'team_master.team_name',
            ])
            ->firstOrFail();
    }

    public function paymentHistory(Request $request, int $paymentId): Collection
    {
        $user = $request->user();
        $summary = DB::table('team_payment_summary')
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
            ->where('payment_id', $paymentId)
            ->select(['team_id', 'payment_month', 'payment_year'])
            ->firstOrFail();

        return DB::table('team_payment_entry')
            ->where('team_payment_entry.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('team_payment_entry.branch_id', $user->branch_id))
            ->where('team_payment_entry.team_id', $summary->team_id)
            ->where('team_payment_entry.payment_month', $summary->payment_month)
            ->where('team_payment_entry.payment_year', $summary->payment_year)
            ->orderByDesc('team_payment_entry.payment_date')
            ->orderByDesc('team_payment_entry.entry_id')
            ->get();
    }

    public function addPayment(Request $request, int $paymentId, array $data): object
    {
        return DB::transaction(function () use ($request, $paymentId, $data): object {
            $summary = DB::table('team_payment_summary')
                ->where('tenant_id', $request->user()->tenant_id)
                ->when($request->user()->branch_id, fn ($builder) => $builder->where('branch_id', $request->user()->branch_id))
                ->where('payment_id', $paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            $pending = (float) $summary->pending_amount;
            if ($pending <= 0) {
                throw ValidationException::withMessages(['payment_amount' => 'This team payment has no pending balance.']);
            }

            $paymentAmount = round((float) $data['payment_amount'], 2);
            if ($paymentAmount <= 0) {
                throw ValidationException::withMessages(['payment_amount' => 'Payment amount must be greater than zero.']);
            }
            if ($paymentAmount > $pending) {
                throw ValidationException::withMessages(['payment_amount' => 'Payment amount cannot exceed the pending balance.']);
            }

            DB::table('team_payment_entry')->insert([
                'tenant_id' => $summary->tenant_id,
                'branch_id' => $summary->branch_id,
                'payment_id' => $summary->payment_id,
                'team_id' => $summary->team_id,
                'payment_month' => $summary->payment_month,
                'payment_year' => $summary->payment_year,
                'payment_amount' => $paymentAmount,
                'payment_date' => $data['payment_date'],
                'payment_mode' => $data['payment_mode'],
                'reference_no' => $data['reference_no'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $paidAmount = (float) DB::table('team_payment_entry')
                ->where('tenant_id', $summary->tenant_id)
                ->where('branch_id', $summary->branch_id)
                ->where('team_id', $summary->team_id)
                ->where('payment_month', $summary->payment_month)
                ->where('payment_year', $summary->payment_year)
                ->sum('payment_amount');

            $lastEntry = DB::table('team_payment_entry')
                ->where('tenant_id', $summary->tenant_id)
                ->where('branch_id', $summary->branch_id)
                ->where('team_id', $summary->team_id)
                ->where('payment_month', $summary->payment_month)
                ->where('payment_year', $summary->payment_year)
                ->orderByDesc('payment_date')
                ->orderByDesc('entry_id')
                ->first();

            DB::table('team_payment_summary')
                ->where('payment_id', $summary->payment_id)
                ->update([
                    'paid_amount' => $paidAmount,
                    'pending_amount' => max(0, (float) $summary->net_payable - $paidAmount),
                    'last_payment_date' => $lastEntry?->payment_date,
                    'last_payment_mode' => $lastEntry?->payment_mode,
                    'last_payment_reference' => $lastEntry?->reference_no,
                    'last_payment_note' => $lastEntry?->remarks,
                    'updated_by' => $request->user()?->id,
                    'updated_at' => now(),
                ]);

            return $this->find($request, $summary->payment_id);
        });
    }
}
