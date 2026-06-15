<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stock_ledger', 'stock_type')) {
            Schema::table('stock_ledger', function (Blueprint $table): void {
                $table->string('stock_type', 30)->default('Fresh')->after('location_id');
            });
        }

        if (! Schema::hasColumn('stock_summary', 'stock_type')) {
            Schema::table('stock_summary', function (Blueprint $table): void {
                $table->string('stock_type', 30)->default('Fresh')->after('location_id');
            });
        }

        DB::table('stock_ledger')->whereNull('stock_type')->orWhere('stock_type', '')->update(['stock_type' => 'Fresh']);
        DB::table('stock_summary')->whereNull('stock_type')->orWhere('stock_type', '')->update(['stock_type' => 'Fresh']);

        if (Schema::hasColumn('production_consumption', 'consumed_qty') && ! Schema::hasColumn('production_consumption', 'wastage_qty')) {
            Schema::table('production_consumption', function (Blueprint $table): void {
                $table->decimal('wastage_qty', 18, 3)->default(0)->after('consumed_qty');
            });
        }

        // Keep the existing item/location uniqueness. Current flows always use Fresh stock.
    }

    public function down(): void
    {
        if (Schema::hasColumn('production_consumption', 'wastage_qty')) {
            Schema::table('production_consumption', function (Blueprint $table): void {
                $table->dropColumn('wastage_qty');
            });
        }

        if (Schema::hasColumn('stock_summary', 'stock_type')) {
            Schema::table('stock_summary', function (Blueprint $table): void {
                $table->dropColumn('stock_type');
            });
        }

        if (Schema::hasColumn('stock_ledger', 'stock_type')) {
            Schema::table('stock_ledger', function (Blueprint $table): void {
                $table->dropColumn('stock_type');
            });
        }
    }
};
