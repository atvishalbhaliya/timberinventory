<?php

namespace App\Services\Production;

use App\Services\AuditLogService;
use App\Services\Finance\TeamLedgerService;
use App\Services\Inventory\InventoryTransactionService;
use App\Services\Inventory\StockSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    private const STATUS_DRAFT = 'Draft';
    private const STATUS_POSTED = 'Posted';
    private const STATUS_CANCELLED = 'Cancelled';
    private const REFERENCE_TYPE = 'Production';

    public function __construct(
        private readonly InventoryTransactionService $transactions,
        private readonly StockSummaryService $summary,
        private readonly TeamLedgerService $teamLedger,
        private readonly AuditLogService $audit,
    ) {
    }

    public function paginate(Request $request)
    {
        $query = $this->baseQuery($request)
            ->leftJoin('bom_master', 'production_master.bom_id', '=', 'bom_master.bom_id')
            ->leftJoin('item_master', 'production_master.produced_item_id', '=', 'item_master.item_id')
            ->leftJoin('team_master', 'production_master.team_id', '=', 'team_master.team_id')
            ->select('production_master.*', 'bom_master.bom_no', 'bom_master.bom_name', 'item_master.item_name as produced_item_name', 'team_master.team_name');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('production_master.production_no', 'like', "%{$search}%")
                    ->orWhere('bom_master.bom_no', 'like', "%{$search}%")
                    ->orWhere('team_master.team_name', 'like', "%{$search}%")
                    ->orWhere('production_master.status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('production_master.status', $request->query('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('production_master.production_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('production_master.production_date', '<=', $request->query('date_to'));
        }

        if ($request->filled('bom_id')) {
            $query->where('production_master.bom_id', $request->query('bom_id'));
        }

        if ($request->filled('team_id')) {
            $query->where('production_master.team_id', $request->query('team_id'));
        }

        if ($request->filled('produced_item_id')) {
            $query->where('production_master.produced_item_id', $request->query('produced_item_id'));
        }

        if ($request->filled('production_no')) {
            $query->where('production_master.production_no', 'like', '%'.trim((string) $request->query('production_no')).'%');
        }

        if ($request->filled('production_date')) {
            $query->whereDate('production_master.production_date', $request->query('production_date'));
        }

        if ($request->filled('bom_search')) {
            $search = trim((string) $request->query('bom_search'));
            $query->where(function ($query) use ($search): void {
                $query->where('bom_master.bom_no', 'like', "%{$search}%")
                    ->orWhere('bom_master.bom_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('team_search')) {
            $query->where('team_master.team_name', 'like', '%'.trim((string) $request->query('team_search')).'%');
        }

        if ($request->filled('produced_item_search')) {
            $query->where('item_master.item_name', 'like', '%'.trim((string) $request->query('produced_item_search')).'%');
        }

        if ($request->filled('qty_min')) {
            $query->where('production_master.produced_qty', '>=', (float) $request->query('qty_min'));
        }

        $columns = [
            'production_no' => 'production_master.production_no',
            'production_date' => 'production_master.production_date',
            'bom_no' => 'bom_master.bom_no',
            'team_name' => 'team_master.team_name',
            'produced_item_name' => 'item_master.item_name',
            'produced_qty' => 'production_master.produced_qty',
            'status' => 'production_master.status',
            'created_at' => 'production_master.created_at',
        ];
        $sortBy = $columns[$request->query('sort_by')] ?? 'production_master.production_id';
        $direction = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $direction)
            ->orderByDesc('production_master.production_id')
            ->paginate((int) min(max((int) $request->query('per_page', 10), 1), 100));
    }

    public function find(Request $request, int $id): object
    {
        $production = $this->baseQuery($request)
            ->leftJoin('bom_master', 'production_master.bom_id', '=', 'bom_master.bom_id')
            ->leftJoin('item_master', 'production_master.produced_item_id', '=', 'item_master.item_id')
            ->leftJoin('team_master', 'production_master.team_id', '=', 'team_master.team_id')
            ->select('production_master.*', 'bom_master.bom_no', 'bom_master.bom_name', 'item_master.item_name as produced_item_name', 'team_master.team_name')
            ->where('production_master.production_id', $id)
            ->firstOrFail();

        $production->consumptions = $this->consumptions($id);
        $production->outputs = DB::table('production_output')->where('production_id', $id)->get();
        $production->wastages = $this->wastages($id);

        return $production;
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

    public function bomMaterials(Request $request, int $bomId): array
    {
        $bom = DB::table('bom_master')
            ->leftJoin('item_master as finished_item', 'bom_master.finished_item_id', '=', 'finished_item.item_id')
            ->where('bom_master.tenant_id', $request->user()->tenant_id)
            ->when($request->user()->branch_id, fn ($query) => $query->where('bom_master.branch_id', $request->user()->branch_id))
            ->where('bom_master.bom_id', $bomId)
            ->select('bom_master.*', 'finished_item.item_name as finished_item_name')
            ->firstOrFail();

        $qty = max((float) $request->query('produced_qty', 1), 1);
        $materials = DB::table('bom_material')
            ->leftJoin('item_master', 'bom_material.item_id', '=', 'item_master.item_id')
            ->leftJoin('uom_master', 'bom_material.uom_id', '=', 'uom_master.uom_id')
            ->where('bom_material.bom_id', $bomId)
            ->select('bom_material.*', 'item_master.item_name', 'uom_master.uom_name')
            ->get()
            ->map(fn ($row): array => [
                'item_id' => $row->item_id,
                'item_name' => $row->item_name,
                'uom_id' => $row->uom_id,
                'uom_name' => $row->uom_name,
                'required_qty' => round((float) $row->required_qty * $qty, 3),
                'wastage_qty' => round(((float) $row->required_qty * (float) $row->wastage_percent / 100) * $qty, 3),
                'consumed_qty' => round(((float) $row->required_qty + ((float) $row->required_qty * (float) $row->wastage_percent / 100)) * $qty, 3),
                'wastage_percent' => (float) $row->wastage_percent,
                'current_stock' => 0,
                'remarks' => $row->remarks,
            ])->all();

        return ['bom' => $bom, 'produced_item_id' => $bom->finished_item_id, 'materials' => $materials];
    }

    public function currentStock(Request $request): array
    {
        $data = $request->validate([
            'item_id' => ['required', 'integer'],
            'location_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: DB::table('storage_location_master')
            ->where('tenant_id', $user->tenant_id)
            ->where('location_id', $data['location_id'])
            ->value('branch_id'));

        return [
            'stock_type' => 'Fresh',
            'available_qty' => $this->summary->currentQty((int) $user->tenant_id, $branchId, (int) $data['item_id'], (int) $data['location_id'], 'Fresh'),
        ];
    }

    public function create(Request $request, array $data): object
    {
        return DB::transaction(function () use ($request, $data): object {
            $payload = $this->payload($request, $data, true);
            $productionId = DB::table('production_master')->insertGetId($payload['master'], 'production_id');
            $this->replaceRows($productionId, $payload);
            $this->audit->record($request, 'production_master', 'create', $productionId, null, $payload);

            return $this->find($request, $productionId);
        });
    }

    public function update(Request $request, int $id, array $data): object
    {
        return DB::transaction(function () use ($request, $id, $data): object {
            $existing = $this->scopedForUpdate($request, $id);
            $this->ensureDraft($existing);
            $payload = $this->payload($request, $data, false);
            DB::table('production_master')->where('production_id', $id)->update($payload['master']);
            $this->replaceRows($id, $payload);
            $this->audit->record($request, 'production_master', 'update', $id, $existing, $payload);

            return $this->find($request, $id);
        });
    }

    public function delete(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id): void {
            $existing = $this->scopedForUpdate($request, $id);
            $this->ensureDraft($existing);
            DB::table('production_wastage')->where('production_id', $id)->delete();
            DB::table('production_output')->where('production_id', $id)->delete();
            DB::table('production_consumption')->where('production_id', $id)->delete();
            DB::table('production_master')->where('production_id', $id)->delete();
            $this->audit->record($request, 'production_master', 'delete', $id, $existing);
        });
    }

    public function post(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $production = $this->scopedForUpdate($request, $id);

            if ($production->status === self::STATUS_POSTED) {
                throw ValidationException::withMessages(['status' => 'Production is already posted.']);
            }

            $this->ensureDraft($production);
            $this->ensurePostingStateIsClean($id);
            $this->ensureBomCanBePosted($request, (int) $production->bom_id);
            $consumptions = DB::table('production_consumption')
                ->leftJoin('item_master', 'production_consumption.item_id', '=', 'item_master.item_id')
                ->leftJoin('storage_location_master', 'production_consumption.location_id', '=', 'storage_location_master.location_id')
                ->where('production_consumption.production_id', $id)
                ->select('production_consumption.*', 'item_master.item_name', 'storage_location_master.location_name')
                ->lockForUpdate()
                ->get();
            if ($consumptions->isEmpty()) {
                throw ValidationException::withMessages(['consumptions' => 'At least one material consumption line is required.']);
            }

            foreach ($consumptions as $line) {
                $consumedQty = (float) $line->consumed_qty;
                $wastageQty = (float) ($line->wastage_qty ?? 0);
                $requiredQty = $consumedQty + $wastageQty;
                $available = $this->summary->currentQty((int) $production->tenant_id, (int) $production->branch_id, (int) $line->item_id, (int) $line->location_id, 'Fresh');
                if ($available < $requiredQty) {
                    $itemName = $line->item_name ?: ('Item '.$line->item_id);
                    $locationName = $line->location_name ?: ('Location '.$line->location_id);

                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$itemName} at {$locationName}. Required: ".number_format($requiredQty, 3).', Available: '.number_format($available, 3).'.',
                    ]);
                }

                $this->transactions->record([
                    'tenant_id' => $production->tenant_id,
                    'branch_id' => $production->branch_id,
                    'item_id' => $line->item_id,
                    'location_id' => $line->location_id,
                    'stock_type' => 'Fresh',
                    'transaction_date' => $production->production_date.' 00:00:00',
                    'transaction_type' => 'Production Consumption',
                    'reference_id' => $id,
                    'reference_type' => self::REFERENCE_TYPE,
                    'qty_in' => 0,
                    'qty_out' => $consumedQty,
                    'user_id' => $request->user()?->id,
                ]);

                if ($wastageQty > 0) {
                    $this->transactions->record([
                        'tenant_id' => $production->tenant_id,
                        'branch_id' => $production->branch_id,
                        'item_id' => $line->item_id,
                        'location_id' => $line->location_id,
                        'stock_type' => 'Fresh',
                        'transaction_date' => $production->production_date.' 00:00:00',
                        'transaction_type' => 'Production Wastage',
                        'reference_id' => $id,
                        'reference_type' => self::REFERENCE_TYPE,
                        'qty_in' => 0,
                        'qty_out' => $wastageQty,
                        'user_id' => $request->user()?->id,
                    ]);
                }
            }

            $this->transactions->record([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'item_id' => $production->produced_item_id,
                'location_id' => $production->fg_location_id,
                'stock_type' => 'Fresh',
                'transaction_date' => $production->production_date.' 00:00:00',
                'transaction_type' => 'Production Output',
                'reference_id' => $id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => $production->produced_qty,
                'qty_out' => 0,
                'rate' => $production->produced_qty > 0 ? ((float) $production->production_cost / (float) $production->produced_qty) : 0,
                'amount' => $production->production_cost,
                'user_id' => $request->user()?->id,
            ]);

            DB::table('production_output')->where('production_id', $id)->delete();
            DB::table('production_output')->insert([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'production_id' => $id,
                'item_id' => $production->produced_item_id,
                'location_id' => $production->fg_location_id,
                'qty' => $production->produced_qty,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->teamLedger->recordProduction([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'team_id' => $production->team_id,
                'reference_type' => 'Production',
                'reference_id' => $id,
                'pallet_model_id' => $production->pallet_model_id,
                'transaction_date' => $production->production_date,
                'qty' => $production->produced_qty,
                'user_id' => $request->user()?->id,
            ]);

            $this->recordProductionWastage($request, $production, $consumptions);

            DB::table('production_master')->where('production_id', $id)->update([
                'status' => self::STATUS_POSTED,
                'posted_by' => $request->user()?->id,
                'posted_at' => now(),
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);

            $this->audit->record($request, 'production_master', 'post', $id, $production, ['status' => self::STATUS_POSTED]);

            return $this->find($request, $id);
        });
    }

    public function cancel(Request $request, int $id, ?string $reason = null): object
    {
        return DB::transaction(function () use ($request, $id, $reason): object {
            $production = $this->scopedForUpdate($request, $id);
            if ($production->status !== self::STATUS_POSTED) {
                throw ValidationException::withMessages(['status' => 'Only posted production can be cancelled.']);
            }

            $usedWastage = DB::table('wastage_stock')
                ->where('source_module', 'Production')
                ->whereIn('source_id', DB::table('production_wastage')->where('production_id', $id)->pluck('wastage_id'))
                ->where('used_qty', '>', 0)
                ->exists();

            if ($usedWastage) {
                throw ValidationException::withMessages(['status' => 'Production wastage has already been reused and cannot be cancelled.']);
            }

            foreach (DB::table('production_consumption')->where('production_id', $id)->lockForUpdate()->get() as $line) {
                $this->transactions->record([
                    'tenant_id' => $production->tenant_id,
                    'branch_id' => $production->branch_id,
                    'item_id' => $line->item_id,
                    'location_id' => $line->location_id,
                    'stock_type' => 'Fresh',
                    'transaction_date' => now(),
                    'transaction_type' => 'Production Consumption Reversal',
                    'reference_id' => $id,
                    'reference_type' => self::REFERENCE_TYPE,
                    'qty_in' => $line->consumed_qty,
                    'qty_out' => 0,
                    'user_id' => $request->user()?->id,
                ]);

                $wastageQty = (float) ($line->wastage_qty ?? 0);
                if ($wastageQty > 0) {
                    $this->transactions->record([
                        'tenant_id' => $production->tenant_id,
                        'branch_id' => $production->branch_id,
                        'item_id' => $line->item_id,
                        'location_id' => $line->location_id,
                        'stock_type' => 'Fresh',
                        'transaction_date' => now(),
                        'transaction_type' => 'Production Wastage Reversal',
                        'reference_id' => $id,
                        'reference_type' => self::REFERENCE_TYPE,
                        'qty_in' => $wastageQty,
                        'qty_out' => 0,
                        'user_id' => $request->user()?->id,
                    ]);
                }
            }

            $this->transactions->record([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'item_id' => $production->produced_item_id,
                'location_id' => $production->fg_location_id,
                'stock_type' => 'Fresh',
                'transaction_date' => now(),
                'transaction_type' => 'Production Output Reversal',
                'reference_id' => $id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => 0,
                'qty_out' => $production->produced_qty,
                'user_id' => $request->user()?->id,
            ]);

            foreach (DB::table('production_wastage')->where('production_id', $id)->lockForUpdate()->get() as $wastage) {
                $this->transactions->record([
                    'tenant_id' => $production->tenant_id,
                    'branch_id' => $production->branch_id,
                    'item_id' => $wastage->item_id,
                    'location_id' => $wastage->location_id ?: $production->fg_location_id,
                    'stock_type' => 'Fresh',
                    'transaction_date' => now(),
                    'transaction_type' => 'Production Wastage Reversal',
                    'reference_id' => $id,
                    'reference_type' => self::REFERENCE_TYPE,
                    'qty_in' => 0,
                    'qty_out' => $wastage->qty,
                    'user_id' => $request->user()?->id,
                ]);
            }

            DB::table('wastage_stock')
                ->where('source_module', 'Production')
                ->whereIn('source_id', DB::table('production_wastage')->where('production_id', $id)->pluck('wastage_id'))
                ->update([
                    'available_qty' => 0,
                    'balance_qty' => 0,
                    'status' => self::STATUS_CANCELLED,
                    'cancelled_by' => $request->user()?->id,
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                    'updated_by' => $request->user()?->id,
                    'updated_at' => now(),
                ]);

            $this->teamLedger->recordProduction([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'team_id' => $production->team_id,
                 'reference_id' => $id,
                    'reference_type' => 'Production',
                'pallet_model_id' => $production->pallet_model_id,
                'transaction_date' => now()->toDateString(),
                'qty' => -1 * (float) $production->produced_qty,
                'user_id' => $request->user()?->id,
            ]);

            DB::table('production_master')->where('production_id', $id)->update([
                'status' => self::STATUS_CANCELLED,
                'cancelled_by' => $request->user()?->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);

            $this->audit->record($request, 'production_master', 'cancel', $id, $production, ['status' => self::STATUS_CANCELLED, 'reason' => $reason]);

            return $this->find($request, $id);
        });
    }

    public function export(Request $request)
    {
        return $this->paginate($request)->getCollection();
    }

    private function baseQuery(Request $request)
    {
        $user = $request->user();

        return DB::table('production_master')
            ->where('production_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('production_master.branch_id', $user->branch_id));
    }

    private function scopedForUpdate(Request $request, int $id): object
    {
        return $this->baseQuery($request)->where('production_id', $id)->lockForUpdate()->firstOrFail();
    }

    private function payload(Request $request, array $data, bool $creating): array
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $data['branch_id']);
        $bom = DB::table('bom_master')
            ->where('tenant_id', $user->tenant_id)
            ->where('bom_id', $data['bom_id'])
            ->first();

        $master = [
            'tenant_id' => $user->tenant_id,
            'branch_id' => $branchId,
            'production_no' => $data['production_no'] ?? $this->nextNumber((int) $user->tenant_id, $branchId),
            'production_date' => $data['production_date'],
            'bom_id' => (int) $data['bom_id'],
            'pallet_model_id' => $data['pallet_model_id'] ?? $bom?->pallet_model_id,
            'produced_item_id' => (int) ($data['produced_item_id'] ?: $bom?->finished_item_id),
            'team_id' => (int) $data['team_id'],
            'fg_location_id' => (int) $data['fg_location_id'],
            'produced_qty' => (float) $data['produced_qty'],
            'production_cost' => (float) ($data['production_cost'] ?? 0),
            'status' => self::STATUS_DRAFT,
            'remarks' => $data['remarks'] ?? null,
            'updated_by' => $user->id,
            'updated_at' => now(),
        ];

        if ($creating) {
            $master['created_by'] = $user->id;
            $master['created_at'] = now();
        }

        $consumptions = collect($data['consumptions'])->map(fn (array $line): array => [
            'tenant_id' => $user->tenant_id,
            'branch_id' => $branchId,
            'item_id' => (int) $line['item_id'],
            'uom_id' => $line['uom_id'] ?? null,
            'location_id' => (int) $line['location_id'],
            'required_qty' => (float) ($line['required_qty'] ?? $line['consumed_qty']),
            'consumed_qty' => (float) $line['consumed_qty'],
            'wastage_qty' => (float) ($line['wastage_qty'] ?? 0),
            'remarks' => $line['remarks'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        $wastages = [];

        return ['master' => $master, 'consumptions' => $consumptions, 'wastages' => $wastages];
    }

    private function replaceRows(int $productionId, array $payload): void
    {
        DB::table('production_consumption')->where('production_id', $productionId)->delete();
        DB::table('production_output')->where('production_id', $productionId)->delete();
        DB::table('production_wastage')->where('production_id', $productionId)->delete();

        foreach ($payload['consumptions'] as $line) {
            $line['production_id'] = $productionId;
            DB::table('production_consumption')->insert($line);
        }

        foreach ($payload['wastages'] as $line) {
            $line['production_id'] = $productionId;
            DB::table('production_wastage')->insert($line);
        }
    }

    private function consumptions(int $productionId)
    {
        return DB::table('production_consumption')
            ->leftJoin('item_master', 'production_consumption.item_id', '=', 'item_master.item_id')
            ->leftJoin('uom_master', 'production_consumption.uom_id', '=', 'uom_master.uom_id')
            ->leftJoin('storage_location_master', 'production_consumption.location_id', '=', 'storage_location_master.location_id')
            ->leftJoin('stock_summary', function ($join): void {
                $join->on('production_consumption.tenant_id', '=', 'stock_summary.tenant_id')
                    ->on('production_consumption.branch_id', '=', 'stock_summary.branch_id')
                    ->on('production_consumption.item_id', '=', 'stock_summary.item_id')
                    ->on('production_consumption.location_id', '=', 'stock_summary.location_id')
                    ->where('stock_summary.stock_type', '=', 'Fresh');
            })
            ->where('production_consumption.production_id', $productionId)
            ->select('production_consumption.*', 'item_master.item_name', 'uom_master.uom_name', 'storage_location_master.location_name', DB::raw('COALESCE(stock_summary.stock_qty, 0) as current_stock'))
            ->get();
    }

    private function wastages(int $productionId)
    {
        return DB::table('production_wastage')
            ->leftJoin('item_master', 'production_wastage.item_id', '=', 'item_master.item_id')
            ->leftJoin('storage_location_master', 'production_wastage.location_id', '=', 'storage_location_master.location_id')
            ->where('production_wastage.production_id', $productionId)
            ->select('production_wastage.*', 'item_master.item_name', 'storage_location_master.location_name')
            ->get();
    }

    private function recordProductionWastage(Request $request, object $production, Collection $consumptions): void
    {
        $wastageLocationId = $this->resolveWastageLocationId($production);
        if ($wastageLocationId <= 0) {
            throw ValidationException::withMessages(['wastage_location_id' => 'A wastage location is required to post production wastage.']);
        }

        foreach ($consumptions as $line) {
            $wastageQty = (float) ($line->wastage_qty ?? 0);
            if ($wastageQty <= 0) {
                continue;
            }

            $wastageId = DB::table('production_wastage')->insertGetId([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'production_id' => $production->production_id,
                'item_id' => $line->item_id,
                'location_id' => $wastageLocationId,
                'qty' => $wastageQty,
                'wastage_type' => 'Scrap',
                'remarks' => $line->remarks ?? null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'wastage_id');

            $this->transactions->record([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'item_id' => $line->item_id,
                'location_id' => $wastageLocationId,
                'stock_type' => 'Fresh',
                'transaction_date' => $production->production_date.' 00:00:00',
                'transaction_type' => 'Production Wastage',
                'reference_id' => $production->production_id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => $wastageQty,
                'qty_out' => 0,
                'user_id' => $request->user()?->id,
            ]);

            DB::table('wastage_stock')->insert([
                'tenant_id' => $production->tenant_id,
                'branch_id' => $production->branch_id,
                'item_id' => $line->item_id,
                'location_id' => $wastageLocationId,
                'wastage_type' => 'Scrap',
                'source_module' => 'Production',
                'source_id' => $wastageId,
                'source_reference' => $production->production_no,
                'transaction_date' => $production->production_date,
                'generated_qty' => $wastageQty,
                'available_qty' => $wastageQty,
                'used_qty' => 0,
                'balance_qty' => $wastageQty,
                'status' => self::STATUS_POSTED,
                'posted_at' => now(),
                'posted_by' => $request->user()?->id,
                'remarks' => $line->remarks ?? null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function resolveWastageLocationId(object $production): int
    {
        $locationId = (int) (DB::table('storage_location_master')
            ->where('tenant_id', $production->tenant_id)
            ->when($production->branch_id, fn ($query) => $query->where('branch_id', $production->branch_id))
            ->whereIn('location_type', ['WASTAGE', 'SCRAP'])
            ->orderByRaw("CASE WHEN location_type = 'WASTAGE' THEN 0 ELSE 1 END")
            ->orderBy('location_id')
            ->value('location_id') ?? 0);

        return $locationId;
    }

    private function nextNumber(int $tenantId, int $branchId): string
    {
        $next = DB::table('production_master')->where('tenant_id', $tenantId)->where('branch_id', $branchId)->lockForUpdate()->count() + 1;

        return 'PROD-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function ensureDraft(object $production): void
    {
        if ($production->status !== self::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Only draft production entries can be changed.']);
        }
    }

    private function ensurePostingStateIsClean(int $productionId): void
    {
        $hasLedgerMovement = DB::table('stock_ledger')
            ->where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $productionId)
            ->exists();

        if ($hasLedgerMovement) {
            throw ValidationException::withMessages(['status' => 'Production already has stock movement entries. Refresh and check current status.']);
        }
    }

    private function ensureBomCanBePosted(Request $request, int $bomId): void
    {
        $bom = DB::table('bom_master')
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->user()->branch_id, fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->where('bom_id', $bomId)
            ->first();

        if (! $bom || ! (bool) $bom->is_active || $bom->status !== 'Active') {
            throw ValidationException::withMessages(['bom_id' => 'Selected BOM is inactive or unavailable.']);
        }

        $inactiveComponent = DB::table('bom_material')
            ->join('item_master', 'bom_material.item_id', '=', 'item_master.item_id')
            ->where('bom_material.bom_id', $bomId)
            ->where('item_master.status', '!=', 'Active')
            ->exists();

        if ($inactiveComponent) {
            throw ValidationException::withMessages(['bom_id' => 'Selected BOM contains inactive material items.']);
        }
    }
}
