<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challan_team_detail', function (Blueprint $table): void {
            if (! Schema::hasColumn('challan_team_detail', 'item_id')) {
                $table->foreignId('item_id')->nullable()->after('challan_id')->constrained('item_master', 'item_id')->nullOnDelete();
            }

            if (! Schema::hasColumn('challan_team_detail', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('item_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('challan_team_detail', function (Blueprint $table): void {
            foreach (['location_id', 'item_id'] as $column) {
                if (Schema::hasColumn('challan_team_detail', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
