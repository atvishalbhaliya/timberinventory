<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('item_master') || Schema::hasColumn('item_master', 'category')) {
            return;
        }

        Schema::table('item_master', function (Blueprint $table): void {
            $table->string('category', 100)->nullable()->after('minimum_stock');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('item_master') || ! Schema::hasColumn('item_master', 'category')) {
            return;
        }

        Schema::table('item_master', function (Blueprint $table): void {
            $table->dropColumn('category');
        });
    }
};
