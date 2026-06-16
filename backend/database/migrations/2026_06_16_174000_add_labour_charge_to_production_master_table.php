<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_master', function (Blueprint $table): void {
            $table->decimal('labour_charge', 15, 2)
                ->default(0)
                ->after('production_cost');
        });
    }

    public function down(): void
    {
        Schema::table('production_master', function (Blueprint $table): void {
            $table->dropColumn('labour_charge');
        });
    }
};
