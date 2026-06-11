# Multi-Currency Payment

Laravel 12 + Vue 3/Inertia implementation for the Buzzvel 2026 Dev Team Test. Employees create local-currency payment requests, the API snapshots the EUR exchange rate at creation time, and finance users approve or reject pending requests.

## Requirements

- PHP 8.4+
- Docker with Laravel Sail
- Composer
- Node.js and npm

## Stack

- Laravel 12, MySQL, Laravel Sail
- Laravel Passport with OAuth2 Personal Access Tokens
- Laravel HTTP Client, Cache, Scheduler, PHPUnit
- Vue 3, Inertia.js, Composition API, Vite, Tailwind CSS, Axios

## Installation

```bash
cp .env.example .env
composer install
npm install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan passport:keys --force
./vendor/bin/sail artisan migrate --seed
npm run dev
```

Do not run `passport:install` in this project. The required Passport migrations are already maintained in `database/migrations`, and the seeder creates the personal access client for the `users` provider.

Open `http://localhost`.

## Seeded Users

All seeded users use the password `password`.

- Finance: `finance@example.com`
- Employees: `ana@example.com`, `bruno@example.com`, `charlotte@example.com`, `george@example.com`, `hana@example.com`, `noah@example.com`

## API

Authentication:

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`

Payment requests:

- `POST /api/payment-requests`
- `GET /api/payment-requests?status=pending`
- `GET /api/payment-requests/{id}`
- `PATCH /api/payment-requests/{id}/approve`
- `PATCH /api/payment-requests/{id}/reject`

Use the Passport access token returned by login:

```http
Authorization: Bearer <access_token>
```

## Documentation

Swagger UI is available at `/api/documentation`. Use the **Authorize** button with the Passport access token.

## Scheduler

Pending requests created more than 48 hours ago are expired by:

```bash
php artisan payments:expire
```

Production cron:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Tests

```bash
php artisan test
```

The tests use `Http::fake()` for the exchange provider and Passport helpers for API authentication.
