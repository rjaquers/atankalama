# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the app

```bash
# Install PHP dependencies
composer install

# Serve locally (serves from project root)
php -S localhost:8000
```

To switch to the local database, change `$dondeestoy = 'web'` to `$dondeestoy = 'local'` in `config/database.php`. Local DB is `hotel_inventory`; production DB is `cat6852_inventario`.

## Architecture

Custom PHP MVC without a framework. Entry point is `index.php`, which dispatches via `?page=X&action=Y&id=Z`:

- **`config/`** — `database.php` (PDO singleton), `config.php` (BASE_URL, session start, session bypass), `helpers.php` (sanitize, redirect, formatDate, authLog)
- **`models/`** — Data layer, each model receives a `Database` instance via constructor
- **`controllers/`** — Business logic; controllers instantiate their models and call `require_once` on view files directly
- **`views/`** — HTML templates; `views/layout/home.php` is the master template that wraps all page content
- **`supabase/migrations/`** — Full schema SQL (use for local setup)

## Routing pattern

All routes go through `index.php`. Adding a new resource means:
1. Add a `case` block in `index.php` routing switch
2. Create `controllers/FooController.php` and `models/Foo.php`
3. Create `views/foo/` directory with view files

## Authentication status

Authentication is **currently bypassed** in `config/config.php` (hardcoded `$_SESSION['user_id'] = 1` and `role = 'admin'`). The app has its own `AuthController`/`User` model with bcrypt + PHPMailer password recovery, but the session check helpers in `helpers.php` (`isLoggedIn()`, `isAdmin()`, `requireLogin()`) are no-ops.

When the shared hotel auth system (AccesoBootstrap) is integrated, the session prefix for this app is `inv` (keys: `inv_admin_email`, `inv_admin_expires`).

## Key models

- **`Product`** — Core model; auto-logs every change to `product_logs` (old/new values). Handles stock adjustments, images, low-stock/dead-stock queries.
- **`ConsumptionEvent`** — Creates consumption records and decrements stock in a transaction.
- **`StockEntry`** — Creates ingreso records and increments stock in a transaction.
- **`VoiceStockModel`** — Spanish NLP: extracts verb + quantity + product name from dictated text, fuzzy-matches against product names (60%+ threshold), then calls ConsumptionEvent or StockEntry.

## Frontend

Bootstrap 5 + Font Awesome 6.4, loaded from CDN in `views/layout/header.php`. Local overrides in `assets/css/main.css`. Voice UI client logic in `public/js/voice-stock.js`. The app ships a PWA manifest (`manifest.json`) and service worker (`service-worker.js`).

## Table prefix

`inv_` tables belong to this app. Shared auth tables use `chk_` prefix and live in `cat6852_hotel_tickets` (separate DB managed by `AccesoBootstrap`).
