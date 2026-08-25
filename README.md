## Asset & Inventory Management System
web-based asset and inventory management system built with **Laravel 10** and **Filament 3**. It covers the full flow of managing a catalog of items, warehouse stock per division, internal requests, multi-step approvals, and activity logging — all wrapped in a role-based permission system.

## Features

- **Item catalog** — master data for items with a single source of truth.
- **Warehouse stock** — per-division stock levels with movement charts and overview widgets.
- **Requests & approvals** — staff submit item requests; admins verify details and approve/reject them, with race-condition-safe approval logic.
- **Activity log** — every create, update, approve, and delete action is recorded and filterable.
- **Role-based access control** — roles and permissions managed through Filament Shield (`super_admin`, `admin`, `finance`, `user`).
- **Division scoping** — users with restricted permissions only see data belonging to their own division.
- **Excel import/export** — bulk import items from a downloadable template and export reports.
- **PDF reports** — stock and activity reports rendered with Dompdf.
- **Dashboard widgets** — stock movement, top requested items, and activity statistics per role.

## Tech Stack

| Layer      | Technology |
|------------|------------|
| Backend    | PHP 8.2+, Laravel 10 |
| Admin UI   | Filament 3 |
| Database   | MySQL / MariaDB |
| Frontend   | Vite 4, Tailwind (via Filament) |
| Auth & RBAC| Laravel Sanctum, spatie/laravel-permission, bezhansalleh/filament-shield |
| Exports    | pxlrbt/filament-excel, eighty nine filament-excel-import, barryvdh/laravel-dompdf |

## Requirements

- PHP 8.2 or newer (with the usual Laravel extensions)
- Composer
- Node.js 18 or newer
- MySQL or MariaDB

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Install frontend dependencies
npm install

# 3. Prepare the environment file
cp .env.example .env
php artisan key:generate
```

Open `.env`, point `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` at your MySQL database, then run:

```bash
php artisan migrate --seed
```

The seeders create the schema, 6 demo divisions, roles and permissions, 13 demo users, sample items, warehouse stock, requests, and activity logs.

## Run Locally

```bash
# Backend
php artisan serve

# Frontend (separate terminal)
npm run dev
```

The application is served at the `/app` path (Filament admin panel).

## Build Assets for Production

```bash
npm run build
```

## Default Demo Login

| Role      | Email             | Password   |
|-----------|-------------------|------------|
| finance  | `admin@gmail.com` | `12345678` |

The remaining 12 demo accounts (warehouse admins and staff for each division) are created by `database/seeders/UserSeeder.php`; every account uses the password `12345678`.

## Project Structure

- `app/Filament` — admin panel, pages, resources, and widgets
- `app/Models` — Eloquent models
- `app/Services` — Excel import and query filter services
- `app/Traits` — `HasBagianScope` division-scoping trait
- `database/migrations` — database schema
- `database/seeders` — demo and bootstrap data
- `resources/views` — Blade views, logo components, and PDF templates
- `public/templates` — Excel import template

## License

This project is open-sourced under the [MIT license](LICENSE).
