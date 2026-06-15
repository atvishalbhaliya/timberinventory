# TimberInventory - Phase 2 Wastage And Reuse Plan

Generated: 2026-06-11

## Objective
Implement Wastage Management and Wastage Reuse as real API-backed, transaction-safe modules.

This phase should move `/wastage` and `/wastage-reuse` from placeholder routes to working operational screens. Wastage produced during production should become visible and controllable, and reusable wastage should be consumed through a controlled reuse production workflow that updates stock ledger and stock summary correctly.

## Current Starting Point

- Production posting already records wastage stock movement.
- The frontend routes `/wastage` and `/wastage-reuse` exist, but are still placeholder/non-API-backed.
- `backend/app/Services/Production/WastageService.php` exists as an empty service placeholder.
- No dedicated wastage or wastage reuse controllers, requests, resources, feature tests, or frontend module views are implemented yet.

## Scope

### 1. Wastage Management APIs
Backend requirements:
- Register protected `/api/v1/wastage` routes.
- Add list, view, create/adjust, approve/post, cancel/reverse, and export endpoints.
- Support filters for date range, branch, item, location, source module, source id/reference, and status.
- Surface wastage generated from posted production records.
- Allow controlled manual wastage adjustment only if the business requires it.
- Enforce transaction-safe stock ledger and stock summary updates.
- Prevent cancelling wastage that has already been consumed by reuse or downstream transactions.

Recommended endpoints:
- `GET /api/v1/wastage`
- `GET /api/v1/wastage/export`
- `POST /api/v1/wastage`
- `GET /api/v1/wastage/{id}`
- `PUT /api/v1/wastage/{id}`
- `DELETE /api/v1/wastage/{id}`
- `POST /api/v1/wastage/{id}/post`
- `POST /api/v1/wastage/{id}/cancel`

### 2. Wastage Management Frontend
Frontend requirements:
- Replace `/wastage` placeholder with an API-backed page.
- Use the GRN/BOM-style list shell.
- Add filters, search, pagination, column picker, export, print, refresh, and compact row actions.
- Add view modal with source reference, item, UOM, quantity, location, status, stock impact, and audit metadata.
- Add create/edit modals only if manual wastage entry is allowed.
- Add post/cancel confirmations with disabled processing state.

### 3. Wastage Reuse Production APIs
Backend requirements:
- Register protected `/api/v1/wastage-reuse` routes.
- Add list, create, view, update, delete, post, cancel, and export endpoints.
- Add next-number endpoint if the UI needs a reuse production number.
- Validate wastage source, consumed wastage item, produced/recovered item, UOM, quantity, source location, destination location, date, and optional team.
- Posting must deduct wastage stock and create recovered/finished stock.
- Cancellation must reverse both stock movements through ledger reversal entries.
- Block posting when wastage stock is insufficient, inactive, cancelled, already consumed, or in a non-reusable location/status.

Recommended endpoints:
- `GET /api/v1/wastage-reuse`
- `GET /api/v1/wastage-reuse/next-number`
- `GET /api/v1/wastage-reuse/export`
- `POST /api/v1/wastage-reuse`
- `GET /api/v1/wastage-reuse/{id}`
- `PUT /api/v1/wastage-reuse/{id}`
- `DELETE /api/v1/wastage-reuse/{id}`
- `POST /api/v1/wastage-reuse/{id}/post`
- `POST /api/v1/wastage-reuse/{id}/cancel`

### 4. Wastage Reuse Frontend
Frontend requirements:
- Replace `/wastage-reuse` placeholder with an API-backed page.
- Follow the Production Entry modal workflow where practical.
- Add filters for date, source wastage item, produced item, location, team, and status.
- Add wastage source selection with available quantity visibility.
- Add output/recovered item rows if multiple outputs are allowed; otherwise keep a single produced item section.
- Add post, cancel, view, edit, delete, export, and print actions.
- Show clear validation messages for insufficient wastage stock and invalid locations/items.

### 5. Stock And Ledger Rules
Wastage management:
- Production-created wastage should increase wastage stock when production is posted.
- Wastage cancellation should reverse stock through ledger reversal entries.
- Manual wastage adjustment, if enabled, must create auditable stock ledger entries.

Wastage reuse:
- Posting should deduct consumed wastage stock.
- Posting should increase recovered/finished stock.
- Cancellation should add wastage stock back and deduct recovered/finished stock through reversal ledger entries.
- All stock-changing operations must run in database transactions.
- All movements must include tenant, branch, item, location, source module, source id, transaction date, and created user context.

### 6. Permissions
Recommended permissions:
- `wastage.view`
- `wastage.manage`
- `wastage.post`
- `wastage.cancel`
- `wastage-reuse.view`
- `wastage-reuse.manage`
- `wastage-reuse.post`
- `wastage-reuse.cancel`

Add/sync these through the permission catalog and seed them for the intended roles.

### 7. Tests
Backend feature tests should cover:
- Wastage list, view, create/adjust if enabled, post, cancel, and export.
- Wastage generated from production is visible in wastage management.
- Wastage stock ledger and stock summary impact.
- Duplicate post prevention.
- Cancellation reversal entries.
- Cancellation blocked when wastage has been reused.
- Wastage reuse draft creation.
- Wastage reuse posting.
- Wastage stock deduction and recovered stock creation.
- Insufficient wastage stock prevention.
- Wastage reuse cancellation and reversal.
- Permission checks for wastage and wastage reuse routes.

Recommended validation commands:
- `php artisan test`
- `php artisan test --filter=WastageApiTest`
- `php artisan test --filter=WastageReuseApiTest`
- `npm run build` in `backend/`
- `npm run build` in `frontend/`

## Suggested Backend Files
Likely files to add or update:
- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/V1/WastageController.php`
- `backend/app/Http/Controllers/Api/V1/WastageReuseController.php`
- `backend/app/Services/Production/WastageService.php`
- `backend/app/Services/Production/WastageReuseService.php`
- `backend/app/Services/InventoryTransactionService.php`
- `backend/app/Services/StockSummaryService.php`
- `backend/app/Services/AuditLogService.php`
- `backend/app/Http/Requests/Api/V1/Wastage/*`
- `backend/app/Http/Requests/Api/V1/WastageReuse/*`
- `backend/app/Http/Resources/Api/V1/WastageResource.php`
- `backend/app/Http/Resources/Api/V1/WastageReuseResource.php`
- `backend/database/migrations/*sync_wastage_permissions.php`
- `backend/tests/Feature/WastageApiTest.php`
- `backend/tests/Feature/WastageReuseApiTest.php`

## Suggested Frontend Files
Likely files to add or update:
- `frontend/routes/web.php`
- `frontend/resources/views/modules/wastage.blade.php`
- `frontend/resources/views/modules/wastage-reuse.blade.php`
- `frontend/resources/views/partials/sidebar.blade.php`
- `frontend/resources/js/bootstrap.js` only if shared API behavior needs changes.

## Definition Of Done
- `/wastage` opens a working API-backed page.
- `/wastage-reuse` opens a working API-backed page.
- Production-created wastage can be listed, filtered, viewed, exported, and cancelled when safe.
- Wastage reuse entries can be drafted, posted, viewed, cancelled, searched, and exported.
- Wastage reuse posting deducts wastage stock and creates recovered/finished stock transactionally.
- Cancellation creates reversal ledger entries and restores balances transactionally.
- Permission middleware protects all new routes.
- Backend feature tests cover core wastage and reuse workflows.
- Full backend test suite passes.
- Backend and frontend Vite builds pass.
- Browser/manual QA passes for desktop, tablet, and mobile layouts.

## Out Of Scope For Phase 2
- Dispatch challan.
- Customer dispatch history.
- Team payment settlement.
- Advanced production and wastage costing reports.
- Barcode/QR workflows.
- Item image upload.
- Full settings/admin module.
