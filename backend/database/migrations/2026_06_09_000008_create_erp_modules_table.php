<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_modules', function (Blueprint $table): void {
            $table->id('module_id');
            $table->string('module_code', 50)->unique();
            $table->string('module_name', 120);
            $table->foreignId('parent_module_id')->nullable()->constrained('erp_modules', 'module_id')->nullOnDelete();
            $table->string('icon', 80)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('route', 150)->nullable();
            $table->string('status', 20)->default('Active');
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        foreach ([
            ['INVENTORY', 'Inventory', 'boxes', 10, null],
            ['MASTERS', 'Masters', 'database', 20, null],
            ['PRODUCTION', 'Production', 'factory', 30, null],
            ['DISPATCH', 'Dispatch', 'truck', 40, null],
            ['REPORTS', 'Reports', 'bar-chart-3', 50, null],
            ['ADMINISTRATION', 'Administration', 'settings', 60, null],
            ['FINANCE', 'Finance', 'wallet', 70, null],
            ['SETTINGS', 'Settings', 'settings-2', 80, '/settings'],
        ] as [$code, $name, $icon, $order, $route]) {
            DB::table('erp_modules')->updateOrInsert(
                ['module_code' => $code],
                [
                    'module_name' => $name,
                    'icon' => $icon,
                    'display_order' => $order,
                    'route' => $route,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_modules');
    }
};
