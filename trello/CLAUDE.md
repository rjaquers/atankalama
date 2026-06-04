# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Setup

1. Create a MySQL database and import `sql/schema.sql` then `sql/seed.sql`.
2. Configure credentials in `config/config.php` (DB_* and MAIL_* constants). For local overrides, create `config/config.local.php` — it's not versioned and is auto-loaded if present.
3. Point the web server DocumentRoot to `/public/`.
4. Demo login: `admin@rkm.local` / `admin123`.
5. PHPMailer is optional: place its `src/` folder at `/vendor/phpmailer/src/`; `MailService` auto-detects it.

## Routing

All HTTP requests go through `public/index.php` → `app/core/Router.php`.

URL format: `/{controller}/{method}/{param1}/{param2}`  
- The first segment maps to `{Name}Controller.php` (auto-capitalized).
- Second segment is the method name (defaults to `index`).
- Supports up to 2 positional parameters passed as method arguments.

Apache rewrites strip the URL into `$_GET['url']`; see `public/.htaccess`.

## Architecture

### Core (`app/core/`)
- **`Router.php`** — dispatches URL to controller/method.
- **`Autoload.php`** — PSR-0-style autoloader; maps class names to file paths under `app/`.
- **`Controller.php`** — base class with `view($template, $data)`, `json($data)`, `redirect($url)`.
- **`Model.php`** — base class that opens a MySQLi connection via `config/database.php`; subclasses use `$this->conn`.
- **`EventDispatcher.php`** — simple `listen(event, fn)` / `dispatch(event, data)` bus; listeners are registered in `public/index.php`.

### Layers
| Layer | Location | Pattern |
|---|---|---|
| Controllers | `app/controllers/` | Thin — delegate to Services |
| Services | `app/services/` | Business logic (Auth, Mail, Notification, Sync) |
| Models | `app/models/` | DB access via MySQLi prepared statements |
| Views | `app/views/` | Pure PHP templates; `extract()` injects controller data |
| Middleware | `app/middleware/` | `AuthMiddleware::check()` for session guard; `PermissionMiddleware` for role checks |

Views always include `layouts/header.php` and `layouts/footer.php`. No template engine.

### Authentication
Session-based. `AuthService` verifies credentials with `password_verify()`. On successful login the session is regenerated and `$_SESSION['user_id']` is set. `AuthMiddleware::check()` guards protected routes.

> **Note**: This starter kit uses its own auth system. The hotel-wide OTP/AccesoBootstrap system (`~/.claude/rules/auth-otp.md`) is a separate shared library — check whether to replace or integrate before adding new auth logic.

### Offline / PWA
- `public/service-worker.js` caches assets (cache-first) and HTML (network-first).
- `public/assets/js/offline-sync.js` queues form submissions to LocalStorage when offline and auto-syncs on reconnect.
- Server-side batch endpoint: `OfflineSyncController@store()` accepts JSON payloads.
- Max batch size: `OFFLINE_SYNC_MAX_BATCH` (default 50).

### Notifications
`NotificationService` routes to three channels: internal DB (`NotificationModel`), email (`MailService` / PHPMailer), and optional Telegram bot. All notifications are persisted in the `notifications` table.

## Key Config Constants (`config/config.php`)

| Constant | Purpose |
|---|---|
| `BASE_URL` | Auto-derived from HTTP host + path |
| `APP_ROOT`, `VIEW_PATH` | Used by autoloader and `Controller::view()` |
| `DB_HOST/USER/PASS/NAME` | MySQL credentials (default DB: `rkm_system`) |
| `MAIL_*` | SMTP settings for MailService |
| `OFFLINE_SYNC_ENDPOINT` | URL the JS sync client POSTs to |

Timezone is `America/Santiago` — matches the rest of the hotel stack.

## Frontend

No build step. CDN-loaded: Bootstrap 5.3, Bootstrap Icons 1.11, FontAwesome 6.5, html5-qrcode 2.3.8. Custom styles in `public/assets/css/style.css`. Main JS bootstrap in `public/assets/js/app.js`.
