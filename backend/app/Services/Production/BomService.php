<?php

namespace App\Services\Production;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BomService
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function paginate(Request $request)
    {
        $query = $this->baseQuery($request)
            ->leftJoin('item_master as finished_item', 'bom_master.finished_item_id', '=', 'finished_item.item_id')
            ->leftJoin('pallet_model_master', 'bom_master.pallet_model_id', '=', 'pallet_model_master.pallet_model_id')
            ->leftJoinSub(
                DB::table('bom_material')
                    ->select('bom_id', DB::raw('COUNT(*) as materials_count'))
                    ->groupBy('bom_id'),
                'bom_material_counts',
                'bom_master.bom_id',
                '=',
                'bom_material_counts.bom_id'
            )
            ->select('bom_master.*', DB::raw('COALESCE(finished_item.item_name, pallet_model_master.model_name) as model_name'), 'finished_item.item_name as finished_item_name', DB::raw('COALESCE(bom_material_counts.materials_count, 0) as materials_count'));

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('bom_master.bom_no', 'like', "%{$search}%")
                    ->orWhere('bom_master.bom_name', 'like', "%{$search}%")
                    ->orWhere('bom_master.version_no', 'like', "%{$search}%")
                    ->orWhere('finished_item.item_name', 'like', "%{$search}%")
                    ->orWhere('finished_item.item_code', 'like', "%{$search}%")
                    ->orWhere('pallet_model_master.model_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('bom_master.status', $request->query('status'));
        }

        if ($request->filled('bom_no')) {
            $query->where('bom_master.bom_no', 'like', '%'.trim((string) $request->query('bom_no')).'%');
        }

        if ($request->filled('bom_name')) {
            $query->where('bom_master.bom_name', 'like', '%'.trim((string) $request->query('bom_name')).'%');
        }

        if ($request->filled('finished_item_id')) {
            $query->where('bom_master.finished_item_id', $request->query('finished_item_id'));
        } elseif ($request->filled('pallet_model_id')) {
            $query->where('bom_master.pallet_model_id', $request->query('pallet_model_id'));
        }

        if ($request->filled('version_no')) {
            $query->where('bom_master.version_no', 'like', '%'.trim((string) $request->query('version_no')).'%');
        }

        if ($request->filled('materials_min')) {
            $query->havingRaw('COALESCE(bom_material_counts.materials_count, 0) >= ?', [(int) $request->query('materials_min')]);
        }

        $columns = [
            'bom_no' => 'bom_master.bom_no',
            'bom_name' => 'bom_master.bom_name',
            'model_name' => DB::raw('COALESCE(finished_item.item_name, pallet_model_master.model_name)'),
            'version_no' => 'bom_master.version_no',
            'status' => 'bom_master.status',
            'materials_count' => 'materials_count',
            'created_at' => 'bom_master.created_at',
        ];
        $sortBy = $columns[$request->query('sort_by')] ?? 'bom_master.bom_id';
        $direction = strtolower((string) $request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $direction)
            ->paginate((int) min(max((int) $request->query('per_page', 10), 1), 100));
    }

    public function find(Request $request, int $id): object
    {
        $bom = $this->baseQuery($request)
            ->leftJoin('item_master as finished_item', 'bom_master.finished_item_id', '=', 'finished_item.item_id')
            ->leftJoin('pallet_model_master', 'bom_master.pallet_model_id', '=', 'pallet_model_master.pallet_model_id')
            ->select('bom_master.*', DB::raw('COALESCE(finished_item.item_name, pallet_model_master.model_name) as model_name'), 'finished_item.item_name as finished_item_name')
            ->where('bom_master.bom_id', $id)
            ->firstOrFail();

        $bom->materials = $this->materials($id);

        return $bom;
    }

    public function create(Request $request, array $data): object
    {
        return DB::transaction(function () use ($request, $data): object {
            $payload = $this->payload($request, $data, true);
            $bomId = DB::table('bom_master')->insertGetId($payload['master'], 'bom_id');
            $this->replaceMaterials($bomId, $payload['materials']);
            $this->audit->record($request, 'bom_master', 'create', $bomId, null, $payload);

            return $this->find($request, $bomId);
        });
    }

    public function update(Request $request, int $id, array $data): object
    {
        return DB::transaction(function () use ($request, $id, $data): object {
            $existing = $this->scopedForUpdate($request, $id);
            $payload = $this->payload($request, $data, false);
            DB::table('bom_master')->where('bom_id', $id)->update($payload['master']);
            $this->replaceMaterials($id, $payload['materials']);
            $this->audit->record($request, 'bom_master', 'update', $id, $existing, $payload);

            return $this->find($request, $id);
        });
    }

    public function delete(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id): void {
            $bom = $this->scopedForUpdate($request, $id);

            if ((bool) $bom->system_protected) {
                throw ValidationException::withMessages(['bom' => 'System protected BOM cannot be deleted.']);
            }

            $used = DB::table('production_master')
                ->where('bom_id', $id)
                ->whereIn('status', ['Posted', 'Cancelled'])
                ->exists();

            if ($used) {
                throw ValidationException::withMessages(['bom' => 'BOM is referenced by a posted production entry.']);
            }

            DB::table('bom_material')->where('bom_id', $id)->delete();
            DB::table('bom_master')->where('bom_id', $id)->delete();
            $this->audit->record($request, 'bom_master', 'delete', $id, $bom);
        });
    }

    public function previewNextNumber(Request $request): string
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $request->query('branch_id'));
        if ($branchId <= 0) {
            $branchId = (int) DB::table('branch_master')->where('tenant_id', $user->tenant_id)->orderBy('branch_id')->value('branch_id');
        }

        return $this->nextBomNumber((int) $user->tenant_id, $branchId);
    }

    public function export(Request $request)
    {
        return $this->baseQuery($request)
            ->leftJoin('item_master as finished_item', 'bom_master.finished_item_id', '=', 'finished_item.item_id')
            ->leftJoin('pallet_model_master', 'bom_master.pallet_model_id', '=', 'pallet_model_master.pallet_model_id')
            ->select('bom_master.*', DB::raw('COALESCE(finished_item.item_name, pallet_model_master.model_name) as model_name'), 'finished_item.item_name as finished_item_name')
            ->orderBy('bom_master.bom_no')
            ->get();
    }

    private function baseQuery(Request $request)
    {
        $user = $request->user();

        return DB::table('bom_master')
            ->where('bom_master.tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($query) => $query->where('bom_master.branch_id', $user->branch_id));
    }

    private function scopedForUpdate(Request $request, int $id): object
    {
        return $this->baseQuery($request)->where('bom_id', $id)->lockForUpdate()->firstOrFail();
    }

    private function payload(Request $request, array $data, bool $creating): array
    {
        $user = $request->user();
        $branchId = (int) ($user->branch_id ?: $data['branch_id']);
        $bomNo = $data['bom_no'] ?? $this->nextBomNumber((int) $user->tenant_id, $branchId);

        $materials = collect($data['materials'])->map(function (array $material) use ($user, $branchId): array {
            return [
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branchId,
                'item_id' => (int) $material['item_id'],
                'uom_id' => (int) $material['uom_id'],
                'required_qty' => (float) $material['required_qty'],
                'wastage_percent' => (float) ($material['wastage_percent'] ?? 0),
                'remarks' => $material['remarks'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->values();

        $master = [
            'tenant_id' => $user->tenant_id,
            'branch_id' => $branchId,
            'bom_no' => $bomNo,
            'bom_name' => $data['bom_name'],
            'finished_item_id' => (int) $data['finished_item_id'],
            'pallet_model_id' => $data['pallet_model_id'] ?? null,
            'version_no' => $data['version_no'],
            'is_active' => ($data['status'] ?? 'Active') === 'Active',
            'status' => $data['status'] ?? 'Active',
            'revision_note' => $data['revision_note'] ?? null,
            'updated_by' => $user->id,
            'updated_at' => now(),
        ];

        if ($creating) {
            $master['created_by'] = $user->id;
            $master['created_at'] = now();
        }

        return ['master' => $master, 'materials' => $materials->all()];
    }

    private function replaceMaterials(int $bomId, array $materials): void
    {
        DB::table('bom_material')->where('bom_id', $bomId)->delete();

        foreach ($materials as $material) {
            $material['bom_id'] = $bomId;
            DB::table('bom_material')->insert($material);
        }
    }

    private function materials(int $bomId)
    {
        return DB::table('bom_material')
            ->leftJoin('item_master', 'bom_material.item_id', '=', 'item_master.item_id')
            ->leftJoin('uom_master', 'bom_material.uom_id', '=', 'uom_master.uom_id')
            ->where('bom_material.bom_id', $bomId)
            ->orderBy('bom_material.bom_material_id')
            ->select('bom_material.*', 'item_master.item_name', 'uom_master.uom_name')
            ->get();
    }

    private function nextBomNumber(int $tenantId, int $branchId): string
    {
        $next = DB::table('bom_master')->where('tenant_id', $tenantId)->where('branch_id', $branchId)->lockForUpdate()->count() + 1;

        return 'BOM-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
