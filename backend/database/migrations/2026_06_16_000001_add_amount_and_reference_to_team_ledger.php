<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {//git 
        Schema::table('team_ledger', function (Blueprint $table): void {
            if (! Schema::hasColumn('team_ledger', 'amount')) {
                $table->decimal('amount', 18, 2)->default(0)->after('qty');
            }

            if (! Schema::hasColumn('team_ledger', 'reference_type')) {
                $table->string('reference_type', 100)->nullable()->after('amount');
            }

            if (! Schema::hasColumn('team_ledger', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }

            $table->index(['reference_type', 'reference_id'], 'team_ledger_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::table('team_ledger', function (Blueprint $table): void {
            $table->dropIndex('team_ledger_reference_idx');

            if (Schema::hasColumn('team_ledger', 'reference_id')) {
                $table->dropColumn('reference_id');
            }

            if (Schema::hasColumn('team_ledger', 'reference_type')) {
                $table->dropColumn('reference_type');
            }

            if (Schema::hasColumn('team_ledger', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
