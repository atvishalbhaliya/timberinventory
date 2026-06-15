<?php

namespace App\Services\Inventory;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockVerificationService
{
    public function __construct(
        private readonly InventoryTransactionService $transactions,
        private readonly AuditLogService $audit,
    ) {
    }

    public function paginate(Request $request)
    {
        return $this->baseQuery($request)
            ->leftJoin('branch_master', 'stock_verification_master.branch_id', '=', 'branch_master.branch_id')
            ->leftJoin('storage_location_master', 'stock_verification_master.location_id', '=', 'storage_location_master.location_id')
            ->select('stock_verification_master.*', 'branch_master.branch_name', 'storage_location_master.location_name')
            ->when($request->filled('status'), fn ($query) => $query->where('stock_verification_master.status', $request->query('status')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->query('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('stock_verification_master.verification_no', 'like', "%{$search}%")
                        ->orWhere('stock_verification_master.remarks', 'like', "%{$search}%")
                        ->orWhere('stock_verification_master.status', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('stock_verification_master.verification_date', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('stock_verification_master.verification_date', '<=', $request->query('date_to')))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('stock_verification_master.branch_id', $request->query('branch_id')))
            ->when($request->filled('location_id'), fn ($query) => $query->where('stock_verification_master.location_id', $request->query('location_id')))
            ->tap(fn ($query) => $this->applySorting($query, $request))
            ->paginate((int) min(max((int) $request->query('per_page', 25), 1), 200));
    }

    public function find(Request $request, int $id): object
    {
        $verification = $this->baseQuery($request)
            ->leftJoin('branch_master', 'stock_verification_master.branch_id', '=', 'branch_master.branch_id')
            ->leftJoin('storage_location_master', 'stock_verification_master.location_id', '=', 'storage_location_master.location_id')
            ->select('stock_verification_master.*', 'branch_master.branch_name', 'storage_location_master.location_name')
            ->where('stock_verification_master.verification_id', $id)
            ->firstOrFail();

        $verification->details = DB::table('stock_verification_detail')
            ->leftJoin('item_master', 'stock_verification_detail.item_id', '=', 'item_master.item_id')
            ->leftJoin('uom_master', 'stock_verification_detail.uom_id', '=', 'uom_master.uom_id')
            ->where('stock_verification_detail.verification_id', $id)
            ->select('stock_verification_detail.*', 'item_master.item_name', 'item_master.item_code', 'uom_master.uom_name')
            ->orderBy('stock_verification_detail.detail_id')
            ->get();

        return $verification;
    }

    public function create(Request $request, array $data): object
    {
        return DB::transaction(function () use ($request, $data): object {
            $payload = $this->payload($request, $data, true);
            $id = DB::table('stock_verification_master')->insertGetId($payload['master'], 'verification_id');
            $this->replaceDetails($id, $payload['details']);
            $this->audit->record($request, 'stock_verification_master', 'create', $id, null, $payload);

            return $this->find($request, $id);
        });
    }

    public function update(Request $request, int $id, array $data): object
    {
        return DB::transaction(function () use ($request, $id, $data): object {
            $existing = $this->masterForUpdate($request, $id);
            $this->ensureMutable($existing);
            $payload = $this->payload($request, $data, false, $existing);
            DB::table('stock_verification_master')->where('verification_id', $id)->update($payload['master']);
            $this->replaceDetails($id, $payload['details']);
            $this->audit->record($request, 'stock_verification_master', 'update', $id, $existing, $payload);

            return $this->find($request, $id);
        });
    }

    public function delete(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id): void {
            $existing = $this->masterForUpdate($request, $id);
            $this->ensureMutable($existing);
            DB::table('stock_verification_detail')->where('verification_id', $id)->delete();
            DB::table('stock_verification_master')->where('verification_id', $id)->delete();
            $this->audit->record($request, 'stock_verification_master', 'delete', $id, $existing);
        });
    }

    public function submit(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $existing = $this->masterForUpdate($request, $id);
            if ($existing->status !== 'Draft') {
                throw ValidationException::withMessages(['status' => 'Only draft verifications can be submitted.']);
            }
            DB::table('stock_verification_master')->where('verification_id', $id)->update([
                'status' => 'Submitted',
                'submitted_at' => now(),
                'submitted_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'stock_verification_master', 'submit', $id, $existing, ['status' => 'Submitted']);

            return $this->find($request, $id);
        });
    }

    public function cancel(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $existing = $this->masterForUpdate($request, $id);
            if (! in_array($existing->status, ['Draft', 'Submitted'], true)) {
                throw ValidationException::withMessages(['status' => 'Only unapproved verifications can be cancelled.']);
            }
            DB::table('stock_verification_master')->where('verification_id', $id)->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'stock_verification_master', 'cancel', $id, $existing, ['status' => 'Cancelled']);

            return $this->find($request, $id);
        });
    }

    public function approve(Request $request, int $id): object
    {
        return DB::transaction(function () use ($request, $id): object {
            $master = $this->masterForUpdate($request, $id);
            if ($master->status === 'Completed' || $master->status === 'Approved') {
                throw ValidationException::withMessages(['status' => 'Verification is already approved.']);
            }
            if ($master->status !== 'Submitted') {
                throw ValidationException::withMessages(['status' => 'Submit verification before approval.']);
            }

            $adjustmentId = DB::table('stock_adjustment_master')->insertGetId([
                'tenant_id' => $master->tenant_id,
                'branch_id' => $master->branch_id,
                'adjustment_no' => $this->nextAdjustmentNumber((int) $master->tenant_id, (int) $master->branch_id),
                'adjustment_date' => now()->toDateString(),
                'verification_id' => $id,
                'reference_type' => 'STOCK_VERIFICATION',
                'reference_id' => $id,
                'remarks' => 'Auto adjustment from stock verification '.$master->verification_no,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'adjustment_id');

            $details = DB::table('stock_verification_detail')->where('verification_id', $id)->lockForUpdate()->get();
            foreach ($details as $detail) {
                $variance = (float) $detail->variance_qty;
                if ($variance == 0.0) {
                    continue;
                }
                $type = $variance > 0 ? 'Excess' : 'Shortage';
                DB::table('stock_adjustment_detail')->insert([
                    'tenant_id' => $master->tenant_id,
                    'branch_id' => $master->branch_id,
                    'adjustment_id' => $adjustmentId,
                    'item_id' => $detail->item_id,
                    'location_id' => $detail->location_id,
                    'adjustment_qty' => abs($variance),
                    'adjustment_type' => $type,
                    'created_by' => $request->user()?->id,
                    'updated_by' => $request->user()?->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->transactions->record([
                    'tenant_id' => $master->tenant_id,
                    'branch_id' => $master->branch_id,
                    'item_id' => $detail->item_id,
                    'location_id' => $detail->location_id,
                    'transaction_date' => now(),
                    'transaction_type' => 'ADJUSTMENT',
                    'reference_id' => $adjustmentId,
                    'reference_type' => 'ADJUSTMENT',
                    'qty_in' => $variance > 0 ? abs($variance) : 0,
                    'qty_out' => $variance < 0 ? abs($variance) : 0,
                    'rate' => 0,
                    'amount' => 0,
                    'user_id' => $request->user()?->id,
                ]);
            }

            DB::table('stock_verification_master')->where('verification_id', $id)->update([
                'status' => 'Completed',
                'approved_at' => now(),
                'approved_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
            $this->audit->record($request, 'stock_verification_master', 'approve', $id, $master, ['status' => 'Completed', 'adjustment_id' => $adjustmentId]);

            return $this->find($request, $id);
        });
    }

    public function currentStock(Request $request): array
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $request->query('branch_id'));
        $locationId = (int) $request->query('location_id');

        return DB::table('stock_summary')
            ->join('item_master', 'stock_summary.item_id', '=', 'item_master.item_id')
            ->leftJoin('uom_master', 'item_master.uom_id', '=', 'uom_master.uom_id')
            ->where('stock_summary.tenant_id', $user->tenant_id)
            ->where('stock_summary.branch_id', $branchId)
            ->where('stock_summary.location_id', $locationId)
            ->select('stock_summary.item_id', 'stock_summary.location_id', 'item_master.item_name', 'item_master.item_code', 'item_master.uom_id', 'uom_master.uom_name', 'stock_summary.stock_qty as system_qty')
            ->orderBy('item_master.item_name')
            ->get()
            ->toArray();
    }

    private function baseQuery(Request $request)
    {
        $user = $request->user();

        return DB::table('stock_verification_master')
            ->where('stock_verification_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('stock_verification_master.branch_id', $user->branch_id));
    }

    private function applySorting($query, Request $request): void
    {
        $columns = [
            'verification_no' => 'stock_verification_master.verification_no',
            'verification_date' => 'stock_verification_master.verification_date',
            'branch_name' => 'branch_master.branch_name',
            'location_name' => 'storage_location_master.location_name',
            'status' => 'stock_verification_master.status',
            'remarks' => 'stock_verification_master.remarks',
            'created_at' => 'stock_verification_master.created_at',
        ];
        $sortBy = $columns[$request->query('sort_by')] ?? 'stock_verification_master.verification_id';
        $direction = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $direction);
    }

    private function masterForUpdate(Request $request, int $id): object
    {
        return $this->baseQuery($request)->where('verification_id', $id)->lockForUpdate()->firstOrFail();
    }

    private function payload(Request $request, array $data, bool $creating, ?object $existing = null): array
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $data['branch_id']);
        $locationId = (int) $data['location_id'];
        $details = collect($data['details'])->map(function (array $detail) use ($user, $branchId, $locationId): array {
            $systemQty = (float) ($detail['system_qty'] ?? 0);
            $physicalQty = (float) ($detail['physical_qty'] ?? 0);
            $variance = round($physicalQty - $systemQty, 3);

            return [
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branchId,
                'verification_id' => 0,
                'item_id' => (int) $detail['item_id'],
                'location_id' => (int) ($detail['location_id'] ?? $locationId),
                'uom_id' => $detail['uom_id'] ?? null,
                'system_qty' => $systemQty,
                'physical_qty' => $physicalQty,
                'variance_qty' => $variance,
                'variance_type' => $variance > 0 ? 'Excess' : ($variance < 0 ? 'Shortage' : 'Matched'),
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->values()->all();

        return [
            'master' => [
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branchId,
                'location_id' => $locationId,
                'verification_no' => $existing?->verification_no ?? $this->nextVerificationNumber((int) $user->tenant_id, $branchId),
                'verification_date' => $data['verification_date'],
                'status' => $existing?->status ?? 'Draft',
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $creating ? $user->id : ($existing?->created_by ?? $user->id),
                'updated_by' => $user->id,
                'created_at' => $creating ? now() : ($existing?->created_at ?? now()),
                'updated_at' => now(),
            ],
            'details' => $details,
        ];
    }

    private function replaceDetails(int $verificationId, array $details): void
    {
        DB::table('stock_verification_detail')->where('verification_id', $verificationId)->delete();
        foreach ($details as $detail) {
            $detail['verification_id'] = $verificationId;
            DB::table('stock_verification_detail')->insert($detail);
        }
    }

    private function nextVerificationNumber(int $tenantId, int $branchId): string
    {
        $next = DB::table('stock_verification_master')->where('tenant_id', $tenantId)->where('branch_id', $branchId)->lockForUpdate()->count() + 1;

        return 'SV-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function nextAdjustmentNumber(int $tenantId, int $branchId): string
    {
        $next = DB::table('stock_adjustment_master')->where('tenant_id', $tenantId)->where('branch_id', $branchId)->lockForUpdate()->count() + 1;

        return 'ADJ-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function ensureMutable(object $verification): void
    {
        if (! in_array($verification->status, ['Draft'], true)) {
            throw ValidationException::withMessages(['status' => 'Only draft verifications can be changed.']);
        }
    }
}
