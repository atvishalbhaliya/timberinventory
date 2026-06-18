<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE stock_summary DROP INDEX stock_summary_unique, ADD UNIQUE KEY stock_summary_unique (tenant_id, branch_id, item_id, location_id, stock_type)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE stock_summary DROP INDEX stock_summary_unique, ADD UNIQUE KEY stock_summary_unique (tenant_id, branch_id, item_id, location_id)');
    }
};
