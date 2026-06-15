<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_verification_master', function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable()->after('branch_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->string('status', 20)->default('Draft')->after('verification_date');
            $table->text('remarks')->nullable()->after('status');
            $table->timestamp('submitted_at')->nullable()->after('remarks');
            $table->unsignedBigInteger('submitted_by')->nullable()->after('submitted_at');
            $table->timestamp('approved_at')->nullable()->after('submitted_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            $table->timestamp('cancelled_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->unique(['tenant_id', 'branch_id', 'verification_no'], 'stock_verification_no_unique');
        });

        Schema::table('stock_verification_detail', function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable()->after('item_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->foreignId('uom_id')->nullable()->after('location_id')->constrained('uom_master', 'uom_id')->nullOnDelete();
            $table->string('variance_type', 20)->default('Matched')->after('variance_qty');
        });

        Schema::table('stock_adjustment_master', function (Blueprint $table): void {
            $table->string('adjustment_no', 50)->nullable()->after('branch_id');
            $table->unsignedBigInteger('verification_id')->nullable()->after('adjustment_date');
            $table->string('reference_type', 50)->nullable()->after('verification_id');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->text('remarks')->nullable()->after('reference_id');
        });

        Schema::table('stock_adjustment_detail', function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable()->after('item_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->string('adjustment_type', 20)->default('Excess')->after('adjustment_qty');
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustment_detail', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn('adjustment_type');
        });

        Schema::table('stock_adjustment_master', function (Blueprint $table): void {
            $table->dropColumn(['adjustment_no', 'verification_id', 'reference_type', 'reference_id', 'remarks']);
        });

        Schema::table('stock_verification_detail', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('location_id');
            $table->dropConstrainedForeignId('uom_id');
            $table->dropColumn('variance_type');
        });

        Schema::table('stock_verification_master', function (Blueprint $table): void {
            $table->dropUnique('stock_verification_no_unique');
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn(['status', 'remarks', 'submitted_at', 'submitted_by', 'approved_at', 'approved_by', 'cancelled_at', 'cancelled_by']);
        });
    }
};
