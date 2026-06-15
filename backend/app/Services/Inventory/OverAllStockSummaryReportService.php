<?php

namespace App\Services\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class OverAllStockSummaryReportService
{
    public function __construct(private readonly InventoryTransactionService $transactions)
    {
    }

    public function paginate(Request $request)
    {
        return $this->query($request)
            ->tap(fn ($query) => $this->applySorting($query, $request))
            ->paginate((int) min(max((int) $request->query('per_page', 25), 1), 200));
    }

    public function export(Request $request)
    {
        return $this->query($request)->orderBy('item_master.item_name')->limit(5000)->get();
    }

   
   

    public function history(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'item_id' => ['required', 'integer'],
            'location_id' => ['nullable', 'integer'],
        ]);

        return DB::table('stock_ledger')
            ->join('item_master', 'stock_ledger.item_id', '=', 'item_master.item_id')
            ->leftJoin('storage_location_master', 'stock_ledger.location_id', '=', 'storage_location_master.location_id')
            ->where('stock_ledger.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('stock_ledger.branch_id', $user->branch_id))
            ->where('stock_ledger.item_id', $request->integer('item_id'))
            ->when($request->filled('location_id'), fn ($query) => $query->where('stock_ledger.location_id', $request->integer('location_id')))
            ->orderByDesc('stock_ledger.transaction_date')
            ->orderByDesc('stock_ledger.ledger_id')
            ->limit(200)
            ->get([
                'stock_ledger.transaction_date',
                'stock_ledger.transaction_type',
                'stock_ledger.stock_type',
                'stock_ledger.reference_type',
                'stock_ledger.reference_id',
                'stock_ledger.qty_in',
                'stock_ledger.qty_out',
                'stock_ledger.balance_qty',
                'storage_location_master.location_name',
                'item_master.item_code',
                'item_master.item_name',
            ]);
    }

    public function dashboardMetrics(Request $request): array
    {
        $rows = $this->query($request)->get();

        return [
            'total_items' => $rows->count(),
            'available_stock' => round((float) $rows->sum('available_qty'), 3),
            'low_stock_items' => $rows->filter(fn ($row) => (float) $row->available_qty > 0 && (float) $row->available_qty <= (float) $row->minimum_stock)->count(),
            'out_of_stock_items' => $rows->filter(fn ($row) => (float) $row->available_qty <= 0)->count(),
        ];
    }

   private function query(Request $request)
{
    $user = $request->user();

    $query = DB::table('stock_summary')
        ->join('item_master', 'stock_summary.item_id', '=', 'item_master.item_id')
        ->leftJoin('material_type_master', 'item_master.material_type_id', '=', 'material_type_master.material_type_id')
        ->leftJoin('uom_master', 'item_master.uom_id', '=', 'uom_master.uom_id')
        ->leftJoin(DB::raw('(
            SELECT
                item_id,
                MAX(transaction_date) as last_movement_date
            FROM stock_ledger
            GROUP BY item_id
        ) ledger_last'), 'stock_summary.item_id', '=', 'ledger_last.item_id')

        ->where('stock_summary.tenant_id', $user->tenant_id)

        ->select([
    'item_master.item_id',
    'item_master.item_code',
    'item_master.item_name',
    'item_master.item_type',
    'item_master.minimum_stock',

    'material_type_master.material_type_name',
    'uom_master.uom_name',

    DB::raw('SUM(stock_summary.stock_qty) as available_qty'),
    DB::raw('AVG(stock_summary.avg_rate) as avg_rate'),
    DB::raw('SUM(stock_summary.stock_qty * stock_summary.avg_rate) as stock_value'),

    DB::raw('MAX(ledger_last.last_movement_date) as last_movement_date')
])
        ->groupBy([
    'item_master.item_id',
    'item_master.item_code',
    'item_master.item_name',
    'item_master.item_type',
    'item_master.minimum_stock',
    'material_type_master.material_type_name',
    'uom_master.uom_name'
]);

    // -----------------------------
    // FILTERS
    // -----------------------------
    foreach ([
        'item_id' => 'stock_summary.item_id',
        'item_type' => 'item_master.item_type',
        'material_type_id' => 'item_master.material_type_id',
        'branch_id' => 'stock_summary.branch_id',
        'location_id' => 'stock_summary.location_id',
        'stock_type' => 'stock_summary.stock_type',
    ] as $input => $column) {

        if ($request->filled($input)) {
            $query->where($column, $request->query($input));
        }
    }

    // -----------------------------
    // SEARCH
    // -----------------------------
    $search = trim((string) $request->query('search', ''));

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('item_master.item_name', 'like', "%{$search}%")
              ->orWhere('item_master.item_code', 'like', "%{$search}%");
        });
    }

    // -----------------------------
    // STOCK STATUS FILTER
    // -----------------------------
    if ($request->filled('stock_status')) {

        switch ($request->query('stock_status')) {

            case 'out':
                $query->havingRaw('SUM(stock_summary.stock_qty) <= 0');
                break;

            case 'low':
                $query->havingRaw('SUM(stock_summary.stock_qty) <= item_master.minimum_stock');
                $query->havingRaw('SUM(stock_summary.stock_qty) > 0');
                break;

            case 'available':
                $query->havingRaw('SUM(stock_summary.stock_qty) > item_master.minimum_stock');
                break;
        }
    }

    // -----------------------------
    // SAFE SORTING (IMPORTANT FIX)
    // -----------------------------
    $sortMap = [
        'item_code' => 'item_master.item_code',
        'item_name' => 'item_master.item_name',
        'material_type_name' => 'material_type_master.material_type_name',
        'uom_name' => 'uom_master.uom_name',
        'available_qty' => DB::raw('SUM(stock_summary.stock_qty)'),
        'avg_rate' => DB::raw('AVG(stock_summary.avg_rate)'),
        'stock_value' => DB::raw('SUM(stock_summary.stock_qty * stock_summary.avg_rate)'),
        'last_movement_date' => 'ledger_last.last_movement_date',
    ];

    $sortBy = $request->get('sort_by', 'item_name');
    $sortDirection = $request->get('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';

    if (!isset($sortMap[$sortBy])) {
        $sortBy = 'item_name';
    }

    $query->orderBy($sortMap[$sortBy], $sortDirection);

    return $query;
}
    private function applySorting($query, Request $request): void
{
    $columns = [
        'item_code'          => 'item_master.item_code',
        'item_name'          => 'item_master.item_name',
        'item_type'          => 'item_master.item_type',
        'material_type_name' => 'material_type_master.material_type_name',
        'uom_name'           => 'uom_master.uom_name',
        'last_movement_date' => 'ledger_last.last_movement_date',
    ];

    $sortKey = (string) $request->query('sort_by', '');
    $direction = strtolower((string) $request->query('sort_direction', 'asc')) === 'desc'
        ? 'desc'
        : 'asc';

    if ($sortKey === 'available_qty') {
        $query->orderByRaw("SUM(stock_summary.stock_qty) {$direction}");
        return;
    }

    if ($sortKey === 'avg_rate') {
        $query->orderByRaw("AVG(stock_summary.avg_rate) {$direction}");
        return;
    }

    if ($sortKey === 'stock_value') {
        $query->orderByRaw("SUM(stock_summary.stock_qty * stock_summary.avg_rate) {$direction}");
        return;
    }

    $sortBy = $columns[$sortKey] ?? 'item_master.item_name';

    $query->orderBy($sortBy, $direction);
}

    private function readImportRows(UploadedFile $file): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === 'xlsx') {
            return $this->readXlsxRows($file->getRealPath());
        }

        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages(['file' => 'Excel import requires PHP Zip extension. Please use the CSV template instead.']);
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => 'Unable to read Excel file.']);
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si ?? [] as $item) {
                $sharedStrings[] = (string) ($item->t ?? $item->r->t ?? '');
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            throw ValidationException::withMessages(['file' => 'Excel file does not contain Sheet1.']);
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row ?? [] as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $value = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }
                $values[] = $value;
            }
            $rows[] = $values;
        }

        return $rows;
    }

    private function combineRow(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    private function isBlankRow(array $data): bool
    {
        return collect($data)->every(fn ($value) => trim((string) $value) === '');
    }
}
