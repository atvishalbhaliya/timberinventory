<?php

namespace App\Services\Production;

use App\Services\AuditLogService;
use App\Services\Inventory\InventoryTransactionService;
use App\Services\Inventory\StockSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WastageReuseService
{
    private const REFERENCE_TYPE = 'Wastage Reuse';

    public function __construct(
        private readonly InventoryTransactionService $transactions,
        private readonly StockSummaryService $summary,
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

        return $query->orderByDesc('wastage_reuse_master.reuse_id')->limit(1000)->get();
    }

    public function find(Request $request, int $id): object
    {
        $reuse = $this->baseQuery($request)->where('wastage_reuse_master.reuse_id', $id)->firstOrFail();
        $reuse->details = $this->reuseDetails($id);

        return $reuse;
    }

    public function previewNextNumber(Request $request): string
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $request->query('branch_id'));
        if ($branchId <= 0) {
            $branchId = (int) DB::table('branch_master')->where('tenant_id', $user->tenant_id)->orderBy('branch_id')->value('branch_id');
        }

        return $this->nextNumber((int) $user->tenant_id, $branchId);
    }

    public function create(Request $request, array $data): object
    {
        return DB::transaction(function () use ($request, $data): object {
            $payload = $this->payload($request, $data, true);
            $id = DB::table('wastage_reuse_master')->insertGetId($payload['master'], 'reuse_id');
            $this->replaceDetails($id, $payload['details']);
            $this->audit->record($request, 'wastage_reuse_master', 'create', $id, null, $payload);

            return $this->find($request, $id);
        });
    }

    public function update(Request $request, int $id, array $data): object
    {
        return DB::transaction(function () use ($request, $id, $data): object {
            $existing = $this->scopedForUpdate($request, $id);
            $this->ensureDraft($existing);
            $payload = $this->payload($request, $data, false);
            DB::table('wastage_reuse_master')->where('reuse_id', $id)->update($payload['master']);
            $this->replaceDetails($id, $payload['details']);
            $this->audit->record($request, 'wastage_reuse_master', 'update', $id, $existing, $payload);

            return $this->find($request, $id);
        });
    }

    public function delete(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id): void {
            $existing = $this->scopedForUpdate($request, $id);
            $this->ensureDraft($existing);
            DB::table('wastage_reuse_detail')->where('reuse_id', $id)->delete();
            DB::table('wastage_reuse_master')->where('reuse_id', $id)->update([
                'deleted_at' => now(),
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'wastage_reuse_master', 'delete', $id, $existing);
        });
    }

    public function post(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $reuse = $this->scopedForUpdate($request, $id);
            if ($reuse->status === 'Posted') {
                throw ValidationException::withMessages(['status' => 'Wastage reuse is already posted.']);
            }
            $this->ensureDraft($reuse);

            $source = DB::table('wastage_stock')
                ->where('wastage_stock_id', $reuse->source_wastage_stock_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($source->status !== 'Posted' || $source->wastage_type !== 'Reusable') {
                throw ValidationException::withMessages(['source_wastage_stock_id' => 'Selected wastage is not reusable posted stock.']);
            }
            if (! $reuse->source_location_id || ! $source->location_id) {
                throw ValidationException::withMessages(['source_wastage_stock_id' => 'Selected wastage stock has no source location. Please select wastage created with a valid location.']);
            }
            if ((float) $source->available_qty < (float) $reuse->consumed_qty) {
                throw ValidationException::withMessages(['consumed_qty' => 'Insufficient available wastage stock.']);
            }

            $stockQty = $this->summary->currentQty((int) $reuse->tenant_id, (int) $reuse->branch_id, (int) $reuse->source_item_id, (int) $reuse->source_location_id, 'Wastage');
            if ($stockQty < (float) $reuse->consumed_qty) {
                throw ValidationException::withMessages(['consumed_qty' => 'Insufficient wastage stock in source location.']);
            }

            $this->transactions->record([
                'tenant_id' => $reuse->tenant_id,
                'branch_id' => $reuse->branch_id,
                'item_id' => $reuse->source_item_id,
                'location_id' => $reuse->source_location_id,
                'transaction_date' => $reuse->reuse_date.' 00:00:00',
                'transaction_type' => 'Wastage Reuse Consumption',
                'stock_type' => 'Wastage',
                'reference_id' => $id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => 0,
                'qty_out' => $reuse->consumed_qty,
                'user_id' => $request->user()?->id,
            ]);

            foreach ($this->reuseDetails($id) as $detail) {
                $this->transactions->record([
                    'tenant_id' => $reuse->tenant_id,
                    'branch_id' => $reuse->branch_id,
                    'item_id' => $detail->item_id,
                    'location_id' => $reuse->destination_location_id,
                    'transaction_date' => $reuse->reuse_date.' 00:00:00',
                    'transaction_type' => 'Wastage Reuse Output',
                    'reference_id' => $id,
                    'reference_type' => self::REFERENCE_TYPE,
                    'qty_in' => $detail->qty,
                    'qty_out' => 0,
                    'rate' => $detail->rate,
                    'amount' => $detail->amount,
                    'user_id' => $request->user()?->id,
                ]);
            }

            DB::table('wastage_stock')->where('wastage_stock_id', $source->wastage_stock_id)->update([
                'available_qty' => (float) $source->available_qty - (float) $reuse->consumed_qty,
                'used_qty' => (float) $source->used_qty + (float) $reuse->consumed_qty,
                'balance_qty' => (float) $source->balance_qty - (float) $reuse->consumed_qty,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);

            DB::table('wastage_reuse_master')->where('reuse_id', $id)->update([
                'status' => 'Posted',
                'posted_by' => $request->user()?->id,
                'posted_at' => now(),
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'wastage_reuse_master', 'post', $id, $reuse, ['status' => 'Posted']);

            return $this->find($request, $id);
        });
    }

    public function cancel(Request $request, int $id, ?string $reason = null): object
    {
        return DB::transaction(function () use ($request, $id, $reason): object {
            $reuse = $this->scopedForUpdate($request, $id);
            if ($reuse->status !== 'Posted') {
                throw ValidationException::withMessages(['status' => 'Only posted wastage reuse can be cancelled.']);
            }

            $source = DB::table('wastage_stock')
                ->where('wastage_stock_id', $reuse->source_wastage_stock_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->transactions->record([
                'tenant_id' => $reuse->tenant_id,
                'branch_id' => $reuse->branch_id,
                'item_id' => $reuse->source_item_id,
                'location_id' => $reuse->source_location_id,
                'transaction_date' => now(),
                'transaction_type' => 'Wastage Reuse Consumption Reversal',
                'stock_type' => 'Wastage',
                'reference_id' => $id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => $reuse->consumed_qty,
                'qty_out' => 0,
                'user_id' => $request->user()?->id,
            ]);

            foreach ($this->reuseDetails($id) as $detail) {
                $this->transactions->record([
                    'tenant_id' => $reuse->tenant_id,
                    'branch_id' => $reuse->branch_id,
                    'item_id' => $detail->item_id,
                    'location_id' => $reuse->destination_location_id,
                    'transaction_date' => now(),
                    'transaction_type' => 'Wastage Reuse Output Reversal',
                    'reference_id' => $id,
                    'reference_type' => self::REFERENCE_TYPE,
                    'qty_in' => 0,
                    'qty_out' => $detail->qty,
                    'user_id' => $request->user()?->id,
                ]);
            }

            DB::table('wastage_stock')->where('wastage_stock_id', $source->wastage_stock_id)->update([
                'available_qty' => (float) $source->available_qty + (float) $reuse->consumed_qty,
                'used_qty' => max(0, (float) $source->used_qty - (float) $reuse->consumed_qty),
                'balance_qty' => (float) $source->balance_qty + (float) $reuse->consumed_qty,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);

            DB::table('wastage_reuse_master')->where('reuse_id', $id)->update([
                'status' => 'Cancelled',
                'cancelled_by' => $request->user()?->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'wastage_reuse_master', 'cancel', $id, $reuse, ['reason' => $reason]);

            return $this->find($request, $id);
        });
    }

    private function payload(Request $request, array $data, bool $creating): array
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $data['branch_id']);
        $source = DB::table('wastage_stock')
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $branchId)
            ->where('wastage_stock_id', $data['source_wastage_stock_id'])
            ->firstOrFail();

        if ($source->status !== 'Posted' || $source->wastage_type !== 'Reusable') {
            throw ValidationException::withMessages(['source_wastage_stock_id' => 'Select posted reusable wastage stock.']);
        }
        if (! $source->location_id) {
            throw ValidationException::withMessages(['source_wastage_stock_id' => 'Selected wastage stock has no source location. Please select wastage created with a valid location.']);
        }
        if ((float) $source->available_qty <= 0) {
            throw ValidationException::withMessages(['source_wastage_stock_id' => 'Selected wastage stock has no available quantity.']);
        }

        $details = $this->normalizeDetails($data);
        if ($details === []) {
            if (empty($data['produced_item_id']) || empty($data['produced_qty'])) {
                throw ValidationException::withMessages(['details' => 'Add at least one produced item row.']);
            }

            $producedQty = (float) $data['produced_qty'];
            $productionCost = (float) ($data['production_cost'] ?? 0);
            $details = [[
                'line_no' => 1,
                'item_id' => (int) $data['produced_item_id'],
                'qty' => $producedQty,
                'rate' => $producedQty > 0 ? round($productionCost / $producedQty, 2) : 0,
                'amount' => $productionCost,
            ]];
        }

        $details = collect($details)->map(function (array $line) use ($user): array {
            $line['created_by'] = $user->id;
            $line['updated_by'] = $user->id;
            $line['created_at'] = now();
            $line['updated_at'] = now();

            return $line;
        })->all();

        $totalQty = round(array_sum(array_map(fn (array $line): float => (float) $line['qty'], $details)), 3);
        $totalCost = round(array_sum(array_map(fn (array $line): float => (float) $line['amount'], $details)), 2);
        $primaryItemId = (int) ($data['produced_item_id'] ?: $details[0]['item_id']);

        $payload = [
            'tenant_id' => $user->tenant_id,
            'branch_id' => $branchId,
            'reuse_no' => $data['reuse_no'] ?? $this->nextNumber((int) $user->tenant_id, $branchId),
            'reuse_date' => $data['reuse_date'],
            'source_wastage_stock_id' => $source->wastage_stock_id,
            'source_item_id' => $source->item_id,
            'source_location_id' => $source->location_id,
            'consumed_qty' => $data['consumed_qty'],
            'produced_item_id' => $primaryItemId,
            'destination_location_id' => $data['destination_location_id'],
            'team_id' => $data['team_id'] ?? null,
            'produced_qty' => $totalQty,
            'production_cost' => $totalCost,
            'status' => 'Draft',
            'remarks' => $data['remarks'] ?? null,
            'updated_by' => $user->id,
            'updated_at' => now(),
        ];

        if ($creating) {
            $payload['created_by'] = $user->id;
            $payload['created_at'] = now();
        }

        return ['master' => $payload, 'details' => $details];
    }

    private function normalizeDetails(array $data): array
    {
        $details = collect($data['details'] ?? [])
            ->filter(fn ($line) => is_array($line))
            ->map(function (array $line, int $index): array {
                $itemId = (int) ($line['item_id'] ?? 0);
                $qty = (float) ($line['qty'] ?? 0);
                $rate = (float) ($line['rate'] ?? 0);
                $amount = array_key_exists('amount', $line) ? (float) $line['amount'] : round($qty * $rate, 2);

                if ($itemId <= 0) {
                    throw ValidationException::withMessages(['details' => 'Select an item for every produced row.']);
                }
                if ($qty <= 0) {
                    throw ValidationException::withMessages(['details' => 'Produced item quantity must be greater than zero.']);
                }

                return [
                    'line_no' => $index + 1,
                    'item_id' => $itemId,
                    'qty' => $qty,
                    'rate' => $rate,
                    'amount' => $amount,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        return $details;
    }

    private function replaceDetails(int $reuseId, array $details): void
    {
        DB::table('wastage_reuse_detail')->where('reuse_id', $reuseId)->delete();

        foreach ($details as $detail) {
            $detail['reuse_id'] = $reuseId;
            DB::table('wastage_reuse_detail')->insert($detail);
        }
    }

    private function reuseDetails(int $reuseId)
    {
        return DB::table('wastage_reuse_detail')
            ->leftJoin('item_master', 'wastage_reuse_detail.item_id', '=', 'item_master.item_id')
            ->where('wastage_reuse_detail.reuse_id', $reuseId)
            ->orderBy('wastage_reuse_detail.line_no')
            ->select('wastage_reuse_detail.*', 'item_master.item_name', 'item_master.item_code')
            ->get();
    }

    private function baseQuery(Request $request)
    {
        $user = $request->user();

        return DB::table('wastage_reuse_master')
            ->leftJoin('item_master as source_item', 'wastage_reuse_master.source_item_id', '=', 'source_item.item_id')
            ->leftJoin('item_master as produced_item', 'wastage_reuse_master.produced_item_id', '=', 'produced_item.item_id')
            ->leftJoin('storage_location_master as source_location', 'wastage_reuse_master.source_location_id', '=', 'source_location.location_id')
            ->leftJoin('storage_location_master as destination_location', 'wastage_reuse_master.destination_location_id', '=', 'destination_location.location_id')
            ->leftJoin('team_master', 'wastage_reuse_master.team_id', '=', 'team_master.team_id')
            ->whereNull('wastage_reuse_master.deleted_at')
            ->where('wastage_reuse_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('wastage_reuse_master.branch_id', $user->branch_id))
            ->select(
                'wastage_reuse_master.*',
                'source_item.item_name as source_item_name',
                'source_item.item_code as source_item_code',
                'produced_item.item_name as produced_item_name',
                 'produced_item.item_code as produced_item_code',
                'source_location.location_name as source_location_name',
                'destination_location.location_name as destination_location_name',
                'team_master.team_name',
               
            );
    }

    private function applyFilters($query, Request $request): void
    {
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('wastage_reuse_master.reuse_no', 'like', "%{$search}%")
                    ->orWhere('source_item.item_name', 'like', "%{$search}%")
                    ->orWhere('produced_item.item_name', 'like', "%{$search}%")
                    ->orWhere('wastage_reuse_master.status', 'like', "%{$search}%");
            });
        }

        foreach (['status', 'team_id', 'source_wastage_stock_id', 'source_item_id', 'produced_item_id', 'source_location_id', 'destination_location_id'] as $field) {
            if ($request->filled($field)) {
                $query->where('wastage_reuse_master.'.$field, $request->query($field));
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('wastage_reuse_master.reuse_date', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('wastage_reuse_master.reuse_date', '<=', $request->query('date_to'));
        }
    }

    private function applySorting($query, Request $request): void
    {
        $columns = [
            'reuse_no' => 'wastage_reuse_master.reuse_no',
            'reuse_date' => 'wastage_reuse_master.reuse_date',
            'source_item_name' => 'source_item.item_name',
            'consumed_qty' => 'wastage_reuse_master.consumed_qty',
            'produced_item_name' => 'produced_item.item_name',
            'produced_qty' => 'wastage_reuse_master.produced_qty',
            'team_name' => 'team_master.team_name',
            'status' => 'wastage_reuse_master.status',
            'created_at' => 'wastage_reuse_master.created_at',
        ];
        $sortBy = $columns[$request->query('sort_by')] ?? 'wastage_reuse_master.reuse_id';
        $direction = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $direction)->orderBy('wastage_reuse_master.reuse_id', $direction);
    }

    private function scopedForUpdate(Request $request, int $id): object
    {
        $user = $request->user();

        return DB::table('wastage_reuse_master')
            ->whereNull('deleted_at')
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('branch_id', $user->branch_id))
            ->where('reuse_id', $id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensureDraft(object $reuse): void
    {
        if ($reuse->status !== 'Draft') {
            throw ValidationException::withMessages(['status' => 'Only draft wastage reuse entries can be changed.']);
        }
    }

    private function nextNumber(int $tenantId, int $branchId): string
    {
        $next = DB::table('wastage_reuse_master')->where('tenant_id', $tenantId)->where('branch_id', $branchId)->lockForUpdate()->count() + 1;

        return 'WRE-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
