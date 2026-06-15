<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wastage_stock') || ! Schema::hasTable('storage_location_master')) {
            return;
        }

        DB::table('wastage_stock')
            ->whereNull('location_id')
            ->orderBy('wastage_stock_id')
            ->select('wastage_stock_id', 'tenant_id', 'branch_id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $locationId = DB::table('storage_location_master')
                        ->where('tenant_id', $row->tenant_id)
                        ->where('branch_id', $row->branch_id)
                        ->where(function ($query): void {
                            $query->where('location_type', 'WASTAGE')
                                ->orWhere('location_code', 'WASTAGE')
                                ->orWhere('location_code', 'WST');
                        })
                        ->orderBy('location_id')
                        ->value('location_id');

                    $locationId ??= DB::table('storage_location_master')
                        ->where('tenant_id', $row->tenant_id)
                        ->where('branch_id', $row->branch_id)
                        ->orderBy('location_id')
                        ->value('location_id');

                    if ($locationId) {
                        DB::table('wastage_stock')
                            ->where('wastage_stock_id', $row->wastage_stock_id)
                            ->update([
                                'location_id' => $locationId,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }, 'wastage_stock_id');
    }

    public function down(): void
    {
        // Data repair migration; keep inferred locations in place on rollback.
    }
};
