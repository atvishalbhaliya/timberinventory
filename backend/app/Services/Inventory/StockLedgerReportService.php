<?php

namespace App\Services\Inventory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockLedgerReportService
{
    public function paginate(Request $request)
    {
        return $this->query($request)
            ->tap(fn ($query) => $this->applySorting($query, $request))
            ->paginate((int) min(max((int) $request->query('per_page', 25), 1), 200));
    }

    public function export(Request $request)
    {
        return $this->query($request)
            ->orderBy('stock_ledger.transaction_date')
            ->orderBy('stock_ledger.ledger_id')
            ->limit(5000)
            ->get();
    }

    private function query(Request $request)
    {
        $user = $request->user();
        $query = DB::table('stock_ledger')
            ->join('item_master', 'stock_ledger.item_id', '=', 'item_master.item_id')
            ->leftJoin('material_type_master', 'item_master.material_type_id', '=', 'material_type_master.material_type_id')
            ->leftJoin('branch_master', 'stock_ledger.branch_id', '=', 'branch_master.branch_id')
            ->leftJoin('storage_location_master', 'stock_ledger.location_id', '=', 'storage_location_master.location_id')
            ->leftJoin('users', 'stock_ledger.created_by', '=', 'users.id')
            ->where('stock_ledger.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('stock_ledger.branch_id', $user->branch_id))
            ->select([
                'stock_ledger.ledger_id',
                'stock_ledger.transaction_date',
                'stock_ledger.transaction_type',
                'stock_ledger.reference_type',
                'stock_ledger.reference_id',
                'stock_ledger.stock_type',
                'stock_ledger.qty_in',
                'stock_ledger.qty_out',
                'stock_ledger.balance_qty as running_balance',
                'item_master.item_code',
                'item_master.item_name',
                'material_type_master.material_type_name',
                'branch_master.branch_name',
                'storage_location_master.location_name',
                'users.full_name as created_by_name',
            ]);

        foreach ([
            'item_id' => 'stock_ledger.item_id',
            'material_type_id' => 'item_master.material_type_id',
            'branch_id' => 'stock_ledger.branch_id',
            'location_id' => 'stock_ledger.location_id',
            'stock_type' => 'stock_ledger.stock_type',
            'reference_type' => 'stock_ledger.reference_type',
        ] as $input => $column) {
            if ($request->filled($input)) {
                $query->where($column, $request->query($input));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('stock_ledger.transaction_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('stock_ledger.transaction_date', '<=', $request->query('date_to'));
        }

        if ($request->filled('reference_number')) {
            $query->where('stock_ledger.reference_id', $request->query('reference_number'));
        }

        if ($request->filled('ledger_date')) {
            $query->whereDate('stock_ledger.transaction_date', $request->query('ledger_date'));
        }

        if ($request->filled('reference_search')) {
            $query->where('stock_ledger.reference_id', 'like', '%'.trim((string) $request->query('reference_search')).'%');
        }

        if ($request->filled('item_search')) {
            $search = trim((string) $request->query('item_search'));
            $query->where(function ($query) use ($search): void {
                $query->where('item_master.item_name', 'like', "%{$search}%")
                    ->orWhere('item_master.item_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('created_by')) {
            $query->where('users.full_name', 'like', '%'.trim((string) $request->query('created_by')).'%');
        }

        if ($request->filled('qty_in_min')) {
            $query->where('stock_ledger.qty_in', '>=', (float) $request->query('qty_in_min'));
        }

        if ($request->filled('qty_out_min')) {
            $query->where('stock_ledger.qty_out', '>=', (float) $request->query('qty_out_min'));
        }

        if ($request->filled('balance_min')) {
            $query->where('stock_ledger.balance_qty', '>=', (float) $request->query('balance_min'));
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('item_master.item_name', 'like', "%{$search}%")
                    ->orWhere('item_master.item_code', 'like', "%{$search}%")
                    ->orWhere('stock_ledger.reference_type', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function applySorting($query, Request $request): void
    {
        $columns = [
            'transaction_date' => 'stock_ledger.transaction_date',
            'reference_type' => 'stock_ledger.reference_type',
            'reference_id' => 'stock_ledger.reference_id',
            'stock_type' => 'stock_ledger.stock_type',
            'item_name' => 'item_master.item_name',
            'material_type_name' => 'material_type_master.material_type_name',
            'branch_name' => 'branch_master.branch_name',
            'location_name' => 'storage_location_master.location_name',
            'qty_in' => 'stock_ledger.qty_in',
            'qty_out' => 'stock_ledger.qty_out',
            'running_balance' => 'stock_ledger.balance_qty',
            'created_by' => 'users.full_name',
        ];
        $sortBy = $columns[$request->query('sort_by')] ?? 'stock_ledger.transaction_date';
        $direction = strtolower((string) $request->query('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $direction)->orderBy('stock_ledger.ledger_id', $direction);
    }
}
