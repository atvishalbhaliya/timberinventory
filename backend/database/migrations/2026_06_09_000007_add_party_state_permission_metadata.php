<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('state_master', function (Blueprint $table): void {
            $table->id('state_id');
            $table->foreignId('tenant_id')->nullable()->constrained('tenant_master', 'tenant_id')->cascadeOnDelete();
            $table->string('state_name', 100);
            $table->string('state_code', 20)->nullable();
            $table->string('status', 20)->default('Active');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'state_name']);
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->string('main_module', 80)->nullable()->after('name');
            $table->string('sub_module', 120)->nullable()->after('main_module');
            $table->string('action', 50)->nullable()->after('sub_module');
            $table->string('description')->nullable()->after('action');
        });

        Schema::table('party_master', function (Blueprint $table): void {
            $table->text('remarks')->nullable()->after('pan_no');
        });

        $this->seedStates();
        $this->backfillPermissionMetadata();
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropColumn(['main_module', 'sub_module', 'action', 'description']);
        });

        Schema::table('party_master', function (Blueprint $table): void {
            $table->dropColumn('remarks');
        });

        Schema::dropIfExists('state_master');
    }

    private function seedStates(): void
    {
        $states = [
            ['AN', 'Andaman and Nicobar Islands'], ['AP', 'Andhra Pradesh'], ['AR', 'Arunachal Pradesh'],
            ['AS', 'Assam'], ['BR', 'Bihar'], ['CH', 'Chandigarh'], ['CT', 'Chhattisgarh'],
            ['DN', 'Dadra and Nagar Haveli and Daman and Diu'], ['DL', 'Delhi'], ['GA', 'Goa'],
            ['GJ', 'Gujarat'], ['HR', 'Haryana'], ['HP', 'Himachal Pradesh'], ['JK', 'Jammu and Kashmir'],
            ['JH', 'Jharkhand'], ['KA', 'Karnataka'], ['KL', 'Kerala'], ['LA', 'Ladakh'],
            ['LD', 'Lakshadweep'], ['MP', 'Madhya Pradesh'], ['MH', 'Maharashtra'], ['MN', 'Manipur'],
            ['ML', 'Meghalaya'], ['MZ', 'Mizoram'], ['NL', 'Nagaland'], ['OD', 'Odisha'],
            ['PY', 'Puducherry'], ['PB', 'Punjab'], ['RJ', 'Rajasthan'], ['SK', 'Sikkim'],
            ['TN', 'Tamil Nadu'], ['TG', 'Telangana'], ['TR', 'Tripura'], ['UP', 'Uttar Pradesh'],
            ['UT', 'Uttarakhand'], ['WB', 'West Bengal'],
        ];

        foreach ($states as [$code, $name]) {
            DB::table('state_master')->updateOrInsert(
                ['tenant_id' => null, 'state_name' => $name],
                ['state_code' => $code, 'status' => 'Active', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function backfillPermissionMetadata(): void
    {
        $moduleMap = [
            'stock-ledger' => ['Inventory', 'Stock Ledger'],
            'stock-summary' => ['Inventory', 'Stock Summary'],
            'stock-verification' => ['Inventory', 'Stock Verification'],
            'purchase-grn' => ['Inventory', 'GRN'],
            'masters' => ['Masters', 'Masters'],
            'users' => ['Administration', 'Users'],
            'roles' => ['Administration', 'Roles'],
            'permissions' => ['Administration', 'Permissions'],
            'role-permissions' => ['Administration', 'Roles'],
            'dashboard' => ['Administration', 'Dashboard'],
            'bom' => ['Production', 'BOM'],
            'production' => ['Production', 'Production'],
            'dispatch' => ['Dispatch', 'Dispatch'],
            'accounts' => ['Finance', 'Accounts'],
            'reports' => ['Reports', 'Reports'],
            'audit' => ['Administration', 'Audit'],
            'inventory' => ['Inventory', 'Inventory'],
        ];

        DB::table('permissions')->orderBy('id')->get(['id', 'name'])->each(function ($permission) use ($moduleMap): void {
            [$module, $action] = array_pad(explode('.', $permission->name, 2), 2, 'view');
            [$mainModule, $subModule] = $moduleMap[$module] ?? ['Settings', str($module)->replace('-', ' ')->title()->toString()];

            DB::table('permissions')->where('id', $permission->id)->update([
                'main_module' => $mainModule,
                'sub_module' => $subModule,
                'action' => str($action)->replace('-', ' ')->title()->toString(),
                'description' => null,
                'updated_at' => now(),
            ]);
        });
    }
};
