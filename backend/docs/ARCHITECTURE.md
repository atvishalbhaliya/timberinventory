# Suresh Timber ERP Architecture

This repository is structured for a two-application ERP:

- `backend`: Laravel 12 API-only application.
- `frontend`: Laravel 12 Blade/AJAX application, to be scaffolded as the UI phase.
- `backend/database/schema`: SQL schema and database reference assets.
- `backend/docker`: deployment and local infrastructure configuration.
- `backend/docs`: implementation, installation, and operating documentation.

## Backend Layers

The backend should follow this request flow:

Controller -> Form Request -> DTO -> Service -> Repository -> Model -> Database

The initial scaffold includes Laravel 12, Sanctum, API routing, Login ID authentication structure, seeders, and a consolidated ERP migration covering tenant, branch, master, stock, production, dispatch, team, payment, verification, adjustment, wastage, and audit tables.

## Database Status

- Schema Generated: Yes
- Migration Files Generated: Yes
- Seeder Files Generated: Yes
- Database Created: No
- Tables Created: No

Reason: MySQL has not yet been started and migrations have not been executed.

Actual database tables will be created only after MySQL is running, the database exists, and Laravel migrations are executed:

```bash
php artisan migrate
php artisan db:seed
```

## API Contract

All versioned APIs should live under `/api/v1` and return:

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

## Multi-Tenant Rules

- Super Admin can access all tenants.
- Tenant Admin can access all branches in the tenant.
- Branch users can access only their branch.

Tenant-aware models should extend `App\Models\ErpModel` or use the `BelongsToTenant` concern.
