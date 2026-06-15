<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_master', function (Blueprint $table): void {
            $table->string('bom_no', 50)->nullable()->after('branch_id');
            $table->string('bom_name')->nullable()->after('bom_no');
            $table->string('status', 20)->default('Active')->after('is_active');
            $table->boolean('system_protected')->default(false)->after('status');
            $table->unique(['tenant_id', 'branch_id', 'bom_no'], 'bom_tenant_branch_no_unique');
            $table->unique(['tenant_id', 'branch_id', 'pallet_model_id', 'version_no'], 'bom_tenant_model_version_unique');
        });

        Schema::table('bom_material', function (Blueprint $table): void {
            $table->foreignId('uom_id')->nullable()->after('item_id')->constrained('uom_master', 'uom_id')->nullOnDelete();
            $table->text('remarks')->nullable()->after('wastage_percent');
        });

        Schema::table('production_master', function (Blueprint $table): void {
            $table->foreignId('bom_id')->nullable()->after('production_date')->constrained('bom_master', 'bom_id')->nullOnDelete();
            $table->foreignId('produced_item_id')->nullable()->after('pallet_model_id')->constrained('item_master', 'item_id')->nullOnDelete();
            $table->foreignId('fg_location_id')->nullable()->after('team_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->string('status', 20)->default('Draft')->after('production_cost');
            $table->text('remarks')->nullable()->after('status');
            $table->timestamp('posted_at')->nullable()->after('remarks');
            $table->unsignedBigInteger('posted_by')->nullable()->after('posted_at');
            $table->timestamp('cancelled_at')->nullable()->after('posted_by');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            $table->unique(['tenant_id', 'branch_id', 'production_no'], 'production_tenant_branch_no_unique');
        });

        Schema::table('production_consumption', function (Blueprint $table): void {
            $table->foreignId('uom_id')->nullable()->after('item_id')->constrained('uom_master', 'uom_id')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->after('uom_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->decimal('required_qty', 18, 3)->default(0)->after('location_id');
            $table->text('remarks')->nullable()->after('consumed_qty');
        });

        Schema::table('production_output', function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable()->after('item_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->text('remarks')->nullable()->after('qty');
        });

        Schema::table('production_wastage', function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable()->after('item_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->string('wastage_type', 30)->default('Scrap')->after('qty');
            $table->text('remarks')->nullable()->after('wastage_type');
        });
    }

    public function down(): void
    {
        Schema::table('production_wastage', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn(['wastage_type', 'remarks']);
        });

        Schema::table('production_output', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn('remarks');
        });

        Schema::table('production_consumption', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('uom_id');
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn(['required_qty', 'remarks']);
        });

        Schema::table('production_master', function (Blueprint $table): void {
            $table->dropUnique('production_tenant_branch_no_unique');
            $table->dropConstrainedForeignId('bom_id');
            $table->dropConstrainedForeignId('produced_item_id');
            $table->dropConstrainedForeignId('fg_location_id');
            $table->dropColumn([
                'status',
                'remarks',
                'posted_at',
                'posted_by',
                'cancelled_at',
                'cancelled_by',
                'cancellation_reason',
            ]);
        });

        Schema::table('bom_material', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('uom_id');
            $table->dropColumn('remarks');
        });

        Schema::table('bom_master', function (Blueprint $table): void {
            $table->dropUnique('bom_tenant_branch_no_unique');
            $table->dropUnique('bom_tenant_model_version_unique');
            $table->dropColumn(['bom_no', 'bom_name', 'status', 'system_protected']);
        });
    }
};
