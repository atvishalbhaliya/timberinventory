<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeamLedgerService
{
    public function recordProduction(array $movement): int
    {
        return DB::table('team_ledger')->insertGetId([
            'tenant_id' => $movement['tenant_id'],
            'branch_id' => $movement['branch_id'],
            'team_id' => $movement['team_id'],
            'pallet_model_id' => $movement['pallet_model_id'],
            'transaction_type' => 'Production',
            'transaction_date' => $movement['transaction_date'],
            'qty' => $movement['qty'],
            'amount' => $movement['amount'] ?? $movement['labour_rate'] ?? $movement['production_rate'] ?? 0,
            'reference_type' => $movement['reference_type'] ?? null,
            'reference_id' => $movement['reference_id'] ?? null,
            'created_by' => $movement['user_id'] ?? null,
            'updated_by' => $movement['user_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'ledger_id');
    }

    public function recordDispatch(array $movement): int
    {
        $rate = (float) ($movement['labour_rate'] ?? $movement['rate_per_pallet'] ?? 0);
        $amount = $movement['amount'] ?? ((float) $movement['qty'] * $rate);

        return DB::table('team_ledger')->insertGetId([
            'tenant_id' => $movement['tenant_id'],
            'branch_id' => $movement['branch_id'],
            'team_id' => $movement['team_id'],
            'pallet_model_id' => $movement['pallet_model_id'] ?? null,
            'transaction_type' => 'Dispatch',
            'transaction_date' => $movement['transaction_date'],
            'qty' => $movement['qty'],
            'amount' => $amount,
            'reference_type' => $movement['reference_type'] ?? 'Dispatch Challan',
            'reference_id' => $movement['reference_id'] ?? null,
            'created_by' => $movement['user_id'] ?? null,
            'updated_by' => $movement['user_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'ledger_id');
    }

    public function deleteByReference(string $referenceType, int $referenceId): int
    {
        return DB::table('team_ledger')
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->delete();
    }

    public function paginate(Request $request)
    {
        $user = $request->user();

        $query = DB::table('team_ledger')
            ->leftJoin('team_master', 'team_ledger.team_id', '=', 'team_master.team_id')
            ->leftJoin('item_master', 'team_ledger.pallet_model_id', '=', 'item_master.item_id')
            ->leftJoin('users as creator', 'team_ledger.created_by', '=', 'creator.id')
            ->where('team_ledger.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('team_ledger.branch_id', $user->branch_id))
            ->select([
                'team_ledger.ledger_id',
                'team_ledger.transaction_date',
                'team_ledger.transaction_type',
                'team_ledger.qty',
                'team_ledger.amount',
                'team_ledger.reference_type',
                'team_ledger.reference_id',
                'team_master.team_code',
                'team_master.team_name',
                'team_master.rate_per_pallet',
                'team_master.tds_percent',
                'item_master.item_name as pallet_model_name',
                'creator.full_name as created_by_name',
            ]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->orWhere('team_master.team_name', 'like', "%{$search}%")
                    ->orWhere('team_master.team_code', 'like', "%{$search}%")
                    ->orWhere('item_master.item_name', 'like', "%{$search}%")
                    ->orWhere('team_ledger.transaction_type', 'like', "%{$search}%")
                    ->orWhere('team_ledger.reference_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('team_id')) {
            $query->where('team_ledger.team_id', $request->query('team_id'));
        }

        if ($request->filled('transaction_type')) {
            $query->where('team_ledger.transaction_type', $request->query('transaction_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('team_ledger.transaction_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('team_ledger.transaction_date', '<=', $request->query('date_to'));
        }

        $sortMap = [
            'transaction_date' => 'team_ledger.transaction_date',
            'team_name' => 'team_master.team_name',
            'team_code' => 'team_master.team_code',
            'pallet_model_name' => 'pallet_model_master.model_name',
            'transaction_type' => 'team_ledger.transaction_type',
            'qty' => 'team_ledger.qty',
            'amount' => 'team_ledger.amount',
            'created_by_name' => 'creator.full_name',
        ];

        $sortBy = $sortMap[$request->query('sort_by', 'transaction_date')] ?? 'team_ledger.transaction_date';
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
