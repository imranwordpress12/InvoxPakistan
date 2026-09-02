# Invox Pakistan

Company Subscription & Payment Management System — built on Laravel, Blade, Bootstrap, and MySQL.

Business rules and full specification live in the project PRD. Development proceeds phase by phase; see `CHANGELOG_PROJECT.md` for what has been implemented so far.

## Tech stack

- Laravel 13 (PHP ^8.3)
- MySQL
- Blade templates
- Bootstrap 5 (loaded via CDN — no npm/Node build step required)
- Eloquent ORM

## Local setup (Laragon)

This project's PHP dependencies (`vendor/`) are not included — install them locally:

1. Open a terminal in this folder (e.g. Laragon's "Terminal" button, or right-click → "Open Terminal here").
2. Copy the environment file and generate an app key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Confirm `.env` matches your local MySQL setup. Defaults assume Laragon's usual MySQL credentials:

   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=invox_pakistan
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Create the `invox_pakistan` database in HeidiSQL (Laragon's built-in DB tool) or phpMyAdmin if it doesn't exist yet.

4. Install PHP dependencies:

   ```bash
   composer install
   ```

5. Run the migrations (Laravel skeleton tables plus companies/subscriptions/transactions/audit_logs/company_fbr_credentials from Phase 2):

   ```bash
   php artisan migrate
   ```

   Optionally seed a local-dev admin login (`admin@invoxpakistan.test` / `password`):

   ```bash
   php artisan db:seed
   ```

6. Serve the app. Either use Laragon's Apache virtual host (Laragon auto-creates `http://invoxpakistan.test` for folders under `www/`), or run:

   ```bash
   php artisan serve
   ```

7. Visit the app in your browser. The homepage has **Admin Login** / **Company Login** buttons:
   - Admin: `admin@invoxpakistan.test` / `password` (from the seeder above).
   - Company: no company accounts are seeded yet — company creation is Phase 4. Create one locally via `php artisan tinker`:
     ```php
     $company = App\Models\Company::factory()->create();
     App\Models\User::factory()->company($company)->create(['email' => 'you@example.test']);
     // password is "password"
     ```

## Frontend notes

Bootstrap 5 and Bootstrap Icons are loaded from a CDN in `resources/views/layouts/app.blade.php` — there's no Vite/npm build step. Project-specific overrides go in plain files at `public/css/app.css` and `public/js/app.js`.

## Testing

```bash
php artisan test
```

## Scheduler

Phase 11 registers an hourly job (`subscriptions:process-expired`) in `routes/console.php` that flips lapsed subscriptions to `pending` and creates their renewal invoice. Laravel's scheduler needs one real cron entry pointing at it — nothing runs on its own otherwise. On the server:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Locally, either run `php artisan schedule:work` while developing, or trigger it by hand: `php artisan subscriptions:process-expired`.

## Security & production notes

Reviewed in Phase 12 — see `CHANGELOG_PROJECT.md` for the full pass. Before deploying anywhere other than local dev:

- Set `APP_ENV=production` and `APP_DEBUG=false` — `.env.example`'s `APP_DEBUG=true` is a local-only default; leaving it `true` in production exposes stack traces.
- Generate a fresh `APP_KEY` per environment (`php artisan key:generate`) — never reuse a key across environments.
- Change or remove the seeded admin account (`admin@invoxpakistan.test` / `password`) before going live.
- Serve over HTTPS and set `SESSION_SECURE_COOKIE=true` once you do.
- FBR credentials are encrypted at rest via `APP_KEY` (Laravel's `encrypted` cast) — losing/rotating `APP_KEY` without re-encrypting existing rows will make them unreadable.
