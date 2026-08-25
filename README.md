# SIATK

SIATK is a Laravel 10 + Filament 3 warehouse and asset management system for catalog items, warehouse stock, requests, approvals, and activity logs.

## Features

- Item catalog management
- Warehouse stock per unit/division
- Request submission and approval flow
- Activity logging and reporting
- Role and permission management via Filament Shield
- Excel and PDF export support

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL or MariaDB

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`, then run:

```bash
php artisan migrate --seed
```

## Run Locally

Start the backend:

```bash
php artisan serve
```

Start the frontend build watcher:

```bash
npm run dev
```

## Build Assets

```bash
npm run build
```

## Default Demo Login

- Email: `admin@gmail.com`
- Password: `12345678`

## Notes

- The seeded demo data uses English labels for public repository use.
- If you are deploying from scratch, always run migrations and seeders after configuring the environment.

## Project Structure

- `app/Filament` - admin panels, pages, resources, and widgets
- `app/Models` - application models
- `database/migrations` - database schema
- `database/seeders` - demo and bootstrap data
- `resources/views` - Blade templates and exports

## License

MIT
