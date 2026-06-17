<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BOMController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\GRNController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\DispatchController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProductionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SetupStatusController;
use App\Http\Controllers\Api\V1\StockLedgerController;
use App\Http\Controllers\Api\V1\StockSummaryController;
use App\Http\Controllers\Api\V1\OverallStockSummaryController;
use App\Http\Controllers\Api\V1\WastageSummaryController;
use App\Http\Controllers\Api\V1\StockVerificationController;
use App\Http\Controllers\Api\V1\WastageController;
use App\Http\Controllers\Api\V1\TeamLedgerController;
use App\Http\Controllers\Api\V1\TeamPaymentController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\WastageReuseController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);
    Route::get('/setup/status', SetupStatusController::class);

    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:sanctum')->prefix('dashboard')->group(function (): void {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/trend', [DashboardController::class, 'trend']);
        Route::get('/alerts', [DashboardController::class, 'alerts']);
        Route::get('/recent', [DashboardController::class, 'recent']);
        Route::get('/navigation', [DashboardController::class, 'navigation']);
    });

    Route::middleware('auth:sanctum')->prefix('admin')->group(function (): void {
        Route::get('/modules', [ModuleController::class, 'index']);
        Route::get('/roles/export', [RoleController::class, 'export'])->middleware('permission:roles.view');
        Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');
        Route::put('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:role-permissions.manage');

        Route::get('/permissions/export', [PermissionController::class, 'export'])->middleware('permission:permissions.view');
        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view');
        Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:permissions.create');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/grns', [GRNController::class, 'index'])->middleware('permission:purchase-grn.view');
        Route::get('/grns/next-number', [GRNController::class, 'nextNumber'])->middleware('permission:purchase-grn.view');
        Route::post('/grns', [GRNController::class, 'store'])->middleware('permission:purchase-grn.manage');
        Route::get('/grns/{id}', [GRNController::class, 'show'])->whereNumber('id')->middleware('permission:purchase-grn.view');
        Route::put('/grns/{id}', [GRNController::class, 'update'])->whereNumber('id')->middleware('permission:purchase-grn.manage');
        Route::delete('/grns/{id}', [GRNController::class, 'destroy'])->whereNumber('id')->middleware('permission:purchase-grn.manage');
        Route::post('/grns/{id}/post', [GRNController::class, 'post'])->whereNumber('id')->middleware('permission:purchase-grn.manage');
        Route::post('/grns/{id}/cancel', [GRNController::class, 'cancel'])->whereNumber('id')->middleware('permission:purchase-grn.manage');

        Route::get('/stock-ledger/export', [StockLedgerController::class, 'export'])->middleware('permission:stock-ledger.view');
        Route::get('/stock-ledger', [StockLedgerController::class, 'index'])->middleware('permission:stock-ledger.view');

        Route::get('/stock-summary/export', [StockSummaryController::class, 'export'])->middleware('permission:stock-summary.view');
        Route::get('/stock-summary/import-template', [StockSummaryController::class, 'importTemplate'])->middleware('permission:stock-summary.view');
        Route::post('/stock-summary/import', [StockSummaryController::class, 'import'])->middleware('permission:stock-summary.view');
        Route::get('/stock-summary/history', [StockSummaryController::class, 'history'])->middleware('permission:stock-summary.view');
        Route::get('/stock-summary', [StockSummaryController::class, 'index'])->middleware('permission:stock-summary.view');
     
         Route::get('/overall-stock-summary/export', [OverallStockSummaryController::class, 'export'])->middleware('permission:stock-summary.view');
        Route::get('/overall-stock-summary/import-template', [OverallStockSummaryController::class, 'importTemplate'])->middleware('permission:stock-summary.view');
        Route::post('/overall-stock-summary/import', [OverallStockSummaryController::class, 'import'])->middleware('permission:stock-summary.view');
        Route::get('/overall-stock-summary/history', [OverallStockSummaryController::class, 'history'])->middleware('permission:stock-summary.view');
        Route::get('/overall-stock-summary', [OverallStockSummaryController::class, 'index'])->middleware('permission:stock-summary.view');
        Route::get('/wastage-summary/export', [WastageSummaryController::class, 'export'])->middleware('permission:wastage.view');
        Route::get('/wastage-summary/history', [WastageSummaryController::class, 'history'])->middleware('permission:wastage.view');
        Route::get('/wastage-summary', [WastageSummaryController::class, 'index'])->middleware('permission:wastage.view');

        Route::get('/stock-verifications/current-stock', [StockVerificationController::class, 'currentStock'])->middleware('permission:stock-verification.view');
        Route::get('/stock-verifications', [StockVerificationController::class, 'index'])->middleware('permission:stock-verification.view');
        Route::post('/stock-verifications', [StockVerificationController::class, 'store'])->middleware('permission:stock-verification.create');
        Route::get('/stock-verifications/{id}', [StockVerificationController::class, 'show'])->whereNumber('id')->middleware('permission:stock-verification.view');
        Route::put('/stock-verifications/{id}', [StockVerificationController::class, 'update'])->whereNumber('id')->middleware('permission:stock-verification.edit');
        Route::delete('/stock-verifications/{id}', [StockVerificationController::class, 'destroy'])->whereNumber('id')->middleware('permission:stock-verification.edit');
        Route::post('/stock-verifications/{id}/submit', [StockVerificationController::class, 'submit'])->whereNumber('id')->middleware('permission:stock-verification.submit');
        Route::post('/stock-verifications/{id}/approve', [StockVerificationController::class, 'approve'])->whereNumber('id')->middleware('permission:stock-verification.approve');
        Route::post('/stock-verifications/{id}/cancel', [StockVerificationController::class, 'cancel'])->whereNumber('id')->middleware('permission:stock-verification.cancel');

        Route::get('/boms/export', [BOMController::class, 'export'])->middleware('permission:bom.view');
        Route::get('/boms/next-number', [BOMController::class, 'nextNumber'])->middleware('permission:bom.view');
        Route::get('/boms', [BOMController::class, 'index'])->middleware('permission:bom.view');
        Route::post('/boms', [BOMController::class, 'store'])->middleware('permission:bom.manage');
        Route::get('/boms/{id}', [BOMController::class, 'show'])->whereNumber('id')->middleware('permission:bom.view');
        Route::put('/boms/{id}', [BOMController::class, 'update'])->whereNumber('id')->middleware('permission:bom.manage');
        Route::delete('/boms/{id}', [BOMController::class, 'destroy'])->whereNumber('id')->middleware('permission:bom.manage');

        Route::get('/production/export', [ProductionController::class, 'export'])->middleware('permission:production.view');
        Route::get('/production/next-number', [ProductionController::class, 'nextNumber'])->middleware('permission:production.view');
        Route::get('/production/current-stock', [ProductionController::class, 'currentStock'])->middleware('permission:production.view');
        Route::get('/production/bom/{bom}/materials', [ProductionController::class, 'bomMaterials'])->whereNumber('bom')->middleware('permission:production.view');
        Route::get('/production', [ProductionController::class, 'index'])->middleware('permission:production.view');
        Route::post('/production', [ProductionController::class, 'store'])->middleware('permission:production.manage');
        Route::get('/production/{id}', [ProductionController::class, 'show'])->whereNumber('id')->middleware('permission:production.view');
        Route::put('/production/{id}', [ProductionController::class, 'update'])->whereNumber('id')->middleware('permission:production.manage');
        Route::delete('/production/{id}', [ProductionController::class, 'destroy'])->whereNumber('id')->middleware('permission:production.manage');
        Route::post('/production/{id}/post', [ProductionController::class, 'post'])->whereNumber('id')->middleware('permission:production.post');
        Route::post('/production/{id}/cancel', [ProductionController::class, 'cancel'])->whereNumber('id')->middleware('permission:production.cancel');

        Route::get('/wastage/export', [WastageController::class, 'export'])->middleware('permission:wastage.view');
        Route::get('/wastage', [WastageController::class, 'index'])->middleware('permission:wastage.view');
        Route::post('/wastage', [WastageController::class, 'store'])->middleware('permission:wastage.manage');
        Route::get('/wastage/{id}', [WastageController::class, 'show'])->whereNumber('id')->middleware('permission:wastage.view');
        Route::put('/wastage/{id}', [WastageController::class, 'update'])->whereNumber('id')->middleware('permission:wastage.manage');
        Route::delete('/wastage/{id}', [WastageController::class, 'destroy'])->whereNumber('id')->middleware('permission:wastage.manage');
        Route::post('/wastage/{id}/post', [WastageController::class, 'post'])->whereNumber('id')->middleware('permission:wastage.post');
        Route::post('/wastage/{id}/cancel', [WastageController::class, 'cancel'])->whereNumber('id')->middleware('permission:wastage.cancel');

        Route::get('/wastage-reuse/export', [WastageReuseController::class, 'export'])->middleware('permission:wastage-reuse.view');
        Route::get('/wastage-reuse/next-number', [WastageReuseController::class, 'nextNumber'])->middleware('permission:wastage-reuse.view');
        Route::get('/wastage-reuse', [WastageReuseController::class, 'index'])->middleware('permission:wastage-reuse.view');
        Route::post('/wastage-reuse', [WastageReuseController::class, 'store'])->middleware('permission:wastage-reuse.manage');
        Route::get('/wastage-reuse/{id}', [WastageReuseController::class, 'show'])->whereNumber('id')->middleware('permission:wastage-reuse.view');
        Route::put('/wastage-reuse/{id}', [WastageReuseController::class, 'update'])->whereNumber('id')->middleware('permission:wastage-reuse.manage');
        Route::delete('/wastage-reuse/{id}', [WastageReuseController::class, 'destroy'])->whereNumber('id')->middleware('permission:wastage-reuse.manage');
        Route::post('/wastage-reuse/{id}/post', [WastageReuseController::class, 'post'])->whereNumber('id')->middleware('permission:wastage-reuse.post');
        Route::post('/wastage-reuse/{id}/cancel', [WastageReuseController::class, 'cancel'])->whereNumber('id')->middleware('permission:wastage-reuse.cancel');

        Route::get('/dispatch/challans/export', [DispatchController::class, 'export'])->middleware('permission:dispatch.view');
        Route::get('/dispatch/challans/next-number', [DispatchController::class, 'nextNumber'])->middleware('permission:dispatch.view');
        Route::get('/dispatch/challans/line-metrics', [DispatchController::class, 'lineMetrics'])->middleware('permission:dispatch.view');
        Route::get('/dispatch/challans', [DispatchController::class, 'index'])->middleware('permission:dispatch.view');
        Route::post('/dispatch/challans', [DispatchController::class, 'store'])->middleware('permission:dispatch.manage');
        Route::get('/dispatch/challans/{id}', [DispatchController::class, 'show'])->whereNumber('id')->middleware('permission:dispatch.view');
        Route::put('/dispatch/challans/{id}', [DispatchController::class, 'update'])->whereNumber('id')->middleware('permission:dispatch.manage');
        Route::delete('/dispatch/challans/{id}', [DispatchController::class, 'destroy'])->whereNumber('id')->middleware('permission:dispatch.manage');

        Route::get('/team-ledger/export', [TeamLedgerController::class, 'export'])->middleware('permission:accounts.view');
        Route::get('/team-ledger', [TeamLedgerController::class, 'index'])->middleware('permission:accounts.view');

        Route::get('/team-payments/export', [TeamPaymentController::class, 'export'])->middleware('permission:accounts.view');
        Route::get('/team-payments', [TeamPaymentController::class, 'index'])->middleware('permission:accounts.view');
        Route::post('/team-payments/refresh', [TeamPaymentController::class, 'refresh'])->middleware('permission:accounts.manage');
        Route::get('/team-payments/{id}', [TeamPaymentController::class, 'show'])->whereNumber('id')->middleware('permission:accounts.view');
        Route::post('/team-payments/{id}/payments', [TeamPaymentController::class, 'pay'])->whereNumber('id')->middleware('permission:accounts.manage');

        Route::get('/parties/next-code', [MasterDataController::class, 'nextPartyCode'])->middleware('permission:masters.view');
        Route::get('/items/import-template', [MasterDataController::class, 'itemImportTemplate'])->middleware('permission:masters.view');
        Route::post('/items/import', [MasterDataController::class, 'importItems'])->middleware('permission:masters.manage');

        foreach (['tenants', 'branches', 'users', 'parties', 'states', 'modules', 'material-types', 'uoms', 'items', 'locations', 'teams', 'pallet-models'] as $module) {
            Route::get("/{$module}/export", [MasterDataController::class, 'export'])->defaults('module', $module)->middleware('permission:masters.view');
            Route::get("/{$module}", [MasterDataController::class, 'index'])->defaults('module', $module)->middleware('permission:masters.view');
            Route::post("/{$module}", [MasterDataController::class, 'store'])->defaults('module', $module)->middleware('permission:masters.manage');
            Route::get("/{$module}/{id}", [MasterDataController::class, 'show'])->defaults('module', $module)->whereNumber('id')->middleware('permission:masters.view');
            Route::put("/{$module}/{id}", [MasterDataController::class, 'update'])->defaults('module', $module)->whereNumber('id')->middleware('permission:masters.manage');
            Route::delete("/{$module}/{id}", [MasterDataController::class, 'destroy'])->defaults('module', $module)->whereNumber('id')->middleware('permission:masters.manage');
        }
    });
});
