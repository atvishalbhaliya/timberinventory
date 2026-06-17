<?php

namespace App\Services\Dispatch;

use App\Services\Inventory\InventoryTransactionService;
use App\Services\Inventory\StockSummaryService;
use App\Services\Finance\TeamLedgerService;
use App\Services\Finance\TeamPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class DispatchService
{
    private const REFERENCE_TYPE = 'Dispatch Challan';

    public function __construct(
        private readonly InventoryTransactionService $transactions,
        private readonly StockSummaryService $summary,
        private readonly TeamLedgerService $teamLedger,
        private readonly TeamPaymentService $teamPayment,
    ) {
    }

    public function paginate(Request $request)
    {
        $user = $request->user();

        $query = DB::table('challan_master')
            ->leftJoin('party_master', 'challan_master.customer_id', '=', 'party_master.party_id')
            ->leftJoin('storage_location_master', 'challan_master.source_location_id', '=', 'storage_location_master.location_id')
            ->leftJoin('users as creator', 'challan_master.created_by', '=', 'creator.id')
            ->leftJoin(DB::raw('(select challan_id, count(*) as line_count, sum(qty) as team_qty from challan_team_detail group by challan_id) as line_totals'), 'challan_master.challan_id', '=', 'line_totals.challan_id')
            ->where('challan_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('challan_master.branch_id', $user->branch_id))
            ->select([
                'challan_master.challan_id',
                'challan_master.challan_no',
                'challan_master.challan_date',
                'challan_master.source_location_id',
                'challan_master.vehicle_no',
                'challan_master.driver_name',
                'challan_master.destination',
                'challan_master.total_qty',
                'challan_master.created_at',
                'party_master.party_name as customer_name',
                'storage_location_master.location_name as source_location_name',
                'line_totals.line_count',
                'line_totals.team_qty',
                'creator.full_name as created_by_name',
            ]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('challan_master.challan_no', 'like', "%{$search}%")
                    ->orWhere('party_master.party_name', 'like', "%{$search}%")
                    ->orWhere('challan_master.vehicle_no', 'like', "%{$search}%")
                    ->orWhere('challan_master.driver_name', 'like', "%{$search}%")
                    ->orWhere('challan_master.destination', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('challan_master.customer_id', $request->query('customer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('challan_master.challan_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('challan_master.challan_date', '<=', $request->query('date_to'));
        }

        $sortMap = [
            'challan_no' => 'challan_master.challan_no',
            'challan_date' => 'challan_master.challan_date',
            'customer_name' => 'party_master.party_name',
            'source_location_name' => 'storage_location_master.location_name',
            'total_qty' => 'challan_master.total_qty',
            'created_by_name' => 'creator.full_name',
            'created_at' => 'challan_master.created_at',
        ];

        $sortBy = $sortMap[$request->query('sort_by', 'challan_date')] ?? 'challan_master.challan_date';
        $sortDirection = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        return $query
            ->groupBy([
                'challan_master.challan_id',
                'challan_master.challan_no',
                'challan_master.challan_date',
                'challan_master.source_location_id',
                'challan_master.vehicle_no',
                'challan_master.driver_name',
                'challan_master.destination',
                'challan_master.total_qty',
                'challan_master.created_at',
                'party_master.party_name',
                'storage_location_master.location_name',
                'creator.full_name',
                'line_totals.line_count',
                'line_totals.team_qty',
            ])
            ->orderBy($sortBy, $sortDirection)
            ->paginate((int) min(max((int) $request->query('per_page', 10), 1), 100));
    }

    public function nextNumber(Request $request): string
    {
        $user = $request->user();
        $prefix = 'CH'.now()->format('ym');
        $lastNo = DB::table('challan_master')
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('branch_id', $user->branch_id))
            ->where('challan_no', 'like', $prefix.'%')
            ->orderByDesc('challan_id')
            ->value('challan_no');

        $next = 1;
        if (is_string($lastNo) && preg_match('/^'.preg_quote($prefix, '/').'-(\d+)$/', $lastNo, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%04d', $prefix, $next);
    }

    public function find(Request $request, int $id): object
    {
        $user = $request->user();
        $header = DB::table('challan_master')
            ->leftJoin('party_master', 'challan_master.customer_id', '=', 'party_master.party_id')
            ->leftJoin('storage_location_master', 'challan_master.source_location_id', '=', 'storage_location_master.location_id')
            ->leftJoin('users as creator', 'challan_master.created_by', '=', 'creator.id')
            ->where('challan_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('challan_master.branch_id', $user->branch_id))
            ->where('challan_master.challan_id', $id)
            ->select([
                'challan_master.*',
                'party_master.party_name as customer_name',
                'storage_location_master.location_name as source_location_name',
                'creator.full_name as created_by_name',
            ])
            ->firstOrFail();

        $details = DB::table('challan_team_detail')
            ->leftJoin('team_master', 'challan_team_detail.team_id', '=', 'team_master.team_id')
            ->leftJoin('item_master', 'challan_team_detail.item_id', '=', 'item_master.item_id')
            ->leftJoin('storage_location_master', 'challan_team_detail.location_id', '=', 'storage_location_master.location_id')
            ->where('challan_team_detail.challan_id', $id)
            ->select([
                'challan_team_detail.*',
                'item_master.item_name',
                'team_master.team_name',
                'storage_location_master.location_name',
            ])
            ->orderBy('challan_team_detail.detail_id')
            ->get();

        return (object) ['header' => $header, 'details' => $details];
    }

    public function store(Request $request): int
    {
        $data = $request->validate([
            'challan_no' => ['nullable', 'string', 'max:50'],
            'challan_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'integer', 'exists:party_master,party_id'],
            'source_location_id' => ['required', 'integer', 'exists:storage_location_master,location_id'],
            'vehicle_no' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'destination' => ['nullable', 'string', 'max:255'],
            'team_details' => ['required', 'array', 'min:1'],
            'team_details.*.item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('item_type', 'Finish Product')],
            'team_details.*.team_id' => ['required', 'integer', 'exists:team_master,team_id'],
            'team_details.*.qty' => ['required', 'numeric', 'gt:0'],
            'team_details.*.labour_rate' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $user = $request->user();
        $details = collect($data['team_details']);
        $teamRates = $this->teamRates($details);
        $data['challan_no'] = ! empty($data['challan_no'] ?? null) ? $data['challan_no'] : $this->nextNumber($request);
        $data['total_qty'] = (float) $details->sum('qty');

        return DB::transaction(function () use ($user, $data, $request, $teamRates): int {
            $challanId = DB::table('challan_master')->insertGetId([
                'tenant_id' => $user->tenant_id,
                'branch_id' => $user->branch_id,
                'challan_no' => $data['challan_no'],
                'challan_date' => $data['challan_date'],
                'customer_id' => $data['customer_id'] ?? null,
                'source_location_id' => $data['source_location_id'] ?? null,
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'destination' => $data['destination'] ?? null,
                'total_qty' => $data['total_qty'],
                'created_by' => $user?->getKey(),
                'updated_by' => $user?->getKey(),
                'created_at' => now(),
                'updated_at' => now(),
            ], 'challan_id');

            $header = (object) [
                'challan_id' => $challanId,
                'tenant_id' => $user->tenant_id,
                'branch_id' => $user->branch_id,
                'challan_date' => $data['challan_date'],
                'source_location_id' => $data['source_location_id'],
            ];

            foreach ($data['team_details'] as $detail) {
                DB::table('challan_team_detail')->insert([
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                    'challan_id' => $challanId,
                    'item_id' => $detail['item_id'],
                    'location_id' => $data['source_location_id'] ?? null,
                    'team_id' => $detail['team_id'],
                    'qty' => $detail['qty'],
                    'labour_rate' => $detail['labour_rate'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $details = collect($data['team_details']);
            $this->recordStockOutMovements($request, $header, $details, $teamRates);
            $this->recordTeamLedgerMovements($request, $header, $details, $teamRates);
            $this->rebuildTeamPaymentSummary($request, $data['challan_date']);

            return $challanId;
        });
    }

    public function update(Request $request, int $id): int
    {
        $data = $request->validate([
            'challan_no' => ['nullable', 'string', 'max:50'],
            'challan_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'integer', 'exists:party_master,party_id'],
            'source_location_id' => ['required', 'integer', 'exists:storage_location_master,location_id'],
            'vehicle_no' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'destination' => ['nullable', 'string', 'max:255'],
            'team_details' => ['required', 'array', 'min:1'],
            'team_details.*.item_id' => ['required', 'integer', Rule::exists('item_master', 'item_id')->where('item_type', 'Finish Product')],
            'team_details.*.team_id' => ['required', 'integer', 'exists:team_master,team_id'],
            'team_details.*.qty' => ['required', 'numeric', 'gt:0'],
            'team_details.*.labour_rate' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $context = $this->scopedMasterForUpdate($request, $id);
        $user = $request->user();
        $details = collect($data['team_details']);
        $teamRates = $this->teamRates($details);
        $data['challan_no'] = ! empty($data['challan_no'] ?? null) ? $data['challan_no'] : $context->challan_no;
        $data['total_qty'] = (float) $details->sum('qty');

        DB::transaction(function () use ($context, $data, $user, $request, $details, $teamRates): void {
            $existingDetails = DB::table('challan_team_detail')
                ->where('challan_id', $context->challan_id)
                ->orderBy('detail_id')
                ->lockForUpdate()
                ->get();

            $oldChallanDate = $context->challan_date;

            $this->reverseStockMovements($context, $existingDetails, $request);
            $this->deleteStockMovements($context->challan_id);

            DB::table('challan_master')->where('challan_id', $context->challan_id)->update([
                'challan_no' => $data['challan_no'],
                'challan_date' => $data['challan_date'],
                'customer_id' => $data['customer_id'] ?? null,
                'source_location_id' => $data['source_location_id'] ?? null,
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'destination' => $data['destination'] ?? null,
                'total_qty' => $data['total_qty'],
                'updated_by' => $user?->getKey(),
                'updated_at' => now(),
            ]);

            DB::table('challan_team_detail')->where('challan_id', $context->challan_id)->delete();
            $this->teamLedger->deleteByReference(self::REFERENCE_TYPE, $context->challan_id);

            $header = (object) [
                'challan_id' => $context->challan_id,
                'tenant_id' => $context->tenant_id,
                'branch_id' => $context->branch_id,
                'challan_date' => $data['challan_date'],
                'source_location_id' => $data['source_location_id'],
            ];

            foreach ($data['team_details'] as $detail) {
                DB::table('challan_team_detail')->insert([
                    'tenant_id' => $context->tenant_id,
                    'branch_id' => $context->branch_id,
                    'challan_id' => $context->challan_id,
                    'item_id' => $detail['item_id'],
                    'location_id' => $data['source_location_id'] ?? $context->source_location_id ?? null,
                    'team_id' => $detail['team_id'],
                    'qty' => $detail['qty'],
                    'labour_rate' => $detail['labour_rate'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->recordStockOutMovements($request, $header, $details, $teamRates);
            $this->recordTeamLedgerMovements($request, $header, $details, $teamRates);
            $this->rebuildTeamPaymentSummary($request, $oldChallanDate, $data['challan_date']);
        });

        return $context->challan_id;
    }

    public function delete(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id): void {
            $context = $this->scopedMasterForUpdate($request, $id);
            $details = DB::table('challan_team_detail')
                ->where('challan_id', $context->challan_id)
                ->orderBy('detail_id')
                ->lockForUpdate()
                ->get();

            $this->reverseStockMovements($context, $details, $request);
            $this->deleteStockMovements($context->challan_id);
            $this->teamLedger->deleteByReference(self::REFERENCE_TYPE, $context->challan_id);
            $this->rebuildTeamPaymentSummary($request, $context->challan_date);

            DB::table('challan_team_detail')->where('challan_id', $context->challan_id)->delete();
            DB::table('challan_master')->where('challan_id', $context->challan_id)->delete();
        });
    }

    public function export(Request $request): Collection
    {
        return $this->paginate($request)->getCollection();
    }

    public function lineMetrics(Request $request): array
    {
        $data = $request->validate([
            'item_id' => ['required', 'integer', 'exists:item_master,item_id'],
            'team_id' => ['required', 'integer', 'exists:team_master,team_id'],
            'location_id' => ['nullable', 'integer', 'exists:storage_location_master,location_id'],
        ]);

        $user = $request->user();

        $query = DB::table('stock_ledger')
            ->where('tenant_id', $user->tenant_id)
           
            ->where('item_id', $data['item_id'])
            ->where('team_id', $data['team_id']);

        if (! empty($data['location_id'])) {
            $query->where('location_id', $data['location_id']);
        }

        $summary = $query->selectRaw('COALESCE(SUM(qty_in), 0) as total_in')
            ->selectRaw('COALESCE(SUM(qty_out), 0) as total_out')
            ->selectRaw('COALESCE(SUM(CASE WHEN qty_in > 0 THEN qty_in ELSE 0 END), 0) as incoming_qty')
            ->selectRaw('COALESCE(SUM(CASE WHEN qty_in > 0 THEN labour_charge ELSE 0 END), 0) as labour_value')
            ->first();

        $currentQty = (float) ($summary->total_in ?? 0) - (float) ($summary->total_out ?? 0);
        $incomingQty = (float) ($summary->incoming_qty ?? 0);
        $labourValue = (float) ($summary->labour_value ?? 0);

        $labourRate = $incomingQty > 0
            ? $labourValue / $incomingQty
            : 0;

        return [
            'current_qty' => round($currentQty, 3),
            'production_qty' => round($currentQty, 3),
            'labour_rate' => round($labourRate, 2),
            'stock_value' => round($currentQty * $labourRate, 2),
        ];
    }

    private function scopedMasterForUpdate(Request $request, int $id): object
    {
        $user = $request->user();

        return DB::table('challan_master')
            ->leftJoin('party_master', 'challan_master.customer_id', '=', 'party_master.party_id')
            ->leftJoin('storage_location_master', 'challan_master.source_location_id', '=', 'storage_location_master.location_id')
            ->leftJoin('users as creator', 'challan_master.created_by', '=', 'creator.id')
            ->where('challan_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($builder) => $builder->where('challan_master.branch_id', $user->branch_id))
            ->where('challan_master.challan_id', $id)
            ->select([
                'challan_master.*',
                'party_master.party_name as customer_name',
                'storage_location_master.location_name as source_location_name',
                'creator.full_name as created_by_name',
            ])
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function recordStockOutMovements(Request $request, object $header, Collection $details, Collection $teamRates): void
    {
        $movements = $details->map(function ($detail) use ($header, $request, $teamRates): array {
            $locationId = (int) ($detail['location_id'] ?? $header->source_location_id ?? 0);
            $labourRate = $this->resolveLabourRate($detail, $teamRates);

            if ($locationId <= 0) {
                throw ValidationException::withMessages(['source_location_id' => 'Location is required for dispatch stock deduction.']);
            }

            return [
                'item_id' => (int) $detail['item_id'],
                'location_id' => $locationId,
                'qty' => (float) $detail['qty'],
                'team_id' => (int) $detail['team_id'],
                'labour_rate' => $labourRate,
                'labour_charge' => (float) $detail['qty'] * $labourRate,
            ];
        });

        $this->assertStockAvailable($header, $movements);

        foreach ($movements as $movement) {
            $this->transactions->record([
                'tenant_id' => $header->tenant_id,
                'branch_id' => $header->branch_id,
                'item_id' => $movement['item_id'],
                 'pallet_model_id' => $movement['item_id'],
                'location_id' => $movement['location_id'],
                'team_id' => $movement['team_id'],
                'transaction_date' => $header->challan_date.' 00:00:00',
                'transaction_type' => 'Dispatch',
                'reference_id' => $header->challan_id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => 0,
                'qty_out' => $movement['qty'],
                'labour_charge' => $movement['labour_charge'],
                'user_id' => $request->user()?->id,
            ]);
        }
    }

    private function recordTeamLedgerMovements(Request $request, object $header, Collection $details, Collection $teamRates): void
    {
        foreach ($details as $detail) {
            $teamId = (int) $detail['team_id'];
            $qty = (float) $detail['qty'];
            $rate = $this->resolveLabourRate($detail, $teamRates);
            $labourCharge = $qty * $rate;

            $this->teamLedger->recordDispatch([
                'tenant_id' => $header->tenant_id,
                'branch_id' => $header->branch_id,
                'team_id' => $teamId,
                'pallet_model_id' => $detail['item_id'],
                'transaction_date' => $header->challan_date,
                'qty' => $qty,
                'amount' => $labourCharge,
                'labour_rate' => $rate,
                'reference_type' => self::REFERENCE_TYPE,
                'reference_id' => $header->challan_id,
                'user_id' => $request->user()?->id,
            ]);
        }
    }

    private function reverseStockMovements(object $header, Collection $details, Request $request): void
    {
        foreach ($details as $detail) {
            $locationId = (int) ($detail->location_id ?? $header->source_location_id ?? 0);

            if ($locationId <= 0) {
                throw ValidationException::withMessages(['source_location_id' => 'Location is required for dispatch stock reversal.']);
            }

            $this->transactions->record([
                'tenant_id' => $header->tenant_id,
                'branch_id' => $header->branch_id,
                'item_id' => (int) $detail->item_id,
                'location_id' => $locationId,
                'transaction_date' => now(),
                'transaction_type' => 'Dispatch Reversal',
                'reference_id' => $header->challan_id,
                'reference_type' => self::REFERENCE_TYPE,
                'qty_in' => (float) $detail->qty,
                'qty_out' => 0,
                'rate' => $this->currentAvgRate($header, (int) $detail->item_id, $locationId),
                'user_id' => $request->user()?->id,
            ]);
        }
    }

    private function assertStockAvailable(object $header, Collection $movements): void
    {
        $movements->groupBy(fn (array $movement): string => $movement['item_id'].'|'.$movement['location_id'])
            ->each(function (Collection $group) use ($header): void {
                $first = $group->first();
                $available = $this->summary->currentQty(
                    (int) $header->tenant_id,
                    (int) $header->branch_id,
                    (int) $first['item_id'],
                    (int) $first['location_id'],
                );
                $required = (float) $group->sum('qty');

                if ($available < $required) {
                    throw ValidationException::withMessages([
                        'team_details' => 'Insufficient stock for the selected finish product at the chosen location.',
                    ]);
                }
            });
    }

    private function currentAvgRate(object $header, int $itemId, int $locationId): float
    {
        return (float) (DB::table('stock_summary')
            ->where('tenant_id', $header->tenant_id)
            ->where('branch_id', $header->branch_id)
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->value('avg_rate') ?? 0);
    }

    private function deleteStockMovements(int $challanId): int
    {
        return DB::table('stock_ledger')
            ->where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $challanId)
            ->delete();
    }

    private function rebuildTeamPaymentSummary(Request $request, string ...$dates): void
    {
        $months = [];

        foreach ($dates as $date) {
            if ($date === '') {
                continue;
            }

            $timestamp = strtotime($date);
            if ($timestamp === false) {
                continue;
            }

            $months[sprintf('%04d-%02d', (int) date('Y', $timestamp), (int) date('n', $timestamp))] = [
                (int) date('n', $timestamp),
                (int) date('Y', $timestamp),
            ];
        }

        foreach ($months as [$month, $year]) {
            $this->teamPayment->rebuild($request, $month, $year);
        }
    }

    private function teamRates(Collection $details): Collection
    {
        return DB::table('team_master')
            ->whereIn('team_id', $details->pluck('team_id')->filter()->unique())
            ->pluck('rate_per_pallet', 'team_id');
    }

    private function resolveLabourRate($detail, Collection $teamRates): float
    {
        $teamId = is_array($detail) ? (int) ($detail['team_id'] ?? 0) : (int) ($detail->team_id ?? 0);
        $detailRate = is_array($detail) ? (float) ($detail['labour_rate'] ?? 0) : (float) ($detail->labour_rate ?? 0);

        if ($detailRate > 0) {
            return $detailRate;
        }

        return (float) $teamRates->get($teamId, 0);
    }
}
