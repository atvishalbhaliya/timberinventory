<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_location_master', function (Blueprint $table): void {
            if (! Schema::hasColumn('storage_location_master', 'status')) {
                $table->string('status', 20)->default('Active')->after('location_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('storage_location_master', function (Blueprint $table): void {
            if (Schema::hasColumn('storage_location_master', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
