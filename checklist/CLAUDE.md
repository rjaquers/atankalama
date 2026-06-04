# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Hotel Checklist Atankalama** — A PHP hotel audit system for conducting checklists across hotel departments (reception, housekeeping, kitchen, etc.), executing evaluations, and generating compliance reports.

## Running the Project

```bash
# Local development server (run from project root)
php -S localhost:8080 -t public/

# Run CLI utility scripts
php scripts/seed_reports.php    # Seeds 100 test evaluation records
php scripts/migrate_foto.php    # Photo migration utility
```

**Requirements:** PHP 7.4+, PDO MySQL driver, GD extension, finfo extension.

**Production:** Apache with `mod_rewrite` enabled. Document root must point to `public/`.

There is no build step — this is pure PHP with no Composer packages.

## Configuration

All environment config lives in [config/config.php](config/config.php):
- Database credentials (separate blocks for production vs. local)
- `BASE_URL` (auto-detected from `HTTP_HOST`)
- SMTP settings for email
- OTP expiration time and attempt limits
- Allowed email domain (`@atankalama.com`)

## Architecture

Custom MVC framework with no external dependencies.

### Request Lifecycle

1. Apache rewrites all requests to `public/index.php`
2. `Router` matches the URL against registered patterns (regex-based)
3. Matched route runs `AuthMiddleware` if protected, then dispatches to a `Controller`
4. Controllers call Models and Services, then call `render()`, `json()`, or `redirect()`
5. `render($view, $data, $layout)` extracts `$data` as variables and wraps the view in a layout using output buffering

### Key Directories

| Path | Purpose |
|---|---|
| `app/Core/` | Framework foundation (Router, Database, Controller, Model, Logger, Security) |
| `app/Controllers/` | HTTP request handlers (Auth, Dashboard, Checklist, Evaluation, Report, Area, User) |
| `app/Models/` | PDO-based data access (User, Checklist, Evaluation, Token, Area, Recepcionista) |
| `app/Services/` | Business logic (EmailService, ReportService, PhotoUploadService) |
| `app/Middleware/` | Auth guard |
| `app/views/` | PHP templates |
| `config/` | Environment config |
| `public/` | Web root (index.php, assets, uploads) |
| `logs/` | System log files (auto-created) |

### Database

- All tables use the `chk_` prefix (configurable)
- `Database.php` is a PDO singleton — always use prepared statements
- No migration framework; schema must be set up manually
- Key tables: `chk_usuarios`, `chk_checklists`, `chk_checklist_preguntas`, `chk_evaluaciones`, `chk_evaluacion_respuestas`, `chk_login_tokens`, `chk_areas`, `chk_system_logs`

### Authentication

Passwordless OTP system: users receive a one-time code by email (10-min expiration, 3-attempt limit). Only emails matching the configured domain are allowed. CSRF tokens are generated/validated via `Core/Security.php`.

### Logging

Dual logging to both the `chk_system_logs` DB table and `/logs/system.log`. Logger levels: INFO, ERROR, WARNING, SECURITY.

### Photo Uploads

`PhotoUploadService` validates MIME types via `finfo`, auto-converts to WebP at 80% quality. Files are stored in `public/uploads/`. Max 3MB per file, 5 files per question.

## Conventions

- **Language:** Code comments and variable names are in Spanish throughout
- **Roles:** `admin` vs. `operador` (operator)
- **Evaluation response types:** boolean (yes/no), numeric scale (1–10), text observation, photo upload
- **PHP polyfills:** `str_starts_with`, `str_ends_with`, `str_contains` are polyfilled in `config.php` for PHP 7.4 compatibility
- Frontend uses Bootstrap 5.3 and Bootstrap Icons loaded from CDN
