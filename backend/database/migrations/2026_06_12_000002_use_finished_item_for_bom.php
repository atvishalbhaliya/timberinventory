<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bom_master', 'finished_item_id')) {
            Schema::table('bom_master', function (Blueprint $table): void {
                $table->foreignId('finished_item_id')->nullable()->after('pallet_model_id')->constrained('item_master', 'item_id')->nullOnDelete();
            });
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE bom_master MODIFY pallet_model_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE production_master MODIFY pallet_model_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bom_master', 'finished_item_id')) {
            Schema::table('bom_master', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('finished_item_id');
            });
        }
    }
};
