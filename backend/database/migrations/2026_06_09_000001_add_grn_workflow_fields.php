<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grn_master', function (Blueprint $table): void {
            $table->string('status', 20)->default('Draft')->after('vehicle_no');
            $table->text('remarks')->nullable()->after('status');
            $table->decimal('total_qty', 18, 3)->default(0)->after('remarks');
            $table->decimal('total_amount', 18, 2)->default(0)->after('total_qty');
            $table->timestamp('posted_at')->nullable()->after('total_amount');
            $table->unsignedBigInteger('posted_by')->nullable()->after('posted_at');
            $table->timestamp('cancelled_at')->nullable()->after('posted_by');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->unique(['tenant_id', 'branch_id', 'grn_no'], 'grn_tenant_branch_no_unique');
        });

        Schema::table('grn_detail', function (Blueprint $table): void {
            $table->foreignId('uom_id')->nullable()->after('item_id')->constrained('uom_master', 'uom_id')->nullOnDelete();
        });

        Schema::table('stock_ledger', function (Blueprint $table): void {
            $table->decimal('rate', 18, 2)->default(0)->after('balance_qty');
            $table->decimal('amount', 18, 2)->default(0)->after('rate');
        });
    }

    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table): void {
            $table->dropColumn(['rate', 'amount']);
        });

        Schema::table('grn_detail', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('uom_id');
        });

        Schema::table('grn_master', function (Blueprint $table): void {
            $table->dropUnique('grn_tenant_branch_no_unique');
            $table->dropColumn([
                'status',
                'remarks',
                'total_qty',
                'total_amount',
                'posted_at',
                'posted_by',
                'cancelled_at',
                'cancelled_by',
            ]);
        });
    }
};
