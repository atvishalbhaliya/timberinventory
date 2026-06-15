<?php

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$masterModules = [
    'tenants' => 'Tenant',
    'branches' => 'Branch',
    'users' => 'User Master',
    'states' => 'State Master',
    'modules' => 'Module',
    'material-types' => 'Material Type Master',
    'uoms' => 'UOM Master',
    'items' => 'Item',
    'locations' => 'Location Master',
    'teams' => 'Team Master',
];

$operations = [
    'grn' => 'GRN',
    'stock-ledger' => 'Stock Ledger',
    'stock-summary' => 'Stock Summary',
    'overall-stock-summary' => 'OverAll Stock Summary',
    'stock-verification' => 'Stock Verification',
    'bom' => 'BOM',
    'production' => 'Production Entry',
    'wastage' => 'Wastage Management',
    'wastage-reuse' => 'Wastage Reuse',
    'dispatch-challan' => 'Dispatch Challan',
    'team-ledger' => 'Team Ledger',
    'team-payments' => 'Team Payments',
    'inventory-reports' => 'Inventory Reports',
    'production-reports' => 'Production Reports',
    'dispatch-reports' => 'Dispatch Reports',
    'payment-reports' => 'Payment Reports',
    'settings' => 'Settings',
];

Route::view('/', 'dashboard');
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/login', 'auth.login')->name('login');

Route::view('/roles', 'modules.role-permissions')->name('roles');
Route::view('/permissions', 'modules.permissions')->name('permissions');
Route::view('/grn', 'modules.grn')->name('operations.grn');
Route::view('/stock-ledger', 'modules.stock-ledger')->name('operations.stock-ledger');
Route::view('/stock-summary', 'modules.stock-summary')->name('operations.stock-summary');
Route::view('/overall-stock-summary', 'modules.overall-stock-summary')->name('operations.overall-stock-summary');
Route::view('/stock-verification', 'modules.stock-verification')->name('operations.stock-verification');
Route::view('/bom', 'modules.bom')->name('operations.bom');
Route::view('/production', 'modules.production')->name('operations.production');
Route::view('/wastage', 'modules.wastage')->name('operations.wastage');
Route::view('/wastage-reuse', 'modules.wastage-reuse')->name('operations.wastage-reuse');
Route::view('/dispatch-challan', 'modules.dispatch-challan')->name('operations.dispatch-challan');
Route::view('/team-ledger', 'modules.team-ledger')->name('operations.team-ledger');
Route::view('/team-payments', 'modules.team-payments')->name('operations.team-payments');
Route::view('/items', 'modules.items')->name('modules.items');
Route::redirect('/parties', '/customers')->name('modules.parties');
Route::get('/customers', fn () => view('modules.party-directory', [
    'partyType' => 'Customer',
    'title' => 'Customer Master',
    'singular' => 'Customer',
    'description' => 'Manage customer accounts, GST details, and contact information.',
]))->name('modules.customers');
Route::get('/suppliers', fn () => view('modules.party-directory', [
    'partyType' => 'Supplier',
    'title' => 'Supplier Master',
    'singular' => 'Supplier',
    'description' => 'Manage supplier accounts, GST details, and contact information.',
]))->name('modules.suppliers');
Route::redirect('/grn/create', '/grn')->name('operations.grn.create');
Route::redirect('/grn/{id}/edit', '/grn')->whereNumber('id')->name('operations.grn.edit');

foreach ($masterModules as $slug => $title) {
    if ($slug === 'items') {
        continue;
    }

    Route::get("/{$slug}", fn () => view('module', [
        'slug' => $slug,
        'title' => $title,
        'apiEndpoint' => "/v1/{$slug}",
        'isCrud' => true,
    ]))->name("modules.{$slug}");
}

foreach ($operations as $slug => $title) {
    if (in_array($slug, ['grn', 'stock-ledger','overall-stock-summary', 'stock-summary', 'stock-verification', 'bom', 'production', 'wastage', 'wastage-reuse', 'dispatch-challan', 'team-ledger', 'team-payments'], true)) {
        continue;
    }

    Route::get("/{$slug}", fn () => view('module', [
        'slug' => $slug,
        'title' => $title,
        'apiEndpoint' => null,
        'isCrud' => false,
    ]))->name("operations.{$slug}");
}

Route::post('/preferences/theme', function (Request $request) {
    $data = $request->validate([
        'themeColor' => ['nullable', 'string', 'max:30'],
        'themePreset' => ['nullable', 'string', 'max:30'],
        'sidebarTheme' => ['nullable', 'string', 'max:30'],
        'headerTheme' => ['nullable', 'string', 'max:30'],
        'darkMode' => ['nullable', 'string', 'max:30'],
        'layoutMode' => ['nullable', 'string', 'max:30'],
        'cardStyle' => ['nullable', 'string', 'max:30'],
        'borderRadius' => ['nullable', 'string', 'max:30'],
        'fontFamily' => ['nullable', 'string', 'max:30'],
    ]);

    if (auth()->id()) {
        UserPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'theme_preset' => $data['themePreset'] ?? 'timber',
                'theme_color' => $data['themeColor'] ?? 'timber',
                'sidebar_theme' => $data['sidebarTheme'] ?? 'dark',
                'header_theme' => $data['headerTheme'] ?? 'light',
                'dark_mode' => $data['darkMode'] ?? 'light',
                'layout_mode' => $data['layoutMode'] ?? 'standard',
                'card_style' => $data['cardStyle'] ?? 'material',
                'border_radius' => $data['borderRadius'] ?? 'comfortable',
                'font_family' => $data['fontFamily'] ?? 'inter',
            ]
        );
    }

    session(['theme_preferences' => $data]);

    return response()->json(['saved' => true]);
})->name('preferences.theme');
