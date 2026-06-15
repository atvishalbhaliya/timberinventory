<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challan_master', function (Blueprint $table): void {
            if (! Schema::hasColumn('challan_master', 'source_location_id')) {
                $table->foreignId('source_location_id')->nullable()->after('customer_id')->constrained('storage_location_master', 'location_id')->nullOnDelete();
            }

            if (! Schema::hasColumn('challan_master', 'status')) {
                $table->string('status', 20)->default('Draft')->after('total_qty');
            }

            if (! Schema::hasColumn('challan_master', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }

            if (! Schema::hasColumn('challan_master', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('remarks');
            }

            if (! Schema::hasColumn('challan_master', 'posted_by')) {
                $table->unsignedBigInteger('posted_by')->nullable()->after('posted_at');
            }

            if (! Schema::hasColumn('challan_master', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('posted_by');
            }

            if (! Schema::hasColumn('challan_master', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            }

            if (! Schema::hasColumn('challan_master', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('challan_master', function (Blueprint $table): void {
            foreach (['source_location_id', 'status', 'remarks', 'posted_at', 'posted_by', 'cancelled_at', 'cancelled_by', 'cancellation_reason'] as $column) {
                if (Schema::hasColumn('challan_master', $column)) {
                    if ($column === 'source_location_id') {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
