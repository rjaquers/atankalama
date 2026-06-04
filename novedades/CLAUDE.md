# CLAUDE.md — Sistema de Novedades (Hotel Atankalama)

> Reglas transversales del hotel en: `/Volumes/disco_secundario/htdocs/docs/CLAUDE.md`
> Este archivo complementa con contexto específico de esta app.

## Contexto del proyecto

App PHP MVC sin framework. Punto de entrada: `public/index.php`.
Base de datos: `cat6852_hotel_tickets` (credenciales en `config/config.php`).

## Estructura

```
controllers/   — Controladores (PascalCase)
models/        — Modelos PDO
services/      — Servicios reutilizables (OtpService, ImportanciaService)
views/         — Vistas PHP puras
config/        — Database.php + config.php (constantes DB, SMTP)
helpers/       — cierre.php (cierre HTML)
sql/           — Migraciones
vendor/        — PHPMailer (composer)
```

## Prefijos de tablas

| Prefijo | Módulo |
|---|---|
| `nov_` | Novedades (este sistema) |
| `chk_` | Checklists / Auth centralizada |
| `chat_` | Chat interno y áreas del hotel |

## Autenticación de secciones admin

Este sistema usa el **sistema OTP centralizado** documentado en `docs/CLAUDE.md`.

- **App slug**: `novedades`
- **Variables de sesión**: `nov_admin_email`, `nov_admin_expires`
- **Rutas protegidas**: `empresas/*`, `encargados/*`, `recepcionistas/*`
- **Función de protección**: `requireAdminAuth($route)` en `public/index.php`
- **Servicio OTP**: `services/OtpService.php` (APP_SLUG = 'novedades')

## Tabla de personal

`nov_recepcionistas` almacena a **todo el personal** del hotel (no solo recepción).
- El campo `area_id` es FK a `chat_areas.id` — NO usar ENUM para áreas
- Las áreas disponibles vienen de `chat_areas WHERE estado = 'activo'`
- El modelo `Recepcionista` hace JOIN a `chat_areas` en todas las consultas

## Email

PHPMailer configurado con constantes `SMTP_*` definidas en `config/config.php`.
Ver uso en `services/OtpService.php` o `controllers/NovedadController.php`.

## Convenciones

- PDO con `prepare()` + `execute()` — sin concatenación de inputs en SQL
- `htmlspecialchars()` en toda salida de datos a HTML
- Redirecciones con `header('Location: ...')` + `exit`
- Sin framework de rutas: switch/case en `public/index.php`
