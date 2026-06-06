# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Temperature recording system for Hotel Atankalama. Staff capture food/kitchen temperatures and upload photos; the system stores them in MySQL and can generate PDFs and send daily email reports.

**Production URL:** `https://www.atankalama.com/temp/`

## Architecture

Vanilla PHP with a minimal MVC-like structure and no framework:

- **Entry point:** `index.php` — reads `?route=` GET param, dispatches to `TemperaturaController`, captures output via `ob_start()`, wraps it in `views/layout.php`.
- **Routes:** `form` (default), `guardar`, `listar`, `exportarPDF`
- **Controller:** `controller/TemperaturaController.php` — handles all business logic including image conversion to WebP (max 800px wide, quality 80) via GD.
- **Model:** `models/Temperatura.php` — PDO queries against table `temp_registros` (columns: `id`, `nombre`, `hotel`, `temperatura`, `fotos` as comma-separated paths, `fecha_hora`).
- **DB connection:** `___conec6.php` — creates a global `$pdo` instance; imported by the controller.
- **Cron:** `cron_envio.php` — sends previous day's records by email via PHPMailer (SMTP: `mail.atankalama.com:587`).

## Dependencies

Managed via Composer:
- `phpmailer/phpmailer ^7.0` — daily email reports
- `dompdf/dompdf ^3.1` — PDF export per record

```bash
composer install      # install dependencies
composer update       # update dependencies
```

## Frontend Stack

Bootstrap 5.3 dark theme (`data-bs-theme="dark"`), Outfit font (Google Fonts), FontAwesome 6, SweetAlert2, SheetJS (XLSX export). All loaded from CDNs — no build step.

Custom CSS lives in `css/pro-max.css`. The app is PWA-enabled (`manifest.json`, `sw.js`).

## Uploads

Photos are saved under `uploads/YYYY_MM_DD/` as `temp_<uniqid>.webp`. The `fotos` DB column stores relative paths joined by commas.

## Running Locally

Requires a PHP web server with GD extension enabled and MySQL access. Point the web root to this directory. Update `___conec6.php` with local DB credentials.

```bash
php -S localhost:8000   # quick local server
```

## Key Constraints

- `___conec6.php` holds hardcoded DB credentials — do not add it to version control or expose it publicly.
- The cron script (`cron_envio.php`) also contains SMTP credentials inline.
- `isRemoteEnabled => true` is set in Dompdf to allow embedding images from the filesystem in PDFs.
