# TimberInventory - Phase 1 Production Core Status

Generated: 2026-06-11

## Objective
Complete the production core of the ERP by making BOM and Production Entry real API-backed, transaction-safe modules.

Current status: the main Phase 1 implementation is complete. BOM and Production Entry are implemented across backend APIs, frontend screens, inventory posting, wastage capture, team ledger impact, permissions, and targeted feature tests. Remaining Phase 1 work is QA, full regression validation, and final business-rule hardening.

## Implemented Scope

### 1. BOM APIs And Frontend
Implemented backend capabilities:
- Protected `/api/v1/boms` routes are registered.
- List, create, view, update, delete, next-number, and export endpoints are available.
- BOM request/resource classes and `BomService` handle validation and persistence.
- BOM material rows support item, UOM, required quantity, wastage percent, and remarks.
- Active/inactive status is supported.
- Delete protection blocks deleting BOMs referenced by posted production records.
- Permission middleware protects BOM routes.

Implemented frontend capabilities:
- `/bom` is now an API-backed operational page.
- List view includes filters, search, pagination, column picker, export, and print.
- Add, edit, and view use modal workflows.
- Material rows can be edited in the modal.
- Validation and save/update feedback are wired to API responses.

### 2. Production Entry APIs And Frontend
Implemented backend capabilities:
- Protected `/api/v1/production` routes are registered.
- List, create, view, update, delete, next-number, BOM-material loading, post, cancel, and export endpoints are available.
- `ProductionController`, `ProductionService`, and production request/resource classes handle production workflows.
- Draft production entries validate BOM, produced item, locations, team, quantity, date, consumption rows, and wastage rows.
- Production entries can be posted and cancelled through controlled workflows.
- Updates/deletes are blocked where the record status makes them unsafe.
- Permission middleware protects view, manage, post, and cancel actions.

Implemented frontend capabilities:
- `/production` is now an API-backed operational page.
- List view includes date, BOM, team, produced item, status filters, search, pagination, column picker, export, and print.
- Add, edit, and view use modal workflows.
- BOM selection can load material requirements.
- Production modal includes produced item/quantity, source and destination locations, team, material consumption, and wastage capture.
- Post, cancel, and delete use confirmation modals that disable while processing.
- View modal includes read-only audit trail metadata.

### 3. Production Posting
Implemented posting behavior:
- Posting runs through transaction-safe production and inventory services.
- Raw materials are deducted through stock ledger and stock summary updates.
- Finished goods stock movement is created.
- Wastage stock movement is recorded.
- Team production ledger impact is recorded.
- Duplicate posting and duplicate stock movement are blocked.
- Inactive BOMs, inactive teams, inactive items, inactive locations, zero/negative quantities, and insufficient stock are blocked.
- Posting stores audit/status metadata.

### 4. Production Cancellation
Implemented cancellation behavior:
- Cancellation is allowed only for posted production records.
- Stock reversal entries are created instead of silently mutating original ledger rows.
- Raw material, finished goods, and wastage stock impacts are reversed through inventory services.
- Team ledger impact is reversed.
- Cancellation reason, cancelled by, and cancelled at metadata are stored.

### 5. Tests And Validation
Implemented test coverage:
- `backend/tests/Feature/BOMApiTest.php`
- `backend/tests/Feature/ProductionApiTest.php`

Latest targeted checks documented in `PROJECT_STATUS.md`:
- `php artisan migrate` applied production-core and location status migrations.
- `php artisan migrate:status` confirmed all migrations are run.
- `php artisan test --filter=BOMApiTest` passed.
- `php artisan test --filter=ProductionApiTest` passed.
- `frontend`: `npm run build` passed after production and shared master UI updates.

## Implemented API Surface

BOM APIs:
- `GET /api/v1/boms`
- `GET /api/v1/boms/next-number`
- `GET /api/v1/boms/export`
- `POST /api/v1/boms`
- `GET /api/v1/boms/{id}`
- `PUT /api/v1/boms/{id}`
- `DELETE /api/v1/boms/{id}`

Production APIs:
- `GET /api/v1/production`
- `GET /api/v1/production/next-number`
- `GET /api/v1/production/export`
- `GET /api/v1/production/bom/{bom}/materials`
- `POST /api/v1/production`
- `GET /api/v1/production/{id}`
- `PUT /api/v1/production/{id}`
- `DELETE /api/v1/production/{id}`
- `POST /api/v1/production/{id}/post`
- `POST /api/v1/production/{id}/cancel`

## Important Files

Backend:
- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/V1/BOMController.php`
- `backend/app/Http/Controllers/Api/V1/ProductionController.php`
- `backend/app/Services/BomService.php`
- `backend/app/Services/ProductionService.php`
- `backend/app/Services/InventoryTransactionService.php`
- `backend/app/Services/StockSummaryService.php`
- `backend/app/Services/TeamLedgerService.php`
- `backend/app/Services/AuditLogService.php`
- `backend/app/Http/Requests/Api/V1/BOMs/*`
- `backend/app/Http/Requests/Api/V1/Production/*`
- `backend/app/Http/Resources/Api/V1/BOMResource.php`
- `backend/app/Http/Resources/Api/V1/ProductionResource.php`
- `backend/tests/Feature/BOMApiTest.php`
- `backend/tests/Feature/ProductionApiTest.php`

Frontend:
- `frontend/routes/web.php`
- `frontend/resources/views/modules/bom.blade.php`
- `frontend/resources/views/modules/production.blade.php`
- `frontend/resources/views/partials/sidebar.blade.php`
- `frontend/resources/js/bootstrap.js`

Migrations:
- `backend/database/migrations/2026_06_10_000001_add_production_core_fields.php`
- `backend/database/migrations/2026_06_10_000002_sync_production_core_permissions.php`
- `backend/database/migrations/2026_06_11_000001_add_location_status.php`

## Permissions Confirmed

Production-core permissions include:
- `bom.view`
- `bom.manage`
- `production.view`
- `production.manage`
- `production.post`
- `production.cancel`

These are synced by the production permission migration and used by protected API routes/UI actions.

## Remaining Phase 1 Hardening

1. Browser-test `/bom` create, edit, view, delete, filters, export, print, and responsive behavior.
2. Browser-test `/production` create, edit, view, delete, post, cancel, filters, export, print, and responsive behavior.
3. Run full backend regression: `php artisan test`.
4. Run backend production build: `npm run build` in `backend/`.
5. Re-run frontend production build after any QA fixes: `npm run build` in `frontend/`.
6. Add any missing frontend validation messages for production modal edge cases.
7. Decide the business rule for cancelling posted production when finished goods have already been dispatched or otherwise consumed downstream.

## Residual Risks

- Production core has targeted tests, but still needs full regression testing.
- Browser/manual QA is still pending for BOM and Production Entry.
- Cancellation currently reverses posted production, but downstream dispatch/consumption protection still needs a final business rule.
- Production UI has been standardized toward the BOM/GRN list pattern, but responsive edge cases still need real browser review.

## Definition Of Done Status

- `/bom` opens a working API-backed page: done.
- `/production` opens a working API-backed page: done.
- BOM data can be created, edited, viewed, deleted when safe, searched, and exported: done.
- Production entries can be drafted, posted, viewed, cancelled, searched, and exported: done.
- Production posting updates stock ledger and stock summary transactionally: done.
- Production posting records wastage and team ledger impact: done.
- Production cancellation creates reversal entries and restores balances transactionally: done.
- Permission middleware protects all new routes: done.
- Backend feature tests cover core BOM and production workflows: done.
- Full `php artisan test` passes after latest changes: pending.
- Backend and frontend Vite builds pass after latest changes: frontend done, backend pending.
- Browser/manual QA passes: pending.

## Out Of Scope For Phase 1

- Full wastage reuse production module.
- Dispatch challan.
- Team payment settlement.
- Advanced production reports.
- Barcode/QR workflows.
- Item image upload.
- Full settings/admin module.
