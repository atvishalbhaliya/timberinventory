<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grn_master', function (Blueprint $table): void {
            $table->string('purchase_order_ref', 80)->nullable()->after('vehicle_no');
            $table->foreignId('warehouse_location_id')->nullable()->after('purchase_order_ref')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            $table->string('received_by', 120)->nullable()->after('warehouse_location_id');
            $table->decimal('discount_amount', 18, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 18, 2)->default(0)->after('discount_amount');
            $table->decimal('freight_charges', 18, 2)->default(0)->after('tax_amount');
            $table->decimal('other_charges', 18, 2)->default(0)->after('freight_charges');
            $table->decimal('grand_total', 18, 2)->default(0)->after('other_charges');
            $table->json('attachments')->nullable()->after('grand_total');
        });

        Schema::table('grn_detail', function (Blueprint $table): void {
            $table->decimal('ordered_qty', 18, 3)->default(0)->after('location_id');
            $table->decimal('received_qty', 18, 3)->default(0)->after('ordered_qty');
            $table->decimal('rejected_qty', 18, 3)->default(0)->after('received_qty');
            $table->decimal('accepted_qty', 18, 3)->default(0)->after('rejected_qty');
            $table->decimal('discount_amount', 18, 2)->default(0)->after('rate');
            $table->decimal('tax_amount', 18, 2)->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('grn_detail', function (Blueprint $table): void {
            $table->dropColumn(['ordered_qty', 'received_qty', 'rejected_qty', 'accepted_qty', 'discount_amount', 'tax_amount']);
        });

        Schema::table('grn_master', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('warehouse_location_id');
            $table->dropColumn([
                'purchase_order_ref',
                'received_by',
                'discount_amount',
                'tax_amount',
                'freight_charges',
                'other_charges',
                'grand_total',
                'attachments',
            ]);
        });
    }
};
