# Docker

Run backend infrastructure from this folder:

```bash
cd backend/docker
docker compose up -d
```

Services:

- nginx: http://localhost:8080
- php-fpm
- mysql 8: `suresh_timber_erp`
- redis
- phpMyAdmin: http://localhost:8081

The database container creates the empty MySQL database only. Tables are created later by Laravel migrations:

```bash
cd backend
php artisan migrate
php artisan db:seed
```
