# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Tech Stack

- **Backend**: Laravel 13 (PHP 8.4), SQLite (dev) / configurable for production
- **Frontend**: Vite 8, Tailwind CSS 4, DaisyUI 5, Alpine.js 3, jQuery
- **Auth**: Laravel Fortify + Sanctum
- **RBAC**: Spatie Laravel Permissions (roles + permissions)
- **Modules**: NWIDART Laravel Modules

## Common Commands

```bash
# Development
npm run dev                  # Vite dev server (port 5173)
php artisan serve            # Laravel dev server
php artisan queue:listen     # Queue worker

# Build
npm run build                # Frontend assets
npx vite build --config vite.config.backend.js  # Backend module bundles

# Database
php artisan migrate
php artisan db:seed
php artisan db:seed --class="Modules\Auth\Database\Seeders\AuthDatabaseSeeder"

# Testing
php artisan test             # All tests
php artisan test --filter=TestName  # Single test
./vendor/bin/phpunit

# Module scaffolding
php artisan module:make ModuleName
php artisan migration:generate --fresh
```

## Architecture Overview

### Multi-Tenancy

All user data is scoped to an `Organization`. The `TenantContext` static singleton is hydrated by middleware (not the service container) and is read by all tenant-scoped queries. Organization is identified via a 4-layer detection: subdomain → header → authenticated user → session.

Base classes enforce tenant isolation:
- `app/Foundation/TenantAwareModel.php` — adds `organization_id` global scope
- `app/Foundation/TenantAwareJob.php` — restores tenant context in queue workers
- `app/Shared/Tenancy/` — TenantContext, Organization model, and traits

### RBAC

Eight roles (CEO, Sales, Ops, Marketing, HR, AI_Operator, System_Admin, Viewer) with 40+ permissions across 11 domains. `config/permissions.php` maps roles to visible sidebar modules — the UI is rendered from this config, not hardcoded.

### Module System (NWIDART)

Feature modules live in `Modules/`. The `Auth` module is fully active. Other domain modules (CRM, Tasks, SOP, Workflow, etc.) have placeholder routes returning 503 stubs and are not yet implemented. Generate new modules with `php artisan module:make`.

### Frontend Architecture

Two Vite configs:
- `vite.config.js` — core bundle (app.css + app.js: jQuery, Alpine.js, Iconify, DaisyUI)
- `vite.config.backend.js` — lazy-loaded per-page bundles (DataTables, FilePond, Jodit ~500KB, TomSelect, Flatpickr, Swiper, etc.)

Heavy UI libraries are **not** in the core bundle — they load on-demand per page to reduce initial payload. The theme (dark/light) is controlled by an Alpine.js store with localStorage persistence.

### Activity Logging

All `TenantAwareModel` subclasses auto-log model changes via Spatie Activity Log's trait. No manual logging is required in controllers.

### Testing

PHPUnit v12 with two suites: `Unit` (`tests/Unit/`) and `Feature` (`tests/Feature/`). Tests use an in-memory SQLite database configured in `phpunit.xml`. Current test coverage is minimal.
