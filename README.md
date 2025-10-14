<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12.x-red" alt="Laravel 12"></a>
  <a href="https://filamentphp.com"><img src="https://img.shields.io/badge/Filament-3.3-orange" alt="Filament 3"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-8.2+-blue" alt="PHP 8.2"></a>
  <a href="https://www.postgresql.org"><img src="https://img.shields.io/badge/PostgreSQL-15+-4169e1" alt="PostgreSQL"></a>
</p>

# Snappie Admin Panel

Snappie Admin is the operational command center for the Snappie ecosystem—a gamified, location-based check-in platform. The panel empowers operations teams to moderate user-generated content, curate places and missions, track gamification metrics, and keep the community engaged through real-time analytics and Filament-powered tooling.

---

## Contents

- [Highlights](#highlights)
- [Architecture](#architecture)
- [Feature Overview](#feature-overview)
- [Tech Stack](#tech-stack)
- [Quick Start](#quick-start)
- [Daily Operations](#daily-operations)
- [API Reference](#api-reference)
- [Testing & QA](#testing--qa)
- [Deployment Notes](#deployment-notes)
- [Contributing](#contributing)

---

## Highlights

- **Operational dashboards** with custom Filament widgets for KPIs, pending reviews, and mission health.
- **Composable CRUD resources** covering users, places, reviews, check-ins, coins, EXP, posts, and partnerships.
- **Gamification engine tooling** to trigger missions, rewards, and coin/experience transactions.
- **In-app social management** (posts, comments, likes, follows) with moderation queues.
- **OpenAPI-backed mobile API** secured by Laravel Sanctum and documented for downstream SDK generation.

---

## Architecture

| Layer | Responsibility | Key Locations |
| --- | --- | --- |
| Presentation | Filament 3 admin UI, widgets, forms, tables | `app/Filament/Resources`, `app/Filament/Widgets` |
| Application | HTTP controllers, request validation, middleware | `app/Http/Controllers/Api`, `app/Http/Requests`, `app/Http/Middleware` |
| Domain | Eloquent models, attributes, relationships | `app/Models` |
| Services | Reusable business workflows (auth, gamification, places, social feeds, notifications) | `app/Services` |
| Infrastructure | Helpers, storage adapters, providers, queues | `app/Helpers`, `app/Providers`, `config/`, `bootstrap/` |
| Data | Migrations, factories, seeders for Postgres schema | `database/migrations`, `database/seeders`, `database/factories` |

**Routing**

- REST API lives under `routes/api.php`, versioned at `/api/v1`, with Sanctum-protected groups for authenticated flows.
- Admin UI is bootstrapped via Filament (default path `/admin`).
- Console and scheduled tasks are registered in `routes/console.php` and `app/Console`.

---

## Feature Overview

### Dashboard & Insights
- Real-time metrics for check-ins, reviews, places, and user growth.
- Review backlog warnings and mission completion progress.
- Widget pagination with caching hooks ready for queue-backed refreshes.

### User & Community Management
- CRUD with avatars, profile bios, social handles, and gamification stats.
- Bulk actions (ban/unban, export) and advanced filters (level, EXP, coin range).
- Social graph oversight (followers, posts, likes, comments) with moderation workflows.

### Places & Missions
- Category-based place catalog with geolocation metadata and media galleries.
- Mission configuration (clues, rewards, partnership flags) and status toggles.
- Automatic slugging, SEO-friendly metadata, and JSON-based auxiliary details.

### Gamification Suite
- Transaction logging for EXP and coins (earn + spend).
- Achievement and challenge assignment with completion tracking.
- Reward redemption pipeline integrated with inventory constraints.

### Content Moderation
- Four-state review lifecycle (pending, approved, rejected, flagged) with bulk actions.
- Mission proof validation through image uploads and GPS validations.
- Post/comment moderation, trending feed insights, and engagement stats.

---

## Tech Stack

| Area | Tooling |
| --- | --- |
| Backend | Laravel 12, PHP 8.2+, Sanctum, Postgres 15 |
| Admin UI | Filament 3.3, Tailwind CSS 4, Heroicons |
| Frontend Build | Vite 6 + `@tailwindcss/vite`, Axios for API calls |
| Dev Utilities | Composer 2, npm 10, Pest, Pint, Sail (optional), Docker Compose |
| Observability | Laravel logging stack, queue workers via `queue:listen` |

---

## Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.x
- PostgreSQL 15+
- Node.js 18+
- npm 10+
- Git

### Setup

```pwsh
# Clone & enter
git clone https://github.com/your-username/snappie-admin.git
cd snappie-admin

# Backend dependencies
composer install

# Frontend dependencies
npm install

# Environment
Copy-Item .env.example .env
php artisan key:generate

# Database
# Update .env with Postgres credentials before running:
php artisan migrate --seed

# Storage symlink for media access
php artisan storage:link
```

### Run locally

```pwsh
# Laravel API & Filament admin
php artisan serve

# Vite asset dev server (optional if using Filament defaults)
npm run dev

# Or run everything + queue worker in one process
composer dev
```

Visit the admin at `http://127.0.0.1:8000/admin` (create an admin user via seeder, tinker, or Filament user management).

---

## Daily Operations

| Task | Where |
| --- | --- |
| Approve reviews & mission proofs | Filament → Activity → Reviews / Check-ins |
| Manage users & gamification stats | Filament → Core Data → Users |
| Curate places & missions | Filament → Core Data → Places |
| Monitor social feed | Filament → Social → Posts / Comments |
| Trigger manual rewards | Filament → Gamification → Rewards / Transactions |

**Tips**

- Use column filters and saved table layouts for repeated moderation workflows.
- Queue listeners (`php artisan queue:listen --tries=1`) handle async jobs like notifications—ensure they run in staging/production.
- `app/Helpers/ApiResponseHelper.php` standardizes JSON payloads when extending the API.

---

## API Reference

- Source of truth: `docs/v2/openapi.yaml` (OpenAPI 3.0.3).
- Base URL (local): `http://127.0.0.1:8000/api/v1`.
- Authentication: Bearer tokens issued via Sanctum (`/api/v1/auth/login`).

### Quick tooling

```pwsh
# Serve interactive docs (Swagger UI)


# Validate spec
npx @apidevtools/swagger-parser validate docs/v2/openapi.yaml
```

### Core endpoints

- **Auth**: register, login, logout.
- **User**: profile details, updates, follower graphs.
- **Places**: listings, nearby search, detail with reviews.
- **Gamification**: check-ins, achievements, coin/EXP transactions, rewards.
- **Social**: posts, likes, comments, trending feed.
- **Leaderboard**: weekly/monthly rankings, user rank lookup.

---

## Testing & QA

```pwsh
# Clear cached config to avoid stale settings
php artisan config:clear

# Run full test suite (Pest)
php artisan test

# Run a focused test
php artisan test --filter=LeaderboardTest

# Static analysis & style
./vendor/bin/pint
```

- Seed test data via `php artisan db:seed` for realistic fixtures.
- Factories live under `database/factories` and are ready for high-volume data generation.

---

## Deployment Notes

1. Update environment variables (`APP_ENV=production`, `APP_DEBUG=false`, database, queue, cache, mail).
2. Build assets: `npm run build` (outputs to `public/build`).
3. Optimize application:
   ```pwsh
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --no-dev --optimize-autoloader
   ```
4. Migrate with backups: `php artisan migrate --force`.
5. Ensure `storage/` and `bootstrap/cache` are writable; run `php artisan storage:link` once per environment.
6. Configure systemd/queue workers for `queue:work` or Horizon (if adopted later).

Suggested baseline server: 2 vCPU, 4GB RAM, Postgres 15 managed service, S3-compatible storage for production media.

---

## Contributing

1. Fork and create a feature branch (`git checkout -b feature/awesome-improvement`).
2. Follow PSR-12 and project conventions (Filament resource structure, dedicated services for complex logic).
3. Add tests where the behaviour changes; run `php artisan test` before committing.
4. Submit a pull request with a clear summary, screenshots for UI changes, and database migration notes if applicable.

---

**Made with ❤️ for the Snappie community**  
_Last updated: October 2025_

