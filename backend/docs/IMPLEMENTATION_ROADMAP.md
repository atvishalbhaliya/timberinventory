# Implementation Roadmap

## Phase 1 - Foundation

- Laravel 12 backend scaffold.
- Sanctum installed.
- `/api/v1/health` endpoint.
- `/api/v1/setup/status` endpoint.
- ERP database migration.
- Tenant-scoped model base.
- Login ID authentication schema.
- Default login user seeder.

## Phase 2 - Authentication and Access Control

- Login, logout, password reset, profile, and token lifecycle APIs.
- Role and permission tables/services/policies.
- Tenant and branch middleware.
- Audit logging for auth and data changes.

## Database Status

- Schema Generated: Yes
- Migration Files Generated: Yes
- Seeder Files Generated: Yes
- Database Created: No
- Tables Created: No

Reason: MySQL has not yet been started and migrations have not been executed.

## Phase 3 - Masters

- Tenant, branch, party, material type, UOM, item, location, team, and pallet model CRUD.
- Form requests, DTOs, repositories, services, resources, and tests.

## Phase 4 - Inventory and Production

- GRN stock posting.
- Stock ledger and stock summary services.
- BOM versioning and active BOM logic.
- Production consumption/output/wastage posting in database transactions.

## Phase 5 - Dispatch and Payments

- Challan entry with multi-team detail.
- Finished goods stock deduction.
- Team ledger updates.
- Monthly payment and TDS calculation.

## Phase 6 - Frontend

- Laravel Blade frontend scaffold.
- Custom light/dark design system.
- AJAX modules, DataTables, forms, charts, and reports.

## Phase 7 - DevOps and QA

- Docker, Nginx, Supervisor, Redis, queues, backups.
- Swagger, Postman collection, PHPUnit coverage.
- Installation guide and user manual.
