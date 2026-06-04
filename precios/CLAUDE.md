# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with this repository.

## What this is

A static PHP-based **digital signage / price display** for Hotel Atankalama. It runs as a full-screen slideshow (typically on a lobby TV or kiosk) cycling through price tables and brochure images using `<META http-equiv='refresh'>` redirects between pages.

## File structure and flow

The slideshow is a chain of `index*.php` files that redirect to each other via meta-refresh:

```
index.php  (prices table, 10s) → index01.php (img 1, 5s) → index02.php (img 2, 5s)
→ ... → index12.php (img 12, 4s) → index.php (loop)
```

- **`index.php`** — price table page with animated gradient background; JS cycles through `.tabla-precios` blocks every 8 s (currently only one block)
- **`index01.php`–`index12.php`** — each shows one brochure image from `img/Brochure-2-N.webp` then redirects to the next page
- **`img/`** — `.webp` brochure images (`Brochure-2-1.webp` … `Brochure-2-12.webp`) plus `politicas.webp/png`

## How to modify

### Adding a new price row or column
Edit `index.php` — the table is plain HTML inside `<div class="tabla-precios">`.

### Changing display duration of a slide
Edit the `content='N'` value in the `<META http-equiv='refresh'>` tag of that page.

### Adding a new brochure image
1. Add the `.webp` to `img/`
2. Create a new `indexN.php` (copy an existing one, update image path and refresh URL)
3. Update the previous last page to redirect to the new page instead of looping back to `index.php`
4. Update the new page to redirect back to `index.php`

### Adding more price tables to `index.php`
Duplicate the `<div class="tabla-precios">` block. The JS (`setInterval`) already handles cycling through all `.tabla-precios` divs — just add more blocks and they will rotate every 8 seconds.

## No build step, no dependencies

Pure PHP/HTML — no Composer, no npm. Deploy by copying files to the web root. No database required.

## Part of the Hotel Atankalama system

This app is standalone (no auth, no DB) — it is intentionally public/read-only for display purposes. It lives under the same server as other hotel apps but is independent of the shared auth system (`/shared/AccesoBootstrap.php`).
