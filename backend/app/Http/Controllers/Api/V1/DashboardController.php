<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function summary(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $branchId = $request->user()->branch_id;

        $stocks = DB::table('stock_summary')
            ->join('item_master', 'stock_summary.item_id', '=', 'item_master.item_id')
            ->where('stock_summary.tenant_id', $tenantId)
            ->when($branchId, fn ($query) => $query->where('stock_summary.branch_id', $branchId))
            ->selectRaw("SUM(CASE WHEN item_master.item_type LIKE 'Raw Material%' THEN stock_summary.stock_qty ELSE 0 END) as raw_material_stock")
            ->selectRaw("SUM(CASE WHEN item_master.item_type LIKE 'Finish Product%' THEN stock_summary.stock_qty ELSE 0 END) as finished_goods_stock")
            ->selectRaw('SUM(stock_summary.stock_qty) as total_stock_qty')
            ->selectRaw('SUM(CASE WHEN stock_summary.stock_qty <= item_master.minimum_stock THEN 1 ELSE 0 END) as low_stock_items')
            ->first() ?? (object) [
                'raw_material_stock' => 0,
                'finished_goods_stock' => 0,
                'total_stock_qty' => 0,
                'low_stock_items' => 0,
            ];

        $summary = [
            'raw_material_stock' => round($stocks->raw_material_stock, 3),
            'finished_goods_stock' => round($stocks->finished_goods_stock, 3),
            'total_stock_qty' => round($stocks->total_stock_qty, 3),
            'total_items' => DB::table('item_master')->where('tenant_id', $tenantId)->count(),
            'low_stock_items' => (int) $stocks->low_stock_items,
            'active_branches' => DB::table('branch_master')->where('tenant_id', $tenantId)->where('status', 'Active')->count(),
            'team_count' => DB::table('team_master')->where('tenant_id', $tenantId)->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->count(),
            'wastage_stock' => (float) DB::table('stock_summary')
                ->leftJoin('storage_location_master', 'stock_summary.location_id', '=', 'storage_location_master.location_id')
                ->where('stock_summary.tenant_id', $tenantId)
                ->when($branchId, fn ($query) => $query->where('stock_summary.branch_id', $branchId))
                ->where('storage_location_master.location_code', 'WASTAGE')
                ->sum('stock_summary.stock_qty'),
            'pending_dispatch' => (int) DB::table('stock_ledger')
                ->where('stock_ledger.tenant_id', $tenantId)
                ->when($branchId, fn ($query) => $query->where('stock_ledger.branch_id', $branchId))
                ->where('stock_ledger.transaction_type', 'Dispatch')
                ->sum('qty_out'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard summary loaded.',
            'data' => $summary,
        ]);
    }

    public function trend(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $branchId = $request->user()->branch_id;
        $today = Carbon::today();

        $trendRows = DB::table('stock_ledger')
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('transaction_date', [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(transaction_date) as day')
            ->selectRaw('SUM(qty_in - qty_out) as net_qty')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('net_qty', 'day')
            ->toArray();

        $labels = [];
        $data = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = $today->copy()->subDays($daysAgo);
            $labels[] = $date->format('D');
            $data[] = (float) ($trendRows[$date->toDateString()] ?? 0);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard trend loaded.',
            'data' => [
                'labels' => $labels,
                'values' => $data,
            ],
        ]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $branchId = $request->user()->branch_id;

        $lowStockItems = DB::table('stock_summary')
            ->join('item_master', 'stock_summary.item_id', '=', 'item_master.item_id')
            ->where('stock_summary.tenant_id', $tenantId)
            ->when($branchId, fn ($query) => $query->where('stock_summary.branch_id', $branchId))
            ->whereRaw('stock_summary.stock_qty <= item_master.minimum_stock')
            ->orderBy('stock_summary.stock_qty')
            ->limit(5)
            ->get([
                'item_master.item_name as item_name',
                'stock_summary.stock_qty as current_qty',
                'item_master.minimum_stock as minimum_qty',
            ]);

        $pendingDispatchCount = DB::table('stock_ledger')
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('transaction_type', 'Dispatch')
            ->count();

        $wastageStock = (float) DB::table('stock_summary')
            ->leftJoin('storage_location_master', 'stock_summary.location_id', '=', 'storage_location_master.location_id')
            ->where('stock_summary.tenant_id', $tenantId)
            ->when($branchId, fn ($query) => $query->where('stock_summary.branch_id', $branchId))
            ->where('storage_location_master.location_code', 'WASTAGE')
            ->sum('stock_summary.stock_qty');

        $notifications = [];

        if ($lowStockItems->count() > 0) {
            $notifications[] = [
                'type' => 'warning',
                'message' => $lowStockItems->count().' item(s) under minimum stock.',
            ];
        }

        $notifications[] = [
            'type' => 'info',
            'message' => $pendingDispatchCount > 0
                ? $pendingDispatchCount.' dispatch record(s) awaiting action.'
                : 'No pending dispatches in the current window.',
        ];

        $notifications[] = [
            'type' => 'success',
            'message' => 'Wastage stock registered: '.number_format($wastageStock, 2),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard alerts loaded.',
            'data' => [
                'low_stock' => $lowStockItems,
                'pending_dispatch' => $pendingDispatchCount,
                'wastage_stock' => $wastageStock,
                'notifications' => $notifications,
            ],
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $branchId = $request->user()->branch_id;

        $activities = DB::table('stock_ledger')
            ->leftJoin('item_master', 'stock_ledger.item_id', '=', 'item_master.item_id')
            ->leftJoin('branch_master', 'stock_ledger.branch_id', '=', 'branch_master.branch_id')
            ->where('stock_ledger.tenant_id', $tenantId)
            ->when($branchId, fn ($query) => $query->where('stock_ledger.branch_id', $branchId))
            ->orderBy('stock_ledger.transaction_date', 'desc')
            ->limit(6)
            ->get([
                'stock_ledger.transaction_date as transaction_date',
                'stock_ledger.transaction_type as transaction_type',
                'item_master.item_name as item_name',
                'branch_master.branch_name as branch_name',
                'stock_ledger.qty_in as qty_in',
                'stock_ledger.qty_out as qty_out',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Recent activity loaded.',
            'data' => $activities,
        ]);
    }

    public function navigation(Request $request): JsonResponse
    {
        $roleName = DB::table('roles')->where('id', $request->user()->role_id)->value('name') ?? 'User';
        $items = $this->permissions->navigationForUser($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Available navigation loaded.',
            'data' => [
                'role_name' => $roleName,
                'items' => array_values($items),
            ],
        ]);
    }
}
