<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    { //
        Schema::create('team_payment_entry', function (Blueprint $table): void {
            $table->id('entry_id');
            $table->foreignId('tenant_id')->constrained('tenant_master', 'tenant_id')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branch_master', 'branch_id')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('team_master', 'team_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('payment_month');
            $table->unsignedSmallInteger('payment_year');
            $table->decimal('payment_amount', 18, 2);
            $table->date('payment_date');
            $table->string('payment_mode', 30);
            $table->string('reference_no', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('user_master', 'user_id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user_master', 'user_id')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'branch_id', 'team_id', 'payment_month', 'payment_year'], 'team_payment_entry_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_payment_entry');
    }
};
