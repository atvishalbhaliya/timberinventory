<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FullSystemDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $tenantId = DB::table('tenant_master')->where('tenant_code', 'SURESH')->value('tenant_id');
        $branchId = DB::table('branch_master')->where('tenant_id', $tenantId)->where('branch_code', 'MAIN')->value('branch_id');

        if (! $tenantId || ! $branchId) {
            return;
        }

        $supplierId = DB::table('party_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'party_code' => 'SUP-PINE',
            'party_name' => 'Pine Wood Traders',
            'party_type' => 'Supplier',
            'gst_no' => '27AAAAA0000A1Z5',
            'contact_person' => 'Ramesh Patil',
            'mobile' => '9000000101',
            'email' => 'supplier@sureshtimber.test',
            'address' => 'Timber Market Yard',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'country' => 'India',
            'credit_days' => 15,
            'credit_limit' => 250000,
            'status' => 'Active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $customerId = DB::table('party_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'party_code' => 'CUS-ALPHA',
            'party_name' => 'Alpha Packaging Pvt Ltd',
            'party_type' => 'Customer',
            'gst_no' => '27BBBBB1111B1Z2',
            'contact_person' => 'Amit Shah',
            'mobile' => '9000000201',
            'email' => 'alpha@sureshtimber.test',
            'address' => 'MIDC Industrial Area',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'country' => 'India',
            'credit_days' => 30,
            'credit_limit' => 500000,
            'status' => 'Active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $rmLocationId = $this->locationId($tenantId, $branchId, 'RM');
        $fgLocationId = $this->locationId($tenantId, $branchId, 'FG');
        $wastageLocationId = $this->locationId($tenantId, $branchId, 'WASTAGE');
        $scrapLocationId = $this->locationId($tenantId, $branchId, 'SCRAP');
        $pineItemId = $this->itemId($tenantId, 'RM-PINE-1356-22');
        $nailItemId = $this->itemId($tenantId, 'RM-NAIL-75');
        $fgItemId = $this->itemId($tenantId, 'FG-P001');
        $scrapItemId = $this->createScrapItem($tenantId);

        $grnId = DB::table('grn_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'supplier_id' => $supplierId,
            'grn_no' => 'GRN-TEST-001',
            'grn_date' => now()->subDays(3)->toDateString(),
            'invoice_no' => 'PIN-INV-1001',
            'vehicle_no' => 'MH12AB1234',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insertGrnDetail($tenantId, $branchId, $grnId, $pineItemId, $rmLocationId, 75, 825);
        $this->insertGrnDetail($tenantId, $branchId, $grnId, $nailItemId, $rmLocationId, 20, 98);

        $modelId = DB::table('pallet_model_master')->where('tenant_id', $tenantId)->where('model_code', 'P001')->value('pallet_model_id');
        $teamId = DB::table('team_master')->where('tenant_id', $tenantId)->where('team_code', 'TEAM-01')->value('team_id');

        $productionId = DB::table('production_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'production_no' => 'PROD-TEST-001',
            'production_date' => now()->subDays(1)->toDateString(),
            'pallet_model_id' => $modelId,
            'team_id' => $teamId,
            'produced_qty' => 18,
            'production_cost' => 21600,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('production_consumption')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'production_id' => $productionId,
            'item_id' => $pineItemId,
            'consumed_qty' => 15.3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('production_output')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'production_id' => $productionId,
            'item_id' => $fgItemId,
            'qty' => 18,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('production_wastage')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'production_id' => $productionId,
            'item_id' => $scrapItemId,
            'qty' => 1.1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->stockMove($tenantId, $branchId, $pineItemId, $rmLocationId, 'Production', $productionId, 'production_master', 0, 15.3);
        $this->stockMove($tenantId, $branchId, $fgItemId, $fgLocationId, 'Production', $productionId, 'production_master', 18, 0);
        $this->stockMove($tenantId, $branchId, $scrapItemId, $wastageLocationId, 'Wastage', $productionId, 'production_master', 1.1, 0);

        DB::table('wastage_stock')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'item_id' => $scrapItemId,
            'wastage_type' => 'Reusable',
            'generated_qty' => 1.1,
            'available_qty' => 1.1,
            'used_qty' => 0,
            'balance_qty' => 1.1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('team_ledger')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'team_id' => $teamId,
            'pallet_model_id' => $modelId,
            'transaction_type' => 'Production',
            'transaction_date' => now()->subDays(1)->toDateString(),
            'qty' => 18,
            'amount' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $challanId = DB::table('challan_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'challan_no' => 'CH-TEST-001',
            'challan_date' => now()->toDateString(),
            'customer_id' => $customerId,
            'vehicle_no' => 'MH14CD2222',
            'driver_name' => 'Vijay Kale',
            'destination' => 'Alpha Packaging Pune',
            'total_qty' => 12,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('challan_team_detail')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'challan_id' => $challanId,
            'pallet_model_id' => $modelId,
            'team_id' => $teamId,
            'qty' => 12,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->stockMove($tenantId, $branchId, $fgItemId, $fgLocationId, 'Dispatch', $challanId, 'challan_master', 0, 12);

        DB::table('team_ledger')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'team_id' => $teamId,
            'pallet_model_id' => $modelId,
            'transaction_type' => 'Dispatch',
            'transaction_date' => now()->toDateString(),
            'qty' => 12,
            'amount' => 300,
            'reference_type' => 'Dispatch Challan',
            'reference_id' => $challanId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('team_payment_summary')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'team_id' => $teamId,
            'payment_month' => (int) now()->month,
            'payment_year' => (int) now()->year,
            'dispatch_qty' => 12,
            'gross_amount' => 300,
            'tds_amount' => 3,
            'net_payable' => 297,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $verificationId = DB::table('stock_verification_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'verification_no' => 'SV-TEST-001',
            'verification_date' => now()->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('stock_verification_detail')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'verification_id' => $verificationId,
            'item_id' => $fgItemId,
            'system_qty' => $this->summaryQty($tenantId, $branchId, $fgItemId, $fgLocationId),
            'physical_qty' => $this->summaryQty($tenantId, $branchId, $fgItemId, $fgLocationId) + 1,
            'variance_qty' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $adjustmentId = DB::table('stock_adjustment_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'adjustment_date' => now()->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('stock_adjustment_detail')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'adjustment_id' => $adjustmentId,
            'item_id' => $fgItemId,
            'adjustment_qty' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->stockMove($tenantId, $branchId, $fgItemId, $fgLocationId, 'Adjustment', $adjustmentId, 'stock_adjustment_master', 1, 0);

        DB::table('audit_log')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'table_name' => 'database_seed',
            'action_type' => 'full_system_demo_seed',
            'record_id' => null,
            'old_value' => null,
            'new_value' => json_encode(['status' => 'Seeded full demo transactions']),
            'user_id' => DB::table('users')->where('login_id', 'superadmin')->value('id'),
            'ip_address' => '127.0.0.1',
            'action_time' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertGrnDetail(int $tenantId, int $branchId, int $grnId, int $itemId, int $locationId, float $qty, float $rate): void
    {
        DB::table('grn_detail')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'grn_id' => $grnId,
            'item_id' => $itemId,
            'location_id' => $locationId,
            'qty' => $qty,
            'rate' => $rate,
            'amount' => $qty * $rate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stockMove($tenantId, $branchId, $itemId, $locationId, 'GRN', $grnId, 'grn_master', $qty, 0, $rate);
    }

    private function stockMove(int $tenantId, int $branchId, int $itemId, int $locationId, string $type, int $referenceId, string $referenceType, float $qtyIn, float $qtyOut, float $rate = 0): void
    {
        $currentQty = $this->summaryQty($tenantId, $branchId, $itemId, $locationId);
        $balanceQty = $currentQty + $qtyIn - $qtyOut;

        DB::table('stock_summary')->updateOrInsert(
            [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'item_id' => $itemId,
                'location_id' => $locationId,
            ],
            [
                'stock_qty' => $balanceQty,
                'avg_rate' => $rate ?: DB::table('stock_summary')
                    ->where('tenant_id', $tenantId)
                    ->where('branch_id', $branchId)
                    ->where('item_id', $itemId)
                    ->where('location_id', $locationId)
                    ->value('avg_rate') ?: 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('stock_ledger')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'item_id' => $itemId,
            'location_id' => $locationId,
            'transaction_date' => now(),
            'transaction_type' => $type,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'balance_qty' => $balanceQty,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function summaryQty(int $tenantId, int $branchId, int $itemId, int $locationId): float
    {
        return (float) DB::table('stock_summary')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->value('stock_qty');
    }

    private function locationId(int $tenantId, int $branchId, string $code): int
    {
        return (int) DB::table('storage_location_master')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('location_code', $code)
            ->value('location_id');
    }

    private function itemId(int $tenantId, string $code): int
    {
        return (int) DB::table('item_master')
            ->where('tenant_id', $tenantId)
            ->where('item_code', $code)
            ->value('item_id');
    }

    private function createScrapItem(int $tenantId): int
    {
        $materialTypeId = DB::table('material_type_master')->where('tenant_id', $tenantId)->where('material_type_code', 'SCRAP')->value('material_type_id');
        $uomId = DB::table('uom_master')->where('tenant_id', $tenantId)->where('uom_code', 'KG')->value('uom_id');

        return DB::table('item_master')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_id' => null,
            'item_code' => 'WST-REUSABLE-WOOD',
            'item_name' => 'Reusable Wood Wastage',
            'item_type' => 'Wastage',
            'material_type_id' => $materialTypeId,
            'uom_id' => $uomId,
            'minimum_stock' => 0,
            'opening_qty' => 0,
            'opening_rate' => 0,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
