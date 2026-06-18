<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challan_team_detail', function (Blueprint $table): void {
            if (! Schema::hasColumn('challan_team_detail', 'labour_rate')) {
                $table->decimal('labour_rate', 18, 2)->default(0)->after('qty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('challan_team_detail', function (Blueprint $table): void {
            if (Schema::hasColumn('challan_team_detail', 'labour_rate')) {
                $table->dropColumn('labour_rate');
            }
        });
    }
};
