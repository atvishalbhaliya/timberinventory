<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterDataController extends Controller
{
    private const MODULES = [
        'tenants' => [
            'table' => 'tenant_master',
            'key' => 'tenant_id',
            'title' => 'Tenant',
            'search' => ['tenant_code', 'tenant_name', 'company_name', 'mobile', 'email', 'status'],
            'columns' => ['tenant_code', 'tenant_name', 'company_name', 'mobile', 'email', 'status'],
            'required' => ['tenant_name'],
            'tenant_scope' => false,
            'branch_scope' => false,
        ],
        'branches' => [
            'table' => 'branch_master',
            'key' => 'branch_id',
            'title' => 'Branch',
            'search' => ['branch_code', 'branch_name', 'city', 'state', 'mobile'],
            'columns' => ['branch_code', 'branch_name', 'address', 'city', 'state', 'country', 'mobile'],
            'required' => ['branch_name'],
            'tenant_scope' => true,
            'branch_scope' => false,
        ],
        'users' => [
            'table' => 'users',
            'key' => 'id',
            'title' => 'User',
            'search' => ['login_id', 'employee_code', 'full_name', 'mobile', 'email', 'status'],
            'columns' => ['login_id', 'password', 'employee_code', 'full_name', 'mobile', 'email', 'role_id', 'status'],
            'required' => ['login_id', 'full_name'],
            'tenant_scope' => true,
            'branch_scope' => false,
        ],
        'parties' => [
            'table' => 'party_master',
            'key' => 'party_id',
            'title' => 'Party',
            'search' => ['party_code', 'party_name', 'party_type', 'contact_person', 'mobile', 'email', 'gst_no', 'pan_no', 'state'],
            'columns' => ['party_code', 'party_type', 'party_name', 'contact_person', 'mobile', 'email', 'address', 'state', 'gst_no', 'pan_no', 'remarks'],
            'required' => ['party_name', 'party_type'],
            'tenant_scope' => true,
            'branch_scope' => true,
        ],
        'states' => [
            'table' => 'state_master',
            'key' => 'state_id',
            'title' => 'State',
            'search' => ['state_code', 'state_name', 'status'],
            'columns' => ['state_code', 'state_name', 'status'],
            'required' => ['state_name'],
            'tenant_scope' => false,
            'branch_scope' => false,
        ],
        'modules' => [
            'table' => 'erp_modules',
            'key' => 'module_id',
            'title' => 'Module',
            'search' => ['module_code', 'module_name', 'route', 'status', 'description'],
            'columns' => ['module_code', 'module_name', 'parent_module_id', 'icon', 'display_order', 'route', 'status', 'description'],
            'required' => ['module_code', 'module_name'],
            'tenant_scope' => false,
            'branch_scope' => false,
        ],
        'material-types' => [
            'table' => 'material_type_master',
            'key' => 'material_type_id',
            'title' => 'Material Type',
            'search' => ['material_type_code', 'material_type_name'],
            'columns' => ['material_type_code', 'material_type_name'],
            'required' => ['material_type_name'],
            'tenant_scope' => true,
            'branch_scope' => false,
        ],
        'uoms' => [
            'table' => 'uom_master',
            'key' => 'uom_id',
            'title' => 'UOM',
            'search' => ['uom_code', 'uom_name'],
            'columns' => ['uom_code', 'uom_name'],
            'required' => ['uom_name'],
            'tenant_scope' => true,
            'branch_scope' => false,
        ],
        'items' => [
            'table' => 'item_master',
            'key' => 'item_id',
            'title' => 'Item',
            'search' => ['item_code', 'item_name', 'item_type', 'status'],
            'columns' => ['item_code', 'item_name', 'item_type', 'material_type_id', 'uom_id', 'length_mm', 'width_mm', 'thickness_mm', 'cft_factor', 'minimum_stock', 'opening_qty', 'opening_rate', 'status', 'created_at'],
            'required' => ['item_name', 'item_type'],
            'tenant_scope' => true,
            'branch_scope' => false,
        ],
        'locations' => [
            'table' => 'storage_location_master',
            'key' => 'location_id',
            'title' => 'Location',
            'search' => ['location_code', 'location_name', 'location_type', 'status'],
            'columns' => ['location_code', 'location_name', 'location_type', 'status'],
            'required' => ['location_name', 'location_type'],
            'tenant_scope' => true,
            'branch_scope' => true,
        ],
        'teams' => [
            'table' => 'team_master',
            'key' => 'team_id',
            'title' => 'Team',
            'search' => ['team_code', 'team_name', 'contractor_name', 'status'],
            'columns' => ['team_code', 'team_name', 'contractor_name', 'rate_per_pallet', 'tds_percent', 'status'],
            'required' => ['team_name'],
            'tenant_scope' => true,
            'branch_scope' => true,
        ],
        'pallet-models' => [
            'table' => 'pallet_model_master',
            'key' => 'pallet_model_id',
            'title' => 'Pallet Model',
            'search' => ['model_code', 'model_name', 'wood_type'],
            'columns' => ['model_code', 'model_name', 'length', 'width', 'height', 'wood_type'],
            'required' => ['model_name'],
            'tenant_scope' => true,
            'branch_scope' => false,
        ],
    ];

    public function index(Request $request, string $module): JsonResponse
    {
        $config = $this->config($module);
        $query = $this->baseQuery($request, $config);
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($query) use ($config, $search): void {
                foreach ($config['search'] as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if ($module === 'parties') {
            $this->applyPartyFilters($query, $request);
        }

        if ($module === 'items') {
            $this->applyItemFilters($query, $request);
        }

        if (! in_array($module, ['parties', 'items'], true)) {
            $this->applyGenericColumnFilters($query, $request, $config);
        }

        if ($module === 'states') {
            $query->where('status', 'Active');
        }

        $perPage = (int) min(max((int) $request->query('per_page', 10), 1), 100);

        $sortBy = $request->query('sort_by', $config['key']);
        $sortDirection = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $rows = $query
            ->when(
                in_array($sortBy, $this->resultColumns($config), true),
                fn ($query) => $query->orderBy($sortBy, $sortDirection),
                fn ($query) => $query->orderByDesc($config['key'])
            )
            ->select($this->resultColumns($config))
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => $config['title'].' records loaded.',
            'data' => $rows,
            'meta' => [
                'module' => $module,
                'title' => $config['title'],
                'key' => $config['key'],
                'columns' => $this->tableColumns($config),
                'form_columns' => $this->formColumns($config),
                'required' => $config['required'],
                'lookups' => $this->lookupMeta($request, $config),
            ],
        ]);
    }

    public function store(Request $request, string $module): JsonResponse
    {
        $config = $this->config($module);
        $payload = $this->payload($request, $config, true);

        $id = DB::table($config['table'])->insertGetId($payload, $config['key']);
        $row = $this->baseQuery($request, $config)
            ->select($this->resultColumns($config))
            ->where($config['key'], $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => $config['title'].' created.',
            'data' => $row,
        ], 201);
    }

    public function nextPartyCode(Request $request): JsonResponse
    {
        $partyType = $request->validate([
            'party_type' => ['required', Rule::in(['Customer', 'Supplier'])],
        ])['party_type'];

        return response()->json([
            'success' => true,
            'message' => 'Next party code generated.',
            'data' => [
                'party_code' => $this->nextPartyCodeForType($request, $partyType),
            ],
        ]);
    }

    public function show(Request $request, int|string $id, string $module): JsonResponse
    {
        $config = $this->config($module);
        $row = $this->baseQuery($request, $config)
            ->select($this->resultColumns($config))
            ->where($config['key'], $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => $config['title'].' loaded.',
            'data' => $row,
        ]);
    }

    public function update(Request $request, int|string $id, string $module): JsonResponse
    {
        $config = $this->config($module);
        $payload = $this->payload($request, $config, false, $id);

        $this->baseQuery($request, $config)->where($config['key'], $id)->update($payload);
        $row = $this->baseQuery($request, $config)
            ->select($this->resultColumns($config))
            ->where($config['key'], $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => $config['title'].' updated.',
            'data' => $row,
        ]);
    }

    public function destroy(Request $request, int|string $id, string $module): JsonResponse
    {
        $config = $this->config($module);
        $query = $this->baseQuery($request, $config)->where($config['key'], $id);

        if (Schema::hasColumn($config['table'], 'deleted_at')) {
            $query->update(['deleted_at' => now(), 'updated_at' => now(), 'updated_by' => $request->user()?->getKey()]);
        } else {
            $query->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $config['title'].' deleted.',
            'data' => [],
        ]);
    }

    public function export(Request $request, string $module): JsonResponse
    {
        $config = $this->config($module);
        $query = $this->baseQuery($request, $config);
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($query) use ($config, $search): void {
                foreach ($config['search'] as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if ($module === 'parties') {
            $this->applyPartyFilters($query, $request);
        }

        if ($module === 'items') {
            $this->applyItemFilters($query, $request);
        }

        if (! in_array($module, ['parties', 'items'], true)) {
            $this->applyGenericColumnFilters($query, $request, $config);
        }

        $rows = $query
            ->select($this->resultColumns($config))
            ->orderByDesc($config['key'])
            ->limit(1000)
            ->get();

        return response()->json([
            'success' => true,
            'message' => $config['title'].' export loaded.',
            'data' => $rows,
        ]);
    }

    public function itemImportTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo "\xEF\xBB\xBF";

            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->itemImportHeaders());
            fputcsv($handle, ['RM-PLY-001', 'Plywood 18mm', 'Raw Material', 'Wood', 'CFT', '2440', '1220', '18', '1', '10', 'Active']);
            fputcsv($handle, ['FG-PALLET-001', 'Standard Pallet', 'Finish Product', 'Finished Goods', 'PCS', '1200', '1000', '150', '1', '0', 'Active']);
            fclose($handle);
        }, 'item-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importItems(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        [$headerRow, $importRows] = $this->readItemImportCsv($request->file('file')->getRealPath());

        if ($headerRow === []) {
            throw ValidationException::withMessages(['file' => ['Import file is empty.']]);
        }

        $headers = array_map(fn ($header) => $this->normalizeItemImportHeader((string) $header), $headerRow);
        $requiredHeaders = ['item_name', 'item_type'];
        $missingHeaders = array_values(array_diff($requiredHeaders, $headers));

        if ($missingHeaders !== []) {
            throw ValidationException::withMessages([
                'file' => ['Missing required columns: '.implode(', ', $missingHeaders).'. Please download the sample file again and upload it as CSV.'],
            ]);
        }

        $tenantId = $request->user()?->tenant_id;
        $materialTypes = $this->importLookupMap('material_type_master', 'material_type_id', 'material_type_code', 'material_type_name', $tenantId);
        $uoms = $this->importLookupMap('uom_master', 'uom_id', 'uom_code', 'uom_name', $tenantId);
        $errors = [];
        $items = [];
        $rowNumber = 1;

        foreach ($importRows as $row) {
            $rowNumber++;
            $row = array_pad($row, count($headers), null);
            $data = array_combine($headers, array_slice($row, 0, count($headers)));

            if ($this->isBlankImportRow($data)) {
                continue;
            }

            if (count($items) >= 1000) {
                $errors[] = "Row {$rowNumber}: Maximum 1000 items can be imported at once.";
                break;
            }

            $itemName = trim((string) $this->importValue($data, ['item_name', 'name']));
            $itemType = trim((string) $this->importValue($data, ['item_type', 'type']));
            $status = trim((string) ($this->importValue($data, ['status']) ?: 'Active'));
            $materialTypeValue = $this->importValue($data, ['material_type_id', 'material_type', 'material_type_name', 'material']);
            $uomValue = $this->importValue($data, ['uom_id', 'uom', 'uom_name']);

            if ($itemName === '') {
                $errors[] = "Row {$rowNumber}: Item name is required.";
            }

            if (! in_array($itemType, ['Raw Material', 'Semi Product', 'Finish Product', 'Wastage', 'Scrap', 'Consumable'], true)) {
                $errors[] = "Row {$rowNumber}: Item type must be Raw Material, Semi Product, Finish Product, Wastage, Scrap, or Consumable.";
            }

            if (! in_array($status, ['Active', 'Inactive'], true)) {
                $errors[] = "Row {$rowNumber}: Status must be Active or Inactive.";
            }

            $materialTypeId = $this->resolveImportLookupId($materialTypeValue, $materialTypes);
            $uomId = $this->resolveImportLookupId($uomValue, $uoms);

            if (! blank($materialTypeValue) && $materialTypeId === null) {
                $errors[] = "Row {$rowNumber}: Material Type '{$materialTypeValue}' was not found.";
            }

            if (! blank($uomValue) && $uomId === null) {
                $errors[] = "Row {$rowNumber}: UOM '{$uomValue}' was not found.";
            }

            $numeric = [];
            foreach (['length_mm', 'width_mm', 'thickness_mm', 'cft_factor', 'minimum_stock'] as $column) {
                $numeric[$column] = $this->importNumericValue($this->importValue($data, [$column, str_replace('_mm', '', $column)]), $column, $rowNumber, $errors);
            }

            $items[] = [
                'item_code' => trim((string) $this->importValue($data, ['item_code', 'code'])),
                'item_name' => $itemName,
                'item_type' => $itemType,
                'material_type_id' => $materialTypeId,
                'uom_id' => $uomId,
                'length_mm' => $numeric['length_mm'],
                'width_mm' => $numeric['width_mm'],
                'thickness_mm' => $numeric['thickness_mm'],
                'cft_factor' => $numeric['cft_factor'],
                'minimum_stock' => $numeric['minimum_stock'] ?? 0,
                'opening_qty' => 0,
                'opening_rate' => 0,
                'status' => $status,
            ];
        }

        if ($items === [] && $errors === []) {
            $errors[] = 'No item rows found to import.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => array_slice($errors, 0, 25)]);
        }

        $user = $request->user();
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($items, $tenantId, $user, &$created, &$updated): void {
            foreach ($items as $item) {
                $existingId = null;

                if ($item['item_code'] !== '') {
                    $existingId = DB::table('item_master')
                        ->where('tenant_id', $tenantId)
                        ->where('item_code', $item['item_code'])
                        ->when(Schema::hasColumn('item_master', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                        ->value('item_id');
                }

                if ($item['item_code'] === '') {
                    $item['item_code'] = null;
                }

                if (Schema::hasColumn('item_master', 'tenant_id')) {
                    $item['tenant_id'] = $tenantId;
                }

                if (Schema::hasColumn('item_master', 'updated_by')) {
                    $item['updated_by'] = $user?->getKey();
                }

                if (Schema::hasColumn('item_master', 'updated_at')) {
                    $item['updated_at'] = now();
                }

                if ($existingId) {
                    DB::table('item_master')->where('item_id', $existingId)->update($item);
                    $updated++;

                    continue;
                }

                if (Schema::hasColumn('item_master', 'created_by')) {
                    $item['created_by'] = $user?->getKey();
                }

                if (Schema::hasColumn('item_master', 'created_at')) {
                    $item['created_at'] = now();
                }

                DB::table('item_master')->insert($item);
                $created++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Items imported. {$created} created, {$updated} updated.",
            'data' => [
                'created' => $created,
                'updated' => $updated,
            ],
        ]);
    }

    private function config(string $module): array
    {
        abort_unless(isset(self::MODULES[$module]), 404, 'Unknown master module.');

        return self::MODULES[$module];
    }

    private function itemImportHeaders(): array
    {
        return ['item_code', 'item_name', 'item_type', 'material_type', 'uom', 'length_mm', 'width_mm', 'thickness_mm', 'cft_factor', 'minimum_stock', 'status'];
    }

    private function readItemImportCsv(string $path): array
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw ValidationException::withMessages(['file' => ['Unable to read import file.']]);
        }

        $lines = array_values(array_filter($lines, fn ($line) => trim((string) $line) !== ''));

        if ($lines === []) {
            return [[], []];
        }

        $lines[0] = str_replace("\xEF\xBB\xBF", '', $lines[0]);
        $delimiter = null;

        if (str_starts_with(strtolower(trim($lines[0])), 'sep=')) {
            $delimiter = substr(trim($lines[0]), 4, 1) ?: ',';
            array_shift($lines);
        }

        if ($lines === []) {
            return [[], []];
        }

        $delimiter ??= $this->detectItemImportDelimiter($lines[0]);
        $header = str_getcsv($lines[0], $delimiter);
        $rows = [];

        foreach (array_slice($lines, 1) as $line) {
            $rows[] = str_getcsv($line, $delimiter);
        }

        return [$header, $rows];
    }

    private function detectItemImportDelimiter(string $line): string
    {
        $delimiters = [',', ';', "\t"];
        $bestDelimiter = ',';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = count(str_getcsv($line, $delimiter));

            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    private function normalizeItemImportHeader(string $header): string
    {
        $header = str_replace("\xEF\xBB\xBF", '', trim($header));
        $header = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $header));
        $header = trim($header, '_');

        return match ($header) {
            'code' => 'item_code',
            'name' => 'item_name',
            'type' => 'item_type',
            'material', 'material_name' => 'material_type',
            'uom_name', 'unit', 'unit_name' => 'uom',
            'length' => 'length_mm',
            'width' => 'width_mm',
            'thickness' => 'thickness_mm',
            default => $header,
        };
    }

    private function importLookupMap(string $table, string $key, string $code, string $name, mixed $tenantId): array
    {
        $rows = DB::table($table)
            ->where('tenant_id', $tenantId)
            ->when(Schema::hasColumn($table, 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
            ->get([$key, $code, $name]);

        $map = [];

        foreach ($rows as $row) {
            foreach ([$row->{$key}, $row->{$code}, $row->{$name}] as $value) {
                if (! blank($value)) {
                    $map[$this->normalizeImportLookup($value)] = (int) $row->{$key};
                }
            }
        }

        return $map;
    }

    private function normalizeImportLookup(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function resolveImportLookupId(mixed $value, array $lookup): ?int
    {
        if (blank($value)) {
            return null;
        }

        return $lookup[$this->normalizeImportLookup($value)] ?? null;
    }

    private function importValue(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && ! blank($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }

    private function importNumericValue(mixed $value, string $column, int $rowNumber, array &$errors): ?float
    {
        if (blank($value)) {
            return null;
        }

        $normalized = str_replace(',', '', trim((string) $value));

        if (! is_numeric($normalized) || (float) $normalized < 0) {
            $errors[] = "Row {$rowNumber}: {$column} must be a positive number.";

            return null;
        }

        return (float) $normalized;
    }

    private function isBlankImportRow(array $data): bool
    {
        foreach ($data as $value) {
            if (! blank($value)) {
                return false;
            }
        }

        return true;
    }

    private function baseQuery(Request $request, array $config)
    {
        $query = DB::table($config['table']);
        $user = $request->user();

        if (Schema::hasColumn($config['table'], 'deleted_at')) {
            $query->whereNull($config['table'].'.deleted_at');
        }

        if ($user && $config['tenant_scope'] && Schema::hasColumn($config['table'], 'tenant_id')) {
            $query->where($config['table'].'.tenant_id', $user->tenant_id);
        }

        if ($user && $config['branch_scope'] && Schema::hasColumn($config['table'], 'branch_id') && $user->branch_id) {
            $query->where($config['table'].'.branch_id', $user->branch_id);
        }

        return $query;
    }

    private function tableColumns(array $config): array
    {
        return array_values(array_diff($config['columns'], ['password']));
    }

    private function resultColumns(array $config): array
    {
        return array_values(array_unique(array_merge([$config['key']], $this->tableColumns($config))));
    }

    private function formColumns(array $config): array
    {
        if ($config['table'] === 'users') {
            return $config['columns'];
        }

        return $this->tableColumns($config);
    }

    private function lookupMeta(Request $request, array $config): array
    {
        if ($config['table'] === 'users') {
            return [
                'roles' => DB::table('roles')
                    ->select('id', 'name')
                    ->where('tenant_id', $request->user()?->tenant_id)
                    ->where('status', 'Active')
                    ->orderBy('name')
                    ->get(),
            ];
        }

        if ($config['table'] === 'erp_modules') {
            return [
                'modules' => DB::table('erp_modules')
                    ->select('module_id', 'module_code', 'module_name')
                    ->whereNull('deleted_at')
                    ->orderBy('display_order')
                    ->orderBy('module_name')
                    ->get(),
            ];
        }

        return [];
    }

    private function payload(Request $request, array $config, bool $creating, int|string|null $currentId = null): array
    {
        $rules = [];
        $user = $request->user();

        foreach ($config['columns'] as $column) {
            $rules[$column] = [Rule::requiredIf($creating && in_array($column, $config['required'], true)), 'nullable'];
        }

        if ($config['table'] === 'erp_modules') {
            $rules['status'] = ['nullable', Rule::in(['Active', 'Inactive'])];
            $rules['parent_module_id'] = [
                'nullable',
                'integer',
                Rule::exists('erp_modules', 'module_id')->whereNull('deleted_at'),
            ];

            if ($currentId !== null) {
                $rules['parent_module_id'][] = Rule::notIn([(int) $currentId]);
            }
        }

        if (in_array('status', $config['columns'], true)) {
            $rules['status'] = ['nullable', Rule::in(['Active', 'Inactive'])];
        }

        if ($config['table'] === 'storage_location_master') {
            $rules['location_type'] = [Rule::requiredIf($creating), 'nullable', Rule::in(['RM', 'WIP', 'FG', 'WASTAGE', 'SCRAP'])];
        }

        if ($config['table'] === 'users') {
            $rules['password'] = [Rule::requiredIf($creating), 'nullable', 'string', 'min:6'];
            $rules['role_id'] = [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $user?->tenant_id)),
            ];
        }

        $data = $request->validate($rules);

        if ($config['table'] === 'party_master') {
            $data = $this->partyPayload($request, $data, $creating);
        }

        if ($config['table'] === 'item_master') {
            $data = $this->itemPayload($data);
        }

        if ($config['table'] === 'storage_location_master') {
            $data = $this->locationPayload($data);
        }

        if ($config['table'] === 'users') {
            $data = $this->userPayload($data);
        }

        if ($config['table'] === 'erp_modules') {
            $data['status'] = $data['status'] ?? 'Active';
            if (($data['parent_module_id'] ?? null) !== null && (string) $data['parent_module_id'] === (string) $currentId) {
                throw ValidationException::withMessages(['parent_module_id' => 'A module cannot be its own parent.']);
            }
        }

        foreach ($config['required'] as $column) {
            if ($creating && blank($data[$column] ?? null)) {
                throw ValidationException::withMessages([$column => "{$column} is required."]);
            }
        }

        if (($data['password'] ?? null) === null || $data['password'] === '') {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $data['password'] = Hash::make((string) $data['password']);
        }

        if ($user && $config['tenant_scope'] && Schema::hasColumn($config['table'], 'tenant_id')) {
            $data['tenant_id'] = $user->tenant_id;
        }

        if ($user && $config['branch_scope'] && Schema::hasColumn($config['table'], 'branch_id')) {
            $data['branch_id'] = $user->branch_id;
        }

        if ($creating && Schema::hasColumn($config['table'], 'created_by')) {
            $data['created_by'] = $user?->getKey();
        }

        if (Schema::hasColumn($config['table'], 'updated_by')) {
            $data['updated_by'] = $user?->getKey();
        }

        if ($creating && Schema::hasColumn($config['table'], 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn($config['table'], 'updated_at')) {
            $data['updated_at'] = now();
        }

        return $data;
    }

    private function partyPayload(Request $request, array $data, bool $creating): array
    {
        $data['party_type'] = $request->validate([
            'party_type' => ['required', Rule::in(['Customer', 'Supplier'])],
        ])['party_type'];
        $data['status'] = 'Active';
        unset($data['city'], $data['country'], $data['credit_days'], $data['credit_limit']);

        if ($creating) {
            $data['party_code'] = $this->nextPartyCodeForType($request, $data['party_type']);
        } else {
            unset($data['party_code']);
        }

        return $data;
    }

    private function itemPayload(array $data): array
    {
        $data['status'] = $data['status'] ?? 'Active';

        foreach (['minimum_stock', 'opening_qty', 'opening_rate'] as $column) {
            if (! array_key_exists($column, $data) || $data[$column] === null || $data[$column] === '') {
                $data[$column] = 0;
            }
        }

        foreach (['length_mm', 'width_mm', 'thickness_mm', 'cft_factor', 'material_type_id', 'uom_id'] as $column) {
            if (array_key_exists($column, $data) && $data[$column] === '') {
                $data[$column] = null;
            }
        }

        return $data;
    }

    private function locationPayload(array $data): array
    {
        if (array_key_exists('status', $data) && blank($data['status'])) {
            $data['status'] = 'Active';
        }

        return $data;
    }

    private function userPayload(array $data): array
    {
        if (array_key_exists('status', $data) && blank($data['status'])) {
            $data['status'] = 'Active';
        }

        if (array_key_exists('role_id', $data) && blank($data['role_id'])) {
            $data['role_id'] = null;
        }

        return $data;
    }

    private function applyItemFilters($query, Request $request): void
    {
        $query
            ->when($request->filled('item_type'), fn ($query) => $query->where('item_type', $request->query('item_type')))
            ->when($request->filled('material_type_id'), fn ($query) => $query->where('material_type_id', $request->query('material_type_id')))
            ->when($request->filled('uom_id'), fn ($query) => $query->where('uom_id', $request->query('uom_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('item_code'), fn ($query) => $query->where('item_code', 'like', '%'.$request->query('item_code').'%'))
            ->when($request->filled('item_name'), fn ($query) => $query->where('item_name', 'like', '%'.$request->query('item_name').'%'))
            ->when($request->filled('length_min'), fn ($query) => $query->where('length_mm', '>=', $request->query('length_min')))
            ->when($request->filled('width_min'), fn ($query) => $query->where('width_mm', '>=', $request->query('width_min')))
            ->when($request->filled('thickness_min'), fn ($query) => $query->where('thickness_mm', '>=', $request->query('thickness_min')))
            ->when($request->filled('minimum_stock_min'), fn ($query) => $query->where('minimum_stock', '>=', $request->query('minimum_stock_min')))
            ->when($request->filled('opening_qty_min'), fn ($query) => $query->where('opening_qty', '>=', $request->query('opening_qty_min')));
    }

    private function applyPartyFilters($query, Request $request): void
    {
        $query
            ->when($request->filled('party_type'), fn ($query) => $query->where('party_type', $request->query('party_type')))
            ->when($request->filled('state'), fn ($query) => $query->where('state', $request->query('state')))
            ->when($request->filled('party_code'), fn ($query) => $query->where('party_code', 'like', '%'.$request->query('party_code').'%'))
            ->when($request->filled('party_name'), fn ($query) => $query->where('party_name', 'like', '%'.$request->query('party_name').'%'))
            ->when($request->filled('contact_person'), fn ($query) => $query->where('contact_person', 'like', '%'.$request->query('contact_person').'%'))
            ->when($request->filled('mobile'), fn ($query) => $query->where('mobile', 'like', '%'.$request->query('mobile').'%'))
            ->when($request->filled('email'), fn ($query) => $query->where('email', 'like', '%'.$request->query('email').'%'))
            ->when($request->filled('gst_no'), fn ($query) => $query->where('gst_no', 'like', '%'.$request->query('gst_no').'%'))
            ->when($request->filled('pan_no'), fn ($query) => $query->where('pan_no', 'like', '%'.$request->query('pan_no').'%'));
    }

    private function applyGenericColumnFilters($query, Request $request, array $config): void
    {
        foreach (array_diff($config['columns'], ['password']) as $column) {
            if (! $request->filled($column)) {
                continue;
            }

            $value = $request->query($column);
            $exact = str_ends_with($column, '_id')
                || in_array($column, ['status', 'item_type', 'party_type', 'location_type'], true);

            $exact
                ? $query->where($column, $value)
                : $query->where($column, 'like', "%{$value}%");
        }
    }

    private function nextPartyCodeForType(Request $request, string $partyType): string
    {
        $prefix = $partyType === 'Customer' ? 'C' : 'S';
        $tenantId = $request->user()?->tenant_id;

        $lastCode = DB::table('party_master')
            ->where('tenant_id', $tenantId)
            ->where('party_type', $partyType)
            ->where('party_code', 'like', $prefix.'%')
            ->orderByDesc('party_code')
            ->value('party_code');

        $lastNumber = $lastCode ? (int) substr($lastCode, 1) : 0;

        return $prefix.str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
    }
}
