# Installation

## Current Database Status

- Schema Generated: Yes
- Migration Files Generated: Yes
- Seeder Files Generated: Yes
- Database Created: No
- Tables Created: No

Reason: MySQL has not yet been started and migrations have not been executed.

Database schema and migration files have been generated. Actual database tables will be created only after:

1. MySQL Server is running
2. Database is created
3. Laravel migrations are executed

```bash
php artisan migrate
php artisan db:seed
```

## Clone

```bash
git clone repository
```

## Backend Setup

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
```

## Start Docker

```bash
cd docker
docker compose up -d
```

## Run Migrations

```bash
cd ..
php artisan migrate
php artisan db:seed
```

## Start Backend

```bash
php artisan serve
```

## Frontend Setup

```bash
cd ../frontend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

## Default Login Users

All default users use password `Admin@123`.

- `superadmin`
- `admin`
- `production`
- `store`
- `accounts`

Passwords are hashed with Laravel `Hash::make()`.

## Startup Validation

After the backend is running, check:

```bash
GET /api/v1/setup/status
```

The endpoint reports:

- Database Connected
- Redis Connected
- Storage Linked
- Queue Running
- Tenant Exists
- Admin User Exists
