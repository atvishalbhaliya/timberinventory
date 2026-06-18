<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_master', function (Blueprint $table): void {
            $table->id('tenant_id');
            $table->string('tenant_code', 20)->unique()->nullable();
            $table->string('tenant_name');
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $this->auditColumns($table, false, false);
        });

        Schema::create('branch_master', function (Blueprint $table): void {
            $table->id('branch_id');
            $table->foreignId('tenant_id')->constrained('tenant_master', 'tenant_id')->cascadeOnDelete();
            $table->string('branch_code', 20)->nullable();
            $table->string('branch_name')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $this->auditColumns($table);
        });

        Schema::create('role_master', function (Blueprint $table): void {
            $table->id('role_id');
            $this->tenantBranchColumns($table, false, false);
            $table->string('role_name', 100);
            $table->string('guard_name', 50)->default('api');
            $this->auditColumns($table);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $this->tenantBranchColumns($table, false, false);
            $table->string('name', 100);
            $table->string('guard_name', 50)->default('api');
            $this->auditColumns($table);
            $table->unique(['tenant_id', 'name'], 'roles_tenant_name_unique');
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('guard_name', 50)->default('api');
            $this->auditColumns($table, false, false);
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $this->auditColumns($table, false, false);
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_master', function (Blueprint $table): void {
            $table->id('user_id');
            $this->tenantBranchColumns($table, false, false);
            $table->foreignId('role_id')->nullable()->constrained('role_master', 'role_id')->nullOnDelete();
            $table->string('login_id', 100);
            $table->string('password');
            $table->string('employee_code', 50)->nullable();
            $table->string('full_name')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $this->auditColumns($table);
            $table->unique(['tenant_id', 'login_id'], 'user_master_tenant_login_unique');
        });

        Schema::create('party_master', function (Blueprint $table): void {
            $table->id('party_id');
            $this->tenantBranchColumns($table);
            $table->string('party_code', 30)->nullable();
            $table->string('party_name');
            $table->enum('party_type', ['Customer', 'Supplier', 'Both']);
            $table->string('gst_no', 30)->nullable();
            $table->string('pan_no', 20)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->unsignedInteger('credit_days')->default(0);
            $table->decimal('credit_limit', 18, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $this->auditColumns($table);
        });

        Schema::create('material_type_master', function (Blueprint $table): void {
            $table->id('material_type_id');
            $this->tenantBranchColumns($table, true, false);
            $table->string('material_type_code', 20)->nullable();
            $table->string('material_type_name', 100);
            $this->auditColumns($table);
        });

        Schema::create('uom_master', function (Blueprint $table): void {
            $table->id('uom_id');
            $this->tenantBranchColumns($table, true, false);
            $table->string('uom_code', 20)->nullable();
            $table->string('uom_name', 50);
            $this->auditColumns($table);
        });

        Schema::create('item_master', function (Blueprint $table): void {
            $table->id('item_id');
            $this->tenantBranchColumns($table, true, false);
            $table->string('item_code', 50)->nullable();
            $table->string('item_name');
            $table->enum('item_type', ['Raw Material', 'Semi Product', 'Finish Product', 'Wastage', 'Scrap', 'Consumable']);
            $table->foreignId('material_type_id')->nullable()->constrained('material_type_master', 'material_type_id')->nullOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('uom_master', 'uom_id')->nullOnDelete();
            $table->decimal('length_mm', 18, 3)->nullable();
            $table->decimal('width_mm', 18, 3)->nullable();
            $table->decimal('thickness_mm', 18, 3)->nullable();
            $table->decimal('cft_factor', 18, 6)->nullable();
            $table->decimal('minimum_stock', 18, 3)->default(0);
            $table->string('category', 100)->nullable()->after('minimum_stock');
            $table->decimal('opening_qty', 18, 3)->default(0);
            $table->decimal('opening_rate', 18, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $this->auditColumns($table);
        });

        Schema::create('storage_location_master', function (Blueprint $table): void {
            $table->id('location_id');
            $this->tenantBranchColumns($table);
            $table->string('location_code', 30)->nullable();
            $table->string('location_name', 100);
            $table->enum('location_type', ['RM', 'WIP', 'FG', 'WASTAGE', 'SCRAP']);
            $this->auditColumns($table);
        });

        Schema::create('team_master', function (Blueprint $table): void {
            $table->id('team_id');
            $this->tenantBranchColumns($table);
            $table->string('team_code', 30)->nullable();
            $table->string('team_name', 100);
            $table->string('contractor_name')->nullable();
            $table->decimal('rate_per_pallet', 18, 2)->default(0);
            $table->decimal('tds_percent', 5, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $this->auditColumns($table);
        });

        Schema::create('pallet_model_master', function (Blueprint $table): void {
            $table->id('pallet_model_id');
            $this->tenantBranchColumns($table, true, false);
            $table->string('model_code', 50)->nullable();
            $table->string('model_name');
            $table->decimal('length', 18, 3)->nullable();
            $table->decimal('width', 18, 3)->nullable();
            $table->decimal('height', 18, 3)->nullable();
            $table->string('wood_type', 100)->nullable();
            $this->auditColumns($table);
        });

        Schema::create('bom_master', function (Blueprint $table): void {
            $table->id('bom_id');
            $this->tenantBranchColumns($table, true, false);
            $table->foreignId('pallet_model_id')->constrained('pallet_model_master', 'pallet_model_id')->cascadeOnDelete();
            $table->string('version_no', 20);
            $table->boolean('is_active')->default(false);
            $table->text('revision_note')->nullable();
            $this->auditColumns($table);
        });

        Schema::create('bom_material', function (Blueprint $table): void {
            $table->id('bom_material_id');
            $this->tenantBranchColumns($table, true, false);
            $table->foreignId('bom_id')->constrained('bom_master', 'bom_id')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->decimal('required_qty', 18, 3);
            $table->decimal('wastage_percent', 8, 2)->default(0);
            $this->auditColumns($table);
        });

        Schema::create('grn_master', function (Blueprint $table): void {
            $table->id('grn_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('supplier_id')->nullable()->constrained('party_master', 'party_id')->nullOnDelete();
            $table->string('grn_no', 50);
            $table->date('grn_date');
            $table->string('invoice_no', 50)->nullable();
            $table->string('vehicle_no', 50)->nullable();
            $this->auditColumns($table);
        });

        Schema::create('grn_detail', function (Blueprint $table): void {
            $table->id('grn_detail_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('grn_id')->constrained('grn_master', 'grn_id')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('storage_location_master', 'location_id')->restrictOnDelete();
            $table->decimal('qty', 18, 3);
            $table->decimal('rate', 18, 2);
            $table->decimal('amount', 18, 2)->default(0);
            $this->auditColumns($table);
        });

        Schema::create('stock_ledger', function (Blueprint $table): void {
            $table->id('ledger_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('storage_location_master', 'location_id')->restrictOnDelete();
            $table->dateTime('transaction_date');
            $table->string('transaction_type', 50);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->decimal('qty_in', 18, 3)->default(0);
            $table->decimal('qty_out', 18, 3)->default(0);
            $table->decimal('balance_qty', 18, 3)->default(0);
            $this->auditColumns($table);
        });

        Schema::create('stock_summary', function (Blueprint $table): void {
            $table->id('stock_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('storage_location_master', 'location_id')->restrictOnDelete();
            $table->decimal('stock_qty', 18, 3)->default(0);
            $table->decimal('avg_rate', 18, 2)->default(0);
            $this->auditColumns($table);
            $table->unique(['tenant_id', 'branch_id', 'item_id', 'location_id'], 'stock_summary_unique');
        });

        Schema::create('production_master', function (Blueprint $table): void {
            $table->id('production_id');
            $this->tenantBranchColumns($table);
            $table->string('production_no', 50);
            $table->date('production_date');
            $table->foreignId('pallet_model_id')->constrained('pallet_model_master', 'pallet_model_id')->restrictOnDelete();
            $table->foreignId('team_id')->constrained('team_master', 'team_id')->restrictOnDelete();
            $table->decimal('produced_qty', 18, 3);
            $table->decimal('production_cost', 18, 2)->default(0);
            $this->auditColumns($table);
        });

        foreach (['production_consumption' => 'consumption_id', 'production_output' => 'output_id', 'production_wastage' => 'wastage_id'] as $tableName => $idColumn) {
            Schema::create($tableName, function (Blueprint $table) use ($idColumn): void {
                $table->id($idColumn);
                $this->tenantBranchColumns($table);
                $table->foreignId('production_id')->constrained('production_master', 'production_id')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
                $table->decimal($idColumn === 'consumption_id' ? 'consumed_qty' : 'qty', 18, 3);
                $this->auditColumns($table);
            });
        }

        Schema::create('wastage_stock', function (Blueprint $table): void {
            $table->id('wastage_stock_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->enum('wastage_type', ['Reusable', 'Non-Reusable', 'Scrap']);
            $table->decimal('generated_qty', 18, 3)->default(0);
            $table->decimal('available_qty', 18, 3)->default(0);
            $table->decimal('used_qty', 18, 3)->default(0);
            $table->decimal('balance_qty', 18, 3)->default(0);
            $this->auditColumns($table);
        });

        Schema::create('team_ledger', function (Blueprint $table): void {
            $table->id('ledger_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('team_id')->constrained('team_master', 'team_id')->cascadeOnDelete();
            $table->foreignId('pallet_model_id')->nullable()->constrained('pallet_model_master', 'pallet_model_id')->nullOnDelete();
            $table->enum('transaction_type', ['Production', 'Dispatch']);
            $table->date('transaction_date');
            $table->decimal('qty', 18, 3);
            $this->auditColumns($table);
        });

        Schema::create('challan_master', function (Blueprint $table): void {
            $table->id('challan_id');
            $this->tenantBranchColumns($table);
            $table->string('challan_no', 50);
            $table->date('challan_date');
            $table->foreignId('customer_id')->nullable()->constrained('party_master', 'party_id')->nullOnDelete();
            $table->string('vehicle_no', 50)->nullable();
            $table->string('driver_name')->nullable();
            $table->string('destination')->nullable();
            $table->decimal('total_qty', 18, 3)->default(0);
            $this->auditColumns($table);
        });

        Schema::create('challan_team_detail', function (Blueprint $table): void {
            $table->id('detail_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('challan_id')->constrained('challan_master', 'challan_id')->cascadeOnDelete();
            $table->foreignId('pallet_model_id')->nullable()->constrained('pallet_model_master', 'pallet_model_id')->nullOnDelete();
            $table->foreignId('team_id')->constrained('team_master', 'team_id')->restrictOnDelete();
            $table->decimal('qty', 18, 3);
            $this->auditColumns($table);
        });

        Schema::create('team_payment_summary', function (Blueprint $table): void {
            $table->id('payment_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('team_id')->constrained('team_master', 'team_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('payment_month');
            $table->unsignedSmallInteger('payment_year');
            $table->decimal('dispatch_qty', 18, 3)->default(0);
            $table->decimal('gross_amount', 18, 2)->default(0);
            $table->decimal('tds_amount', 18, 2)->default(0);
            $table->decimal('net_payable', 18, 2)->default(0);
            $this->auditColumns($table);
        });

        Schema::create('stock_verification_master', function (Blueprint $table): void {
            $table->id('verification_id');
            $this->tenantBranchColumns($table);
            $table->string('verification_no', 50);
            $table->date('verification_date');
            $this->auditColumns($table);
        });

        Schema::create('stock_verification_detail', function (Blueprint $table): void {
            $table->id('detail_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('verification_id')->constrained('stock_verification_master', 'verification_id')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->decimal('system_qty', 18, 3)->default(0);
            $table->decimal('physical_qty', 18, 3)->default(0);
            $table->decimal('variance_qty', 18, 3)->default(0);
            $this->auditColumns($table);
        });

        Schema::create('stock_adjustment_master', function (Blueprint $table): void {
            $table->id('adjustment_id');
            $this->tenantBranchColumns($table);
            $table->date('adjustment_date');
            $this->auditColumns($table);
        });

        Schema::create('stock_adjustment_detail', function (Blueprint $table): void {
            $table->id('detail_id');
            $this->tenantBranchColumns($table);
            $table->foreignId('adjustment_id')->constrained('stock_adjustment_master', 'adjustment_id')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master', 'item_id')->restrictOnDelete();
            $table->decimal('adjustment_qty', 18, 3);
            $this->auditColumns($table);
        });

        Schema::create('audit_log', function (Blueprint $table): void {
            $table->id('audit_id');
            $this->tenantBranchColumns($table);
            $table->string('table_name', 100);
            $table->string('action_type', 50);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('action_time');
            $this->auditColumns($table);
        });
    }

    public function down(): void
    {
        foreach (array_reverse([
            'tenant_master',
            'branch_master',
            'role_master',
            'roles',
            'permissions',
            'role_permissions',
            'user_master',
            'party_master',
            'material_type_master',
            'uom_master',
            'item_master',
            'storage_location_master',
            'team_master',
            'pallet_model_master',
            'bom_master',
            'bom_material',
            'grn_master',
            'grn_detail',
            'stock_ledger',
            'stock_summary',
            'production_master',
            'production_consumption',
            'production_output',
            'production_wastage',
            'wastage_stock',
            'team_ledger',
            'challan_master',
            'challan_team_detail',
            'team_payment_summary',
            'stock_verification_master',
            'stock_verification_detail',
            'stock_adjustment_master',
            'stock_adjustment_detail',
            'audit_log',
        ]) as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function tenantBranchColumns(Blueprint $table, bool $tenantRequired = true, bool $branchRequired = true): void
    {
        $tenant = $table->foreignId('tenant_id');
        if (! $tenantRequired) {
            $tenant->nullable();
        }
        $tenantRequired
            ? $tenant->constrained('tenant_master', 'tenant_id')->cascadeOnDelete()
            : $tenant->constrained('tenant_master', 'tenant_id')->nullOnDelete();

        $branch = $table->foreignId('branch_id');
        if (! $branchRequired) {
            $branch->nullable();
        }
        $branchRequired
            ? $branch->constrained('branch_master', 'branch_id')->cascadeOnDelete()
            : $branch->constrained('branch_master', 'branch_id')->nullOnDelete();
    }

    private function auditColumns(Blueprint $table, bool $withCreatedBy = true, bool $withUpdatedBy = true): void
    {
        if ($withCreatedBy) {
            $table->unsignedBigInteger('created_by')->nullable();
        }

        if ($withUpdatedBy) {
            $table->unsignedBigInteger('updated_by')->nullable();
        }

        $table->timestamps();
        $table->softDeletes();
    }
};
