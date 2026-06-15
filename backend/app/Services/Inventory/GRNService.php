<?php

namespace App\Services\Inventory;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class GRNService
{
    private const STATUS_DRAFT = 'Draft';
    private const STATUS_POSTED = 'Posted';
    private const STATUS_CANCELLED = 'Cancelled';
    private const REFERENCE_TYPE = 'GRN';

    public function __construct(
        private readonly InventoryTransactionService $transactions,
        private readonly StockLedgerService $ledger,
        private readonly AuditLogService $audit,
    ) {
    }

    public function paginate(Request $request)
    {
        $query = $this->baseQuery($request)
            ->leftJoin('party_master', 'grn_master.supplier_id', '=', 'party_master.party_id')
            ->leftJoin('branch_master', 'grn_master.branch_id', '=', 'branch_master.branch_id')
            ->leftJoin('users as creator', 'grn_master.created_by', '=', 'creator.id')
            ->leftJoinSub(
                DB::table('grn_detail')
                    ->select('grn_id', DB::raw('COUNT(*) as item_count'))
                    ->groupBy('grn_id'),
                'grn_item_counts',
                'grn_master.grn_id',
                '=',
                'grn_item_counts.grn_id'
            )
            ->leftJoinSub(
                DB::table('grn_detail')
                    ->leftJoin('storage_location_master', 'grn_detail.location_id', '=', 'storage_location_master.location_id')
                    ->select('grn_detail.grn_id', DB::raw('GROUP_CONCAT(DISTINCT storage_location_master.location_name ORDER BY storage_location_master.location_name SEPARATOR ", ") as location_names'))
                    ->groupBy('grn_detail.grn_id'),
                'grn_locations',
                'grn_master.grn_id',
                '=',
                'grn_locations.grn_id'
            )
            ->select(
                'grn_master.*',
                'party_master.party_name',
                'party_master.party_name as supplier_name',
                'branch_master.branch_name',
                'grn_locations.location_names',
                'creator.full_name as created_by_name',
                DB::raw('COALESCE(grn_item_counts.item_count, 0) as item_count')
            );

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('grn_master.grn_no', 'like', "%{$search}%")
                    ->orWhere('grn_master.purchase_order_ref', 'like', "%{$search}%")
                    ->orWhere('party_master.party_name', 'like', "%{$search}%")
                    ->orWhere('grn_master.status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('grn_master.status', $request->query('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('grn_master.branch_id', $request->query('branch_id'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('grn_master.supplier_id', $request->query('supplier_id'));
        }

        if ($request->filled('grn_no')) {
            $query->where('grn_master.grn_no', 'like', '%'.trim((string) $request->query('grn_no')).'%');
        }

        if ($request->filled('grn_date')) {
            $query->whereDate('grn_master.grn_date', $request->query('grn_date'));
        }

        if ($request->filled('location_id')) {
            $query->whereExists(function ($exists) use ($request): void {
                $exists->selectRaw('1')
                    ->from('grn_detail')
                    ->whereColumn('grn_detail.grn_id', 'grn_master.grn_id')
                    ->where('grn_detail.location_id', $request->query('location_id'));
            });
        }

        if ($request->filled('amount_min')) {
            $query->where('grn_master.total_amount', '>=', (float) $request->query('amount_min'));
        }

        if ($request->filled('amount_max')) {
            $query->where('grn_master.total_amount', '<=', (float) $request->query('amount_max'));
        }

        if ($request->filled('created_by')) {
            $query->where('creator.full_name', 'like', '%'.trim((string) $request->query('created_by')).'%');
        }

        return $query
            ->tap(fn ($query) => $this->applySorting($query, $request))
            ->paginate((int) min(max((int) $request->query('per_page', 25), 1), 200));
    }

    public function previewNextNumber(Request $request): string
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $request->query('branch_id'));

        if ($branchId <= 0) {
            $branchId = (int) DB::table('branch_master')
                ->where('tenant_id', $user->tenant_id)
                ->orderBy('branch_id')
                ->value('branch_id');
        }

        return $this->nextGrnNumber((int) $user->tenant_id, $branchId);
    }

    private function applySorting($query, Request $request): void
    {
        $columns = [
            'grn_no' => 'grn_master.grn_no',
            'grn_date' => 'grn_master.grn_date',
            'party_name' => 'party_master.party_name',
            'supplier_name' => 'party_master.party_name',
            'branch_name' => 'branch_master.branch_name',
            'location' => 'grn_locations.location_names',
            'item_count' => 'item_count',
            'total_qty' => 'grn_master.total_qty',
            'status' => 'grn_master.status',
            'total_amount' => 'grn_master.total_amount',
            'created_by' => 'creator.full_name',
            'created_at' => 'grn_master.created_at',
        ];
        $sortBy = $columns[$request->query('sort_by')] ?? 'grn_master.grn_id';
        $direction = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $direction);
    }

    public function find(Request $request, int $id): object
    {
        $grn = $this->baseQuery($request)
            ->leftJoin('party_master', 'grn_master.supplier_id', '=', 'party_master.party_id')
            ->leftJoin('branch_master', 'grn_master.branch_id', '=', 'branch_master.branch_id')
            ->select('grn_master.*', 'party_master.party_name', 'party_master.party_name as supplier_name', 'branch_master.branch_name')
            ->where('grn_master.grn_id', $id)
            ->firstOrFail();

        $grn->details = DB::table('grn_detail')
            ->leftJoin('item_master', 'grn_detail.item_id', '=', 'item_master.item_id')
            ->leftJoin('uom_master', 'grn_detail.uom_id', '=', 'uom_master.uom_id')
            ->leftJoin('storage_location_master', 'grn_detail.location_id', '=', 'storage_location_master.location_id')
            ->where('grn_detail.grn_id', $id)
            ->orderBy('grn_detail.grn_detail_id')
            ->select('grn_detail.*', 'item_master.item_name', 'uom_master.uom_name', 'storage_location_master.location_name')
            ->get();

        return $grn;
    }

    public function create(Request $request, array $data): object
    {
        return DB::transaction(function () use ($request, $data): object {
            $payload = $this->normalizePayload($request, $data, true);
            $grnId = DB::table('grn_master')->insertGetId($payload['master'], 'grn_id');

            $this->replaceDetails($request, $grnId, $payload['details']);
            $this->audit->record($request, 'grn_master', 'create', $grnId, null, $payload);

            return $this->find($request, $grnId);
        });
    }

    public function update(Request $request, int $id, array $data): object
    {
        return DB::transaction(function () use ($request, $id, $data): object {
            $existing = $this->scopedMasterForUpdate($request, $id);
            $this->ensureDraft($existing);
            $data['grn_no'] = $existing->grn_no;

            $payload = $this->normalizePayload($request, $data, false);
            DB::table('grn_master')->where('grn_id', $id)->update($payload['master']);
            $this->replaceDetails($request, $id, $payload['details']);
            $this->audit->record($request, 'grn_master', 'update', $id, $existing, $payload);

            return $this->find($request, $id);
        });
    }

    public function delete(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id): void {
            $existing = $this->scopedMasterForUpdate($request, $id);
            $this->ensureDraft($existing);

            if (Schema::hasColumn('grn_detail', 'deleted_at')) {
                DB::table('grn_detail')->where('grn_id', $id)->update(['deleted_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('grn_detail')->where('grn_id', $id)->delete();
            }

            if (Schema::hasColumn('grn_master', 'deleted_at')) {
                DB::table('grn_master')->where('grn_id', $id)->update(['deleted_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('grn_master')->where('grn_id', $id)->delete();
            }

            $this->audit->record($request, 'grn_master', 'delete', $id, $existing);
        });
    }

    public function post(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $grn = $this->scopedMasterForUpdate($request, $id);

            if ($grn->status === self::STATUS_POSTED || $this->ledger->hasReference(self::REFERENCE_TYPE, $id)) {
                throw ValidationException::withMessages(['status' => 'GRN is already posted.']);
            }

            $this->ensureDraft($grn);
            $details = $this->detailsForPosting($id);

            foreach ($details as $detail) {
                $this->transactions->record([
                    'tenant_id' => $grn->tenant_id,
                    'branch_id' => $grn->branch_id,
                    'item_id' => $detail->item_id,
                    'location_id' => $detail->location_id,
                    'stock_type' => 'Fresh',
                    'transaction_date' => $grn->grn_date.' 00:00:00',
                    'transaction_type' => 'GRN',
                    'reference_id' => $id,
                    'reference_type' => self::REFERENCE_TYPE,
                    'qty_in' => $detail->qty,
                    'qty_out' => 0,
                    'rate' => $detail->rate,
                    'amount' => $detail->amount,
                    'user_id' => $request->user()?->id,
                ]);
            }

            DB::table('grn_master')->where('grn_id', $id)->update([
                'status' => self::STATUS_POSTED,
                'posted_at' => now(),
                'posted_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);

            $this->audit->record($request, 'grn_master', 'post', $id, $grn, ['status' => self::STATUS_POSTED]);

            return $this->find($request, $id);
        });
    }

    public function cancel(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $grn = $this->scopedMasterForUpdate($request, $id);

            if ($grn->status === self::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['status' => 'GRN is already cancelled.']);
            }

            if ($grn->status === self::STATUS_POSTED) {
                foreach ($this->detailsForPosting($id) as $detail) {
                    $this->transactions->record([
                        'tenant_id' => $grn->tenant_id,
                        'branch_id' => $grn->branch_id,
                        'item_id' => $detail->item_id,
                        'location_id' => $detail->location_id,
                        'stock_type' => 'Fresh',
                        'transaction_date' => now(),
                        'transaction_type' => 'GRN_CANCEL',
                        'reference_id' => $id,
                        'reference_type' => self::REFERENCE_TYPE,
                        'qty_in' => 0,
                        'qty_out' => $detail->qty,
                        'rate' => $detail->rate,
                        'amount' => $detail->amount,
                        'user_id' => $request->user()?->id,
                    ]);
                }
            }

            DB::table('grn_master')->where('grn_id', $id)->update([
                'status' => self::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);

            $this->audit->record($request, 'grn_master', 'cancel', $id, $grn, ['status' => self::STATUS_CANCELLED]);

            return $this->find($request, $id);
        });
    }

    private function baseQuery(Request $request)
    {
        $user = $request->user();

        return DB::table('grn_master')
            ->where('grn_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('grn_master.branch_id', $user->branch_id));
    }

    private function scopedMasterForUpdate(Request $request, int $id): object
    {
        return $this->baseQuery($request)
            ->where('grn_id', $id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function normalizePayload(Request $request, array $data, bool $creating): array
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $data['branch_id']);
        $warehouseLocationId = $data['warehouse_location_id'] ?? null;
        $details = collect($data['details'])
            ->map(function (array $detail) use ($user, $branchId, $warehouseLocationId): array {
                $receivedQty = (float) ($detail['received_qty'] ?? $detail['qty'] ?? $detail['accepted_qty'] ?? 0);
                $rejectedQty = (float) ($detail['rejected_qty'] ?? 0);
                $acceptedQty = (float) ($detail['accepted_qty'] ?? $detail['qty'] ?? max($receivedQty - $rejectedQty, 0));
                $discount = (float) ($detail['discount_amount'] ?? 0);
                $tax = (float) ($detail['tax_amount'] ?? 0);
                $lineAmount = round(($acceptedQty * (float) $detail['rate']) - $discount + $tax, 2);

                if ($acceptedQty <= 0) {
                    throw ValidationException::withMessages(['details' => 'Accepted quantity must be greater than zero for every line.']);
                }

                return [
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branchId,
                'item_id' => (int) $detail['item_id'],
                'uom_id' => (int) $detail['uom_id'],
                'location_id' => (int) ($detail['location_id'] ?? $warehouseLocationId),
                'ordered_qty' => (float) ($detail['ordered_qty'] ?? 0),
                'received_qty' => $receivedQty,
                'rejected_qty' => $rejectedQty,
                'accepted_qty' => $acceptedQty,
                'qty' => $acceptedQty,
                'rate' => (float) $detail['rate'],
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'amount' => $lineAmount,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
                ];
            })
            ->values();

        if ($details->contains(fn (array $detail): bool => $detail['location_id'] <= 0)) {
            throw ValidationException::withMessages(['details' => 'Line location is required.']);
        }

        $lineDiscount = round($details->sum('discount_amount'), 2);
        $lineTax = round($details->sum('tax_amount'), 2);
        $freight = (float) ($data['freight_charges'] ?? 0);
        $other = (float) ($data['other_charges'] ?? 0);
        $totalAmount = round($details->sum('amount'), 2);

        $master = [
            'tenant_id' => $user->tenant_id,
            'branch_id' => $branchId,
            'supplier_id' => $data['supplier_id'] ?? null,
            'grn_no' => $data['grn_no'] ?? $this->nextGrnNumber($user->tenant_id, $branchId),
            'grn_date' => $data['grn_date'],
            'invoice_no' => $data['invoice_no'] ?? null,
            'vehicle_no' => $data['vehicle_no'] ?? null,
            'purchase_order_ref' => $data['purchase_order_ref'] ?? null,
            'warehouse_location_id' => $warehouseLocationId,
            'received_by' => $data['received_by'] ?? null,
            'status' => self::STATUS_DRAFT,
            'remarks' => $data['remarks'] ?? null,
            'total_qty' => round($details->sum('qty'), 3),
            'total_amount' => $totalAmount,
            'discount_amount' => $lineDiscount,
            'tax_amount' => $lineTax,
            'freight_charges' => $freight,
            'other_charges' => $other,
            'grand_total' => round($totalAmount + $freight + $other, 2),
            'attachments' => isset($data['attachments']) ? json_encode($data['attachments']) : null,
            'updated_by' => $user->id,
            'updated_at' => now(),
        ];

        if ($creating) {
            $master['created_by'] = $user->id;
            $master['created_at'] = now();
        }

        return ['master' => $master, 'details' => $details->all()];
    }

    private function replaceDetails(Request $request, int $grnId, array $details): void
    {
        DB::table('grn_detail')->where('grn_id', $grnId)->delete();

        foreach ($details as $detail) {
            $detail['grn_id'] = $grnId;
            DB::table('grn_detail')->insert($detail);
        }
    }

    private function nextGrnNumber(int $tenantId, int $branchId): string
    {
        $next = DB::table('grn_master')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->count() + 1;

        return 'GRN-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function detailsForPosting(int $grnId)
    {
        $details = DB::table('grn_detail')->where('grn_id', $grnId)->lockForUpdate()->get();

        if ($details->isEmpty()) {
            throw ValidationException::withMessages(['details' => 'At least one GRN line is required before posting.']);
        }

        return $details;
    }

    private function ensureDraft(object $grn): void
    {
        if ($grn->status !== self::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Only draft GRNs can be changed.']);
        }
    }
}
