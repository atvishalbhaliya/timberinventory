# Next Phase Modules And Data Structure

Generated: 2026-06-15

## Purpose

This document defines the next phase of the TimberInventory ERP after the current core modules are in place.

The current system already covers:
- Authentication and permission-driven navigation.
- Admin screens for roles, permissions, and modules.
- Master data CRUD.
- GRN, stock ledger, stock summary, overall stock summary, and stock verification.
- BOM and production entry.
- Wastage and wastage reuse.

The next phase should complete the remaining business modules, tighten the data model usage, and keep the frontend and backend navigation in sync with the permission catalog.

## Next Phase Goal

Deliver the remaining operational ERP flows:
- Dispatch challan management.
- Team ledger tracking.
- Team payment calculation and settlement.
- Reporting screens for operations and finance.
- Settings and final admin refinements.
- Data consistency, permission sync, and navigation cleanup.

## Module Inventory

### 1. Completed Or Current Core Modules

| Area | Module | Route | Permission | Primary Data |
|---|---|---:|---|---|
| Dashboard | Dashboard | `/dashboard` | `dashboard.view` | Summary cards, trends, alerts, recent activity, navigation |
| Administration | Roles | `/roles` | `roles.view` | `roles`, `role_permissions` |
| Administration | Permissions | `/permissions` | `permissions.view` | `permissions` |
| Administration | Modules | `/modules` | `modules.view` | `erp_modules` |
| Master Data | Tenants | `/tenants` | `masters.view` | `tenant_master` |
| Master Data | Branches | `/branches` | `masters.view` | `branch_master` |
| Master Data | Users | `/users` | `masters.view` | `users` |
| Master Data | Parties / Customers / Suppliers | `/customers`, `/suppliers` | `masters.view` | `party_master` |
| Master Data | States | `/states` | `masters.view` | `state_master` |
| Master Data | Material Types | `/material-types` | `masters.view` | `material_type_master` |
| Master Data | UOMs | `/uoms` | `masters.view` | `uom_master` |
| Master Data | Items | `/items` | `masters.view` | `item_master` |
| Master Data | Locations | `/locations` | `masters.view` | `storage_location_master` |
| Master Data | Teams | `/teams` | `masters.view` | `team_master` |
| Master Data | Pallet Models | `/pallet-models` | `masters.view` | `pallet_model_master` |
| Inventory | GRN | `/grn` | `purchase-grn.view` | `grn_master`, `grn_detail`, `stock_ledger`, `stock_summary` |
| Inventory | Stock Ledger | `/stock-ledger` | `stock-ledger.view` | `stock_ledger` |
| Inventory | Stock Summary | `/stock-summary` | `stock-summary.view` | `stock_summary` |
| Inventory | Overall Stock Summary | `/overall-stock-summary` | `overall-stock-summary.view` | `stock_summary`, `stock_ledger` |
| Inventory | Stock Verification | `/stock-verification` | `stock-verification.view` | `stock_verification_master`, `stock_verification_detail`, `stock_adjustment_master`, `stock_adjustment_detail` |
| Production | BOM | `/bom` | `bom.view`, `bom.manage` | `bom_master`, `bom_material` |
| Production | Production Entry | `/production` | `production.view`, `production.manage`, `production.post`, `production.cancel` | `production_master`, `production_consumption`, `production_output`, `production_wastage`, `stock_ledger`, `stock_summary`, `team_ledger` |
| Production Support | Wastage Management | `/wastage` | `wastage.view`, `wastage.manage`, `wastage.post`, `wastage.cancel` | `wastage_stock`, `stock_ledger`, `stock_summary` |
| Production Support | Wastage Reuse | `/wastage-reuse` | `wastage-reuse.view`, `wastage-reuse.manage`, `wastage-reuse.post`, `wastage-reuse.cancel` | `wastage_reuse_master`, `wastage_stock`, `stock_ledger`, `stock_summary` |

### 2. Next Phase Modules

| Area | Module | Route | Suggested Permission | Notes |
|---|---|---:|---|---|
| Dispatch | Dispatch Challan | `/dispatch-challan` | `dispatch.view`, `dispatch.manage` | Customer dispatch, stock deduction, team allocation |
| Dispatch | Team Ledger | `/team-ledger` | `accounts.view`, `accounts.manage` | Team production/dispatch ledger view and reconciliation |
| Finance | Team Payments | `/team-payments` | `accounts.view`, `accounts.manage` | Monthly payment, TDS, deductions, settlement status |
| Reports | Inventory Reports | `/inventory-reports` | `reports.view` | Consolidated inventory analysis |
| Reports | Production Reports | `/production-reports` | `reports.view` | Production performance, usage, and wastage analysis |
| Reports | Dispatch Reports | `/dispatch-reports` | `reports.view` | Dispatch summary, customer-wise, team-wise, period-wise |
| Reports | Payment Reports | `/payment-reports` | `reports.view` | Team payment summary, dues, deductions, paid history |
| Settings | Settings | `/settings` | `masters.manage` or dedicated settings permission | System preferences, labels, and admin configuration |

## Data Structure Overview

### 1. Identity And Access

These tables drive login, tenancy, authorization, and audit.

| Table | Purpose | Key Columns |
|---|---|---|
| `tenant_master` | Tenant/company master | 
`tenant_id`, `tenant_code`, `tenant_name`, `company_name`, `address`, `mobile`, `email`, `status`, 
audit columns |
| `branch_master` | Branch master under a tenant | 
`branch_id`, `tenant_id`, `branch_code`, `branch_name`, `address`, `city`, `state`, `country`, `mobile`, `status`, 
audit columns |
| `role_master` | 
Legacy role master table in ERP schema 
| `role_id`, `tenant_id`, `branch_id`, `role_name`, `guard_name`, 
audit columns |
| `roles` 
| Live role table used by permission service and API | 
`id`, `tenant_id`, `branch_id`, `name`, `guard_name`, audit columns, unique tenant/name |
| `permissions` | Permission catalog with metadata | `id`, `name`, `guard_name`, `main_module`, `sub_module`, `action`, `description`, audit columns |
| `role_permissions` | Role-to-permission mapping | `id`, `role_id`, `permission_id`, audit columns, unique role/permission pair |
| `users` | Live application auth table | `id`, `tenant_id`, `branch_id`, `login_id`, `password`, `employee_code`, `full_name`, `mobile`, `email`, `role_id`, `status`, timestamps, soft deletes |
| `user_master` | Legacy/demo ERP user table | `user_id`, `tenant_id`, `branch_id`, `role_id`, `login_id`, `password`, `employee_code`, `full_name`, `mobile`, `email`, `status`, audit columns |
| `personal_access_tokens` | Sanctum API tokens | `id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, timestamps |
| `sessions` | Database session store | `id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity` |
| `password_reset_tokens` | Password reset support | `login_id`, `token`, `created_at` |
| `audit_log` | Change history for important business actions | `audit_id`, `tenant_id`, `branch_id`, `table_name`, `action_type`, `record_id`, `old_value`, `new_value`, `user_id`, `ip_address`, `action_time`, audit columns |
| `user_preferences` | UI theme and layout preferences | `user_id`, theme fields, layout fields, timestamps |

### 2. Master Data

These tables describe the business entities used by inventory, production, and dispatch.

| Table | Purpose | Key Columns |
|---|---|---|
| `state_master` | State list for tax/address use | `state_id`, `tenant_id`, `state_name`, `state_code`, `status`, soft deletes, unique tenant/state |
| `party_master` | Customers and suppliers | `party_id`, `tenant_id`, `branch_id`, `party_code`, `party_name`, `party_type`, `gst_no`, `pan_no`, `contact_person`, `mobile`, `email`, `address`, `city`, `state`, `country`, `credit_days`, `credit_limit`, `status`, `remarks` |
| `material_type_master` | Material classification | `material_type_id`, `tenant_id`, `branch_id`, `material_type_code`, `material_type_name` |
| `uom_master` | Unit of measure master | `uom_id`, `tenant_id`, `branch_id`, `uom_code`, `uom_name` |
| `item_master` | Item/product/raw material master | `item_id`, `tenant_id`, `branch_id`, `item_code`, `item_name`, `item_type`, `material_type_id`, `uom_id`, `length_mm`, `width_mm`, `thickness_mm`, `cft_factor`, `minimum_stock`, `opening_qty`, `opening_rate`, `status` |
| `storage_location_master` | Stock locations | `location_id`, `tenant_id`, `branch_id`, `location_code`, `location_name`, `location_type`, `status` |
| `team_master` | Contractor/team master | `team_id`, `tenant_id`, `branch_id`, `team_code`, `team_name`, `contractor_name`, `rate_per_pallet`, `tds_percent`, `status` |
| `pallet_model_master` | Pallet model master | `pallet_model_id`, `tenant_id`, `branch_id`, `model_code`, `model_name`, `length`, `width`, `height`, `wood_type` |
| `erp_modules` | Module registry for navigation and module management | `module_id`, `module_code`, `module_name`, `parent_module_id`, `icon`, `display_order`, `route`, `status`, `description`, audit columns, soft deletes |

### 3. Inventory And Production

These tables store stock movement, production, wastage, and stock control data.

| Table | Purpose | Key Columns |
|---|---|---|
| `bom_master` | BOM header/version master | `bom_id`, `tenant_id`, `branch_id`, `pallet_model_id`, `version_no`, `is_active`, `revision_note` |
| `bom_material` | BOM component rows | `bom_material_id`, `tenant_id`, `branch_id`, `bom_id`, `item_id`, `required_qty`, `wastage_percent` |
| `grn_master` | Goods receipt header | `grn_id`, `tenant_id`, `branch_id`, `supplier_id`, `grn_no`, `grn_date`, `invoice_no`, `vehicle_no` |
| `grn_detail` | GRN line items | `grn_detail_id`, `tenant_id`, `branch_id`, `grn_id`, `item_id`, `location_id`, `qty`, `rate`, `amount` |
| `stock_ledger` | Transaction ledger for all stock movement | `ledger_id`, `tenant_id`, `branch_id`, `item_id`, `location_id`, `transaction_date`, `transaction_type`, `reference_id`, `reference_type`, `qty_in`, `qty_out`, `balance_qty` |
| `stock_summary` | Current stock snapshot by item/location | `stock_id`, `tenant_id`, `branch_id`, `item_id`, `location_id`, `stock_qty`, `avg_rate`, unique tenant/branch/item/location pair |
| `production_master` | Production header | `production_id`, `tenant_id`, `branch_id`, `production_no`, `production_date`, `pallet_model_id`, `team_id`, `produced_qty`, `production_cost` |
| `production_consumption` | Production raw-material consumption rows | `consumption_id`, `tenant_id`, `branch_id`, `production_id`, `item_id`, `consumed_qty` |
| `production_output` | Production finished-goods output rows | `output_id`, `tenant_id`, `branch_id`, `production_id`, `item_id`, `qty` |
| `production_wastage` | Production wastage rows | `wastage_id`, `tenant_id`, `branch_id`, `production_id`, `item_id`, `qty` |
| `wastage_stock` | Wastage stock ledger and status | `wastage_stock_id`, `tenant_id`, `branch_id`, `item_id`, `location_id`, `wastage_type`, `source_module`, `source_id`, `source_reference`, `transaction_date`, `generated_qty`, `available_qty`, `used_qty`, `balance_qty`, `status`, `posted_at`, `posted_by`, `cancelled_at`, `cancelled_by`, `cancellation_reason`, `remarks` |
| `wastage_reuse_master` | Wastage reuse production header | `reuse_id`, `tenant_id`, `branch_id`, `reuse_no`, `reuse_date`, `source_wastage_stock_id`, `source_item_id`, `source_location_id`, `consumed_qty`, `produced_item_id`, `destination_location_id`, `team_id`, `produced_qty`, `production_cost`, `status`, `remarks`, `posted_at`, `posted_by`, `cancelled_at`, `cancelled_by`, `cancellation_reason` |
| `team_ledger` | Team production/dispatch ledger | `ledger_id`, `tenant_id`, `branch_id`, `team_id`, `pallet_model_id`, `transaction_type`, `transaction_date`, `qty` |
| `challan_master` | Dispatch challan header | `challan_id`, `tenant_id`, `branch_id`, `challan_no`, `challan_date`, `customer_id`, `vehicle_no`, `driver_name`, `destination`, `total_qty` |
| `challan_team_detail` | Challan team/detail rows | `detail_id`, `tenant_id`, `branch_id`, `challan_id`, `pallet_model_id`, `team_id`, `qty` |
| `team_payment_summary` | Monthly team payment summary | `payment_id`, `tenant_id`, `branch_id`, `team_id`, `payment_month`, `payment_year`, `dispatch_qty`, `gross_amount`, `tds_amount`, `net_payable` |
| `stock_verification_master` | Stock count header | `verification_id`, `tenant_id`, `branch_id`, `verification_no`, `verification_date` |
| `stock_verification_detail` | Stock count rows | `detail_id`, `tenant_id`, `branch_id`, `verification_id`, `item_id`, `system_qty`, `physical_qty`, `variance_qty` |
| `stock_adjustment_master` | Stock adjustment header | `adjustment_id`, `tenant_id`, `branch_id`, `adjustment_date` |
| `stock_adjustment_detail` | Stock adjustment rows | `detail_id`, `tenant_id`, `branch_id`, `adjustment_id`, `item_id`, `adjustment_qty` |

### 4. System And Infrastructure Tables

These are supporting tables from Laravel and the ERP platform itself.

| Table | Purpose |
|---|---|
| `cache` | Cache storage |
| `cache_locks` | Cache locking |
| `jobs` | Queue jobs |
| `job_batches` | Batch queue metadata |
| `failed_jobs` | Failed queue jobs |
| `sessions` | Web session storage |
| `password_reset_tokens` | Password reset flow |
| `personal_access_tokens` | Sanctum API tokens |

## Common Data Pattern

Most ERP business tables follow the same structure:
- A numeric primary key ending in `_id`.
- `tenant_id` for multi-tenant separation.
- `branch_id` where branch scope matters.
- `created_by` and `updated_by` audit fields.
- `created_at` and `updated_at`.
- `deleted_at` for soft-deleted entities where applicable.

Most stock-impacting tables also include:
- `transaction_date`
- `status`
- source/reference fields
- reversal or cancellation metadata

## Permission And Navigation Structure

The navigation system is permission-driven:
- `PermissionCatalog::permissions()` defines the canonical permission keys.
- `PermissionCatalog::navigation()` defines the sidebar items.
- `PermissionService::navigationForUser()` filters navigation by the current user's role permissions.
- `frontend/resources/js/app.js` caches sidebar navigation in local storage and reloads it from `/api/v1/dashboard/navigation`.

Important permission groups already in the catalog:
- `dashboard.view`
- `roles.*`
- `permissions.*`
- `modules.*`
- `masters.view`, `masters.manage`
- `purchase-grn.*`
- `stock-ledger.view`
- `stock-summary.view`
- `overall-stock-summary.view`
- `stock-verification.*`
- `bom.*`
- `production.*`
- `wastage.*`
- `wastage-reuse.*`
- `dispatch.*`
- `accounts.*`
- `reports.view`
- `audit.view`

## Recommended Next Phase Build Order

1. Dispatch challan.
2. Team ledger.
3. Team payments and month-end settlement.
4. Reporting suite.
5. Settings screen and admin refinements.
6. Final permission and sidebar cleanup.
7. Regression tests and browser QA.

## Suggested Backend Files For Next Phase

- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/V1/DispatchController.php`
- `backend/app/Http/Controllers/Api/V1/TeamLedgerController.php`
- `backend/app/Http/Controllers/Api/V1/TeamPaymentController.php`
- `backend/app/Http/Controllers/Api/V1/ReportController.php`
- `backend/app/Services/Dispatch/*`
- `backend/app/Services/Finance/*`
- `backend/app/Services/Reports/*`
- `backend/app/Http/Requests/Api/V1/Dispatch/*`
- `backend/app/Http/Requests/Api/V1/Finance/*`
- `backend/app/Http/Resources/Api/V1/*`
- `backend/database/migrations/*dispatch*`
- `backend/database/migrations/*team*payment*`

## Suggested Frontend Files For Next Phase

- `frontend/routes/web.php`
- `frontend/resources/views/modules/dispatch-challan.blade.php`
- `frontend/resources/views/modules/team-ledger.blade.php`
- `frontend/resources/views/modules/team-payments.blade.php`
- `frontend/resources/views/modules/inventory-reports.blade.php`
- `frontend/resources/views/modules/production-reports.blade.php`
- `frontend/resources/views/modules/dispatch-reports.blade.php`
- `frontend/resources/views/modules/payment-reports.blade.php`
- `frontend/resources/views/modules/settings.blade.php`
- `frontend/resources/views/partials/sidebar.blade.php`
- `frontend/resources/js/app.js`

## Notes

- `users` is the live auth table used by the application.
- `user_master` still exists in the ERP schema and seeder data, so it should be treated as legacy/demo-compatible until the project intentionally removes it.
- The current sidebar and route permission system already supports the next-phase navigation model.
- The next phase should reuse the same modal/list layout patterns already established in GRN, BOM, Production, Wastage, and generic master pages.
