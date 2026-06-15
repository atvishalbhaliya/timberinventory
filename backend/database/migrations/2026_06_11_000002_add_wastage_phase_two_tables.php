<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wastage_stock', function (Blueprint $table): void {
            if (! Schema::hasColumn('wastage_stock', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('item_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            }
            if (! Schema::hasColumn('wastage_stock', 'source_module')) {
                $table->string('source_module', 50)->default('Manual')->after('wastage_type');
            }
            if (! Schema::hasColumn('wastage_stock', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_module');
            }
            if (! Schema::hasColumn('wastage_stock', 'source_reference')) {
                $table->string('source_reference', 100)->nullable()->after('source_id');
            }
            if (! Schema::hasColumn('wastage_stock', 'transaction_date')) {
                $table->date('transaction_date')->nullable()->after('source_reference');
            }
            if (! Schema::hasColumn('wastage_stock', 'status')) {
                $table->string('status', 20)->default('Posted')->after('balance_qty');
            }
            if (! Schema::hasColumn('wastage_stock', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('wastage_stock', 'posted_by')) {
                $table->unsignedBigInteger('posted_by')->nullable()->after('posted_at');
            }
            if (! Schema::hasColumn('wastage_stock', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('posted_by');
            }
            if (! Schema::hasColumn('wastage_stock', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('wastage_stock', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            }
            if (! Schema::hasColumn('wastage_stock', 'remarks')) {
                $table->text('remarks')->nullable()->after('cancellation_reason');
            }
        });

        Schema::create('wastage_reuse_master', function (Blueprint $table): void {
            $table->id('reuse_id');
            $table->foreignId('tenant_id')->constrained('tenant_master', 'tenant_id')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branch_master', 'branch_id')->cascadeOnDelete();
            $table->string('reuse_no', 50);
            $table->date('reuse_date');
            $table->foreignId('source_wastage_stock_id')->constrained('wastage_stock', 'wastage_stock_id')->restrictOnDelete();
            $table->foreignId('source_item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->foreignId('source_location_id')->constrained('storage_location_master', 'location_id')->restrictOnDelete();
            $table->decimal('consumed_qty', 18, 3);
            $table->foreignId('produced_item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->foreignId('destination_location_id')->constrained('storage_location_master', 'location_id')->restrictOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('team_master', 'team_id')->nullOnDelete();
            $table->decimal('produced_qty', 18, 3);
            $table->decimal('production_cost', 18, 2)->default(0);
            $table->string('status', 20)->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'branch_id', 'reuse_no'], 'wastage_reuse_tenant_branch_no_unique');
        });

        foreach ([
            'wastage.view',
            'wastage.manage',
            'wastage.post',
            'wastage.cancel',
            'wastage-reuse.view',
            'wastage-reuse.manage',
            'wastage-reuse.post',
            'wastage-reuse.cancel',
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission],
                ['guard_name' => 'api', 'deleted_at' => null, 'created_at' => now(), 'updated_at' => now()],
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['wastage.view', 'wastage.manage', 'wastage.post', 'wastage.cancel', 'wastage-reuse.view', 'wastage-reuse.manage', 'wastage-reuse.post', 'wastage-reuse.cancel'])
            ->pluck('id');
        $roleIds = DB::table('roles')->whereIn('name', ['Super Admin', 'Admin', 'Production', 'Store'])->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wastage_reuse_master');

        Schema::table('wastage_stock', function (Blueprint $table): void {
            foreach (['location_id'] as $column) {
                if (Schema::hasColumn('wastage_stock', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            $columns = ['source_module', 'source_id', 'source_reference', 'transaction_date', 'status', 'posted_at', 'posted_by', 'cancelled_at', 'cancelled_by', 'cancellation_reason', 'remarks'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('wastage_stock', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
