<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->decimal('labour_charge', 15, 2)
                  ->default(0)
                  ->after('production_rate');
        });
    }

    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropColumn('labour_charge');
        });
    }
};