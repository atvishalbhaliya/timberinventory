<?php

namespace App\Services\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class StockSummaryReportService
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

    public function importTemplate()
    {
        return [
            ['item_code', 'item_name', 'location_name', 'qty', 'rate', 'reference'],
            ['ITEM-001', 'Sample Item Name', 'Main Store', '10', '125.50', '1001'],
        ];
    }

    public function importStock(Request $request): array
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $rows = $this->readImportRows($request->file('file'));
        if (count($rows) < 2) {
            throw ValidationException::withMessages(['file' => 'Import file does not contain stock rows.']);
        }

        $headers = array_map(fn ($value) => strtolower(trim((string) $value)), array_shift($rows));
        $required = ['item_code', 'location_name', 'qty', 'rate'];
        foreach ($required as $column) {
            if (! in_array($column, $headers, true)) {
                throw ValidationException::withMessages(['file' => "Missing required column: {$column}."]);
            }
        }

        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: DB::table('branch_master')->where('tenant_id', $user->tenant_id)->orderBy('branch_id')->value('branch_id'));
        if ($branchId <= 0) {
            throw ValidationException::withMessages(['branch_id' => 'No branch available for stock import.']);
        }

        $batchReference = (int) now()->format('ymdHis');
        $imported = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $headers, $user, $branchId, $batchReference, &$imported, &$errors): void {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $data = $this->combineRow($headers, $row);
                if ($this->isBlankRow($data)) {
                    continue;
                }

                $validator = Validator::make($data, [
                    'item_code' => ['required', 'string'],
                    'location_name' => ['required', 'string'],
                    'qty' => ['required', 'numeric', 'gt:0'],
                    'rate' => ['required', 'numeric', 'min:0'],
                    'reference' => ['nullable', 'string'],
                ]);

                if ($validator->fails()) {
                    $errors[] = "Row {$rowNumber}: ".$validator->errors()->first();
                    continue;
                }

                $item = DB::table('item_master')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('item_code', trim((string) $data['item_code']))
                    ->first();
                if (! $item) {
                    $errors[] = "Row {$rowNumber}: Item code not found.";
                    continue;
                }

                $location = DB::table('storage_location_master')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('location_name', trim((string) $data['location_name']))
                    ->first();
                if (! $location) {
                    $errors[] = "Row {$rowNumber}: Location not found.";
                    continue;
                }

                $qty = (float) $data['qty'];
                $rate = (float) $data['rate'];
                $reference = trim((string) ($data['reference'] ?? ''));
                $this->transactions->record([
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $branchId,
                    'item_id' => $item->item_id,
                    'location_id' => $location->location_id,
                    'stock_type' => 'Fresh',
                    'transaction_date' => now(),
                    'transaction_type' => 'Stock Import',
                    'reference_id' => is_numeric($reference) ? (int) $reference : $batchReference,
                    'reference_type' => 'STOCK_IMPORT',
                    'qty_in' => $qty,
                    'qty_out' => 0,
                    'rate' => $rate,
                    'amount' => round($qty * $rate, 2),
                    'user_id' => $user->getKey(),
                ]);

                $imported++;
            }
        });

        if ($imported === 0 && $errors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', array_slice($errors, 0, 5))]);
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
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

    protected function query(Request $request)
    {
        $user = $request->user();
        $query = DB::table('stock_summary')
            ->join('item_master', 'stock_summary.item_id', '=', 'item_master.item_id')
            ->leftJoin('material_type_master', 'item_master.material_type_id', '=', 'material_type_master.material_type_id')
            ->leftJoin('uom_master', 'item_master.uom_id', '=', 'uom_master.uom_id')
            ->leftJoin('branch_master', 'stock_summary.branch_id', '=', 'branch_master.branch_id')
            ->leftJoin('storage_location_master', 'stock_summary.location_id', '=', 'storage_location_master.location_id')
            ->leftJoin(DB::raw('(select item_id, branch_id, location_id, stock_type, max(transaction_date) as last_movement_date from stock_ledger group by item_id, branch_id, location_id, stock_type) as ledger_last'), function ($join): void {
                $join->on('stock_summary.item_id', '=', 'ledger_last.item_id')
                    ->on('stock_summary.branch_id', '=', 'ledger_last.branch_id')
                    ->on('stock_summary.location_id', '=', 'ledger_last.location_id')
                    ->on('stock_summary.stock_type', '=', 'ledger_last.stock_type');
            })
            ->where('stock_summary.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('stock_summary.branch_id', $user->branch_id))
            ->select([
                'stock_summary.stock_id',
                'stock_summary.branch_id',
                'stock_summary.location_id',
                'stock_summary.stock_type',
                'item_master.item_id',
                'item_master.item_code',
                'item_master.item_name',
                'item_master.item_type',
                'item_master.minimum_stock',
                'material_type_master.material_type_name',
                'uom_master.uom_name',
                'branch_master.branch_name',
                'storage_location_master.location_name',
                'stock_summary.stock_qty as available_qty',
                'stock_summary.avg_rate',
                DB::raw('(stock_summary.stock_qty * stock_summary.avg_rate) as stock_value'),
                'ledger_last.last_movement_date',
            ]);

        $this->applyItemTypeFilter($query);

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

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('item_master.item_name', 'like', "%{$search}%")
                    ->orWhere('item_master.item_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_status')) {
            match ($request->query('stock_status')) {
                'out' => $query->where('stock_summary.stock_qty', '<=', 0),
                'low' => $query->whereColumn('stock_summary.stock_qty', '<=', 'item_master.minimum_stock')->where('stock_summary.stock_qty', '>', 0),
                'available' => $query->whereColumn('stock_summary.stock_qty', '>', 'item_master.minimum_stock'),
                default => null,
            };
        }

        return $query;
    }

    protected function itemTypePatterns(): array
    {
        return ['Raw Material%'];
    }

    protected function applyItemTypeFilter($query): void
    {
        $patterns = array_values(array_filter($this->itemTypePatterns(), fn ($pattern) => is_string($pattern) && $pattern !== ''));

        if ($patterns === []) {
            return;
        }

        $query->where(function ($nested) use ($patterns): void {
            foreach ($patterns as $index => $pattern) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $nested->{$method}('item_master.item_type', 'like', $pattern);
            }
        });
    }

    private function applySorting($query, Request $request): void
    {
        $columns = [
            'item_code' => 'item_master.item_code',
            'item_name' => 'item_master.item_name',
            'item_type' => 'item_master.item_type',
            'material_type_name' => 'material_type_master.material_type_name',
            'branch_name' => 'branch_master.branch_name',
            'location_name' => 'storage_location_master.location_name',
            'stock_type' => 'stock_summary.stock_type',
            'uom_name' => 'uom_master.uom_name',
            'available_qty' => 'stock_summary.stock_qty',
            'avg_rate' => 'stock_summary.avg_rate',
            'stock_value' => 'stock_value',
            'last_movement_date' => 'ledger_last.last_movement_date',
        ];
        $sortKey = (string) $request->query('sort_by', '');
        $sortBy = $columns[$sortKey] ?? 'item_master.item_name';
        $direction = strtolower((string) $request->query('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortKey === 'stock_value') {
            $query->orderByRaw("(stock_summary.stock_qty * stock_summary.avg_rate) {$direction}");

            return;
        }

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
