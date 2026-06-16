<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table): void {
            $table->foreignId('team_id')
                ->nullable()
                ->after('location_id')
                ->constrained('team_master', 'team_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('team_id');
        });
    }
};
