# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**SMS-App** is a Laravel 12 application for **PPDB (Penerimaan Peserta Didik Baru)** — a school new-student admission system. It manages the full student registration lifecycle: account creation, form submission, payment via Midtrans, document verification, multi-stage scoring/ranking, and announcement.

## Commands

**Full development stack (runs server + queue + vite + pail log viewer concurrently):**
```bash
composer dev
```

**Individual services:**
```bash
php artisan serve
npm run dev
php artisan queue:listen --tries=1
```

**Build frontend assets:**
```bash
npm run build
```

**Run all tests:**
```bash
composer test
# or
php artisan test
```

**Run a single test file or filter:**
```bash
php artisan test --filter TestClassName
php artisan test tests/Feature/SomeTest.php
```

**Code style (Laravel Pint):**
```bash
./vendor/bin/pint
./vendor/bin/pint --test   # dry run
```

**Fresh setup from scratch:**
```bash
composer setup
```

**Database:**
```bash
php artisan migrate
php artisan db:seed          # seeds RBAC, master data, and demo PPDB data
php artisan migrate:fresh --seed
```

## Architecture

### Repository-Service Pattern

All business logic flows strictly through: `Controller → Service → RepositoryInterface → Repository → Eloquent Model`

Every module follows this structure. Interface bindings are registered in [AppServiceProvider](app/Providers/AppServiceProvider.php). When adding new repository-backed logic, always:
1. Create an interface and a concrete implementation.
2. Register the binding in `AppServiceProvider::register()`.
3. Inject the interface (not the concrete class) into services.

### Module Structure

Controllers, models, repositories, and services are all grouped by the same domain folders:

| Domain | Purpose |
|---|---|
| `Rbac/` | Roles, permissions, user-role management |
| `Master/` | School profile, curriculum, academic year, semester, majors (jurusan) |
| `Ppdb/` | PPDB configuration: registration waves (pembukaan), paths (jalur), schedules, requirements, fees, quotas, dynamic form builder, document templates |
| `Peserta/` | Student identity cluster (Dapodik-compliant): bio, address, parents, periodic data, contacts, personal documents |
| `Transaksi/` | Registration transactions, uploaded documents, payments, selection scoring, results |
| `Portal/` | Student-facing portal controllers (separate auth) |

### Two Separate Auth Systems

- **Admin panel** (`/admin/*`): uses Laravel's built-in `auth` guard + `CheckPermission` middleware that maps route names to RBAC permissions.
- **Portal/peserta** (`/portal/*`): uses separate `PesertaAuth` and `PesertaAktif` middlewares in [app/Http/Middleware/](app/Http/Middleware/).

RBAC permissions are seeded via `RbacSeeder` and can be re-synced from admin panel at `POST /admin/system/sync-permissions`.

### Cross-Cutting Services

- **`ResponseService`** — all AJAX/JSON responses must use this for consistent `{status, message, data}` format.
- **`LogActivityService`** — audit log required by UU PDP (Indonesian data protection law). Call `log(action, description)` for any data mutation operation.
- **`NotifikasiService`** — in-app + email notification dispatch.

### Database

- Local: **SQLite** using file `marifat` (root of the project).
- Production: MySQL. `DB_CONNECTION=sqlite` in `.env` points to the local file.
- Sessions are stored in the database (not files).

### Frontend

- **Blade templates** with Bootstrap 5 (admin panel) and Tailwind CSS v4 (portal/peserta).
- **Yajra DataTables**: all admin list views use the `list` route convention (e.g. `GET /admin/ppdb/jalur/list`) which returns JSON for DataTables.
- **Vite** bundles `resources/css/app.css` and `resources/js/app.js`.

### Key Integrations

- **Midtrans Snap**: payment gateway for registration fees. Webhook handled at `POST /ppdb/payment/webhook`. Config in [config/midtrans.php](config/midtrans.php).
- **barryvdh/laravel-dompdf**: generates PDF kartu peserta and pengumuman kelulusan.
- **maatwebsite/excel**: Dapodik-standard CSV export/import.

### Dynamic Form Builder

`FormulirPendaftaran` + `FormulirField` tables let admins create custom form fields per registration path (jalur) without code changes. Field values are stored in `PendaftaranFieldValue`.
