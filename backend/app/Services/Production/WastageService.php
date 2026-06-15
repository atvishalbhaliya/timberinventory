<?php

namespace App\Services\Production;

use App\Services\AuditLogService;
use App\Services\Inventory\InventoryTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WastageService
{
    private const REFERENCE_TYPE = 'Wastage';

    public function __construct(
        private readonly InventoryTransactionService $transactions,
        private readonly AuditLogService $audit,
    ) {
    }

    public function paginate(Request $request)
    {
        $query = $this->baseQuery($request);
        $this->applyFilters($query, $request);

        $this->applySorting($query, $request);

        return $query
            ->paginate((int) min(max((int) $request->query('per_page', 10), 1), 100));
    }

    public function export(Request $request)
    {
        $query = $this->baseQuery($request);
        $this->applyFilters($query, $request);

        return $query->orderByDesc('wastage_stock.wastage_stock_id')->limit(1000)->get();
    }

    public function find(Request $request, int $id): object
    {
        return $this->baseQuery($request)->where('wastage_stock.wastage_stock_id', $id)->firstOrFail();
    }

    public function create(Request $request, array $data): object
    {
        return DB::transaction(function () use ($request, $data): object {
            $user = $request->user();
            $branchId = (int) ($user->branch_id ?: $data['branch_id']);
            $id = DB::table('wastage_stock')->insertGetId([
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branchId,
                'item_id' => $data['item_id'],
                'location_id' => $data['location_id'],
                'wastage_type' => $data['wastage_type'],
                'source_module' => 'Manual',
                'source_reference' => $data['source_reference'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'generated_qty' => $data['generated_qty'],
                'available_qty' => 0,
                'used_qty' => 0,
                'balance_qty' => 0,
                'status' => 'Draft',
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'wastage_stock_id');

            DB::table('wastage_stock')->where('wastage_stock_id', $id)->update(['source_id' => $id]);
            $this->audit->record($request, 'wastage_stock', 'create', $id, null, $data);

            return $this->find($request, $id);
        });
    }

    public function update(Request $request, int $id, array $data): object
    {
        return DB::transaction(function () use ($request, $id, $data): object {
            $existing = $this->scopedForUpdate($request, $id);
            if ($existing->status !== 'Draft') {
                throw ValidationException::withMessages(['status' => 'Only draft manual wastage can be changed.']);
            }
            if ($existing->source_module !== 'Manual') {
                throw ValidationException::withMessages(['source' => 'Production wastage can only be changed from production entry.']);
            }

            $user = $request->user();
            DB::table('wastage_stock')->where('wastage_stock_id', $id)->update([
                'item_id' => $data['item_id'],
                'location_id' => $data['location_id'],
                'wastage_type' => $data['wastage_type'],
                'source_reference' => $data['source_reference'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'generated_qty' => $data['generated_qty'],
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => $user->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'wastage_stock', 'update', $id, $existing, $data);

            return $this->find($request, $id);
        });
    }

    public function delete(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id): void {
            $existing = $this->scopedForUpdate($request, $id);
            if ($existing->status !== 'Draft' || $existing->source_module !== 'Manual') {
                throw ValidationException::withMessages(['status' => 'Only draft manual wastage can be deleted.']);
            }

            DB::table('wastage_stock')->where('wastage_stock_id', $id)->delete();
            $this->audit->record($request, 'wastage_stock', 'delete', $id, $existing);
        });
    }

    public function post(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $wastage = $this->scopedForUpdate($request, $id);
            if ($wastage->status === 'Posted') {
                throw ValidationException::withMessages(['status' => 'Wastage is already posted.']);
            }
            if ($wastage->status !== 'Draft' || $wastage->source_module !== 'Manual') {
                throw ValidationException::withMessages(['status' => 'Only draft manual wastage can be posted here.']);
            }

            $this->transactions->record([
                'tenant_id' => $wastage->tenant_id,
                'branch_id' => $wastage->branch_id,
                'item_id' => $wastage->item_id,
                'location_id' => $wastage->location_id,
                'transaction_date' => $wastage->transaction_date.' 00:00:00',
                'transaction_type' => 'Wastage Adjustment',
                'reference_id' => $id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => $wastage->generated_qty,
                'qty_out' => 0,
                'user_id' => $request->user()?->id,
            ]);

            DB::table('wastage_stock')->where('wastage_stock_id', $id)->update([
                'available_qty' => $wastage->generated_qty,
                'balance_qty' => $wastage->generated_qty,
                'status' => 'Posted',
                'posted_by' => $request->user()?->id,
                'posted_at' => now(),
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'wastage_stock', 'post', $id, $wastage, ['status' => 'Posted']);

            return $this->find($request, $id);
        });
    }

    public function cancel(Request $request, int $id, ?string $reason = null): object
    {
        return DB::transaction(function () use ($request, $id, $reason): object {
            $wastage = $this->scopedForUpdate($request, $id);
            if ($wastage->status !== 'Posted') {
                throw ValidationException::withMessages(['status' => 'Only posted wastage can be cancelled.']);
            }
            if ((float) $wastage->used_qty > 0) {
                throw ValidationException::withMessages(['status' => 'Wastage already consumed by reuse cannot be cancelled.']);
            }

            $this->transactions->record([
                'tenant_id' => $wastage->tenant_id,
                'branch_id' => $wastage->branch_id,
                'item_id' => $wastage->item_id,
                'location_id' => $wastage->location_id,
                'transaction_date' => now(),
                'transaction_type' => 'Wastage Reversal',
                'reference_id' => $id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => 0,
                'qty_out' => $wastage->available_qty,
                'user_id' => $request->user()?->id,
            ]);

            DB::table('wastage_stock')->where('wastage_stock_id', $id)->update([
                'available_qty' => 0,
                'balance_qty' => 0,
                'status' => 'Cancelled',
                'cancelled_by' => $request->user()?->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'wastage_stock', 'cancel', $id, $wastage, ['reason' => $reason]);

            return $this->find($request, $id);
        });
    }

    private function baseQuery(Request $request)
    {
        $user = $request->user();

        return DB::table('wastage_stock')
            ->leftJoin('item_master', 'wastage_stock.item_id', '=', 'item_master.item_id')
            ->leftJoin('storage_location_master', 'wastage_stock.location_id', '=', 'storage_location_master.location_id')
            ->where('wastage_stock.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('wastage_stock.branch_id', $user->branch_id))
            ->select('wastage_stock.*', 'item_master.item_name', 'item_master.item_code', 'storage_location_master.location_name');
    }

    private function applyFilters($query, Request $request): void
    {
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('item_master.item_name', 'like', "%{$search}%")
                    ->orWhere('item_master.item_code', 'like', "%{$search}%")
                    ->orWhere('wastage_stock.source_reference', 'like', "%{$search}%")
                    ->orWhere('wastage_stock.status', 'like', "%{$search}%");
            });
        }

        foreach (['branch_id', 'item_id', 'location_id', 'source_module', 'source_id', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where('wastage_stock.'.$field, $request->query($field));
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('wastage_stock.transaction_date', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('wastage_stock.transaction_date', '<=', $request->query('date_to'));
        }
    }

    private function applySorting($query, Request $request): void
    {
        $columns = [
            'transaction_date' => 'wastage_stock.transaction_date',
            'item_name' => 'item_master.item_name',
            'location_name' => 'storage_location_master.location_name',
            'wastage_type' => 'wastage_stock.wastage_type',
            'source_module' => 'wastage_stock.source_module',
            'source_reference' => 'wastage_stock.source_reference',
            'generated_qty' => 'wastage_stock.generated_qty',
            'available_qty' => 'wastage_stock.available_qty',
            'used_qty' => 'wastage_stock.used_qty',
            'status' => 'wastage_stock.status',
            'created_at' => 'wastage_stock.created_at',
        ];
        $sortBy = $columns[$request->query('sort_by')] ?? 'wastage_stock.wastage_stock_id';
        $direction = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $direction)->orderBy('wastage_stock.wastage_stock_id', $direction);
    }

    private function scopedForUpdate(Request $request, int $id): object
    {
        $user = $request->user();

        return DB::table('wastage_stock')
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('branch_id', $user->branch_id))
            ->where('wastage_stock_id', $id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
